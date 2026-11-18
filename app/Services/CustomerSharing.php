<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/** Identità comuni e proiezioni locali transazionali; non estende gli scope tenant. */
final class CustomerSharing
{
    public const RESIDENCE = ['country', 'city', 'region', 'province', 'cap', 'address', 'number'];
    public const FIELDS = ['name', 'surname', 'sex', 'country', 'city', 'region', 'province', 'cap',
        'address', 'number', 'email', 'phone', 'fax', 'cellphone', 'country_reg', 'region_reg',
        'city_reg', 'prov_reg', 'cap_reg', 'ciudadania_reg', 'nac_reg', 'type_doc_reg', 'num_doc_reg',
        'date_pub_reg', 'expire_reg', 'country_doc_reg', 'city_doc_reg'];

    public function available(): bool
    {
        // L'ultima tabella è anche il marcatore di completamento della migrazione additiva.
        return Schema::hasTable('customer_duplicate_reviews') && Schema::hasTable('customer_sharing_groups');
    }

    public function groupFor(int $sid): ?object
    {
        if (!$sid || !$this->available()) return null;
        return DB::table('customer_sharing_groups as g')
            ->join('customer_sharing_memberships as m', 'm.group_id', '=', 'g.id')
            ->join('struttura as s', 's.id', '=', 'm.struttura_id')
            ->where('m.struttura_id', $sid)->where('g.stato', 'attivo')
            ->whereColumn('s.proprietario_id', 'g.proprietario_id')->select('g.*')->first();
    }

    public function members(int $groupId, bool $current = false): array
    {
        return DB::table('customer_sharing_memberships as m')
            ->join('customer_sharing_groups as g', 'g.id', '=', 'm.group_id')
            ->join('struttura as s', 's.id', '=', 'm.struttura_id')
            ->where('g.id', $groupId)->where('g.stato', 'attivo')
            ->whereColumn('s.proprietario_id', 'g.proprietario_id')->orderBy('s.id')->when($current, fn ($q) => $q->lockForUpdate())
            ->get(['s.id'])->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function lockForStructure(int $sid): ?object
    {
        if (!$sid || !$this->available()) return null;
        // La stessa barriera del proprietario serializza attivazione e scritture:
        // una richiesta partita prima dell'attivazione non può salvare fuori gruppo.
        $observedOwner = DB::table('struttura')->where('id', $sid)->value('proprietario_id');
        if (!$observedOwner) return null;
        DB::table('proprietari')->where('id', $observedOwner)->lockForUpdate()->first();
        $structure = DB::table('struttura')->where('id', $sid)->lockForUpdate()->first();
        abort_unless($structure && (int) $structure->proprietario_id === (int) $observedOwner, 409, 'La struttura è cambiata. Ricarica il contesto.');
        $membership = DB::table('customer_sharing_memberships')->where('struttura_id', $sid)->lockForUpdate()->first();
        if (!$membership) return null;
        $locked = DB::table('customer_sharing_groups')->where('id', $membership->group_id)->where('proprietario_id', $observedOwner)->lockForUpdate()->first();
        abort_unless($locked && $locked->stato === 'attivo', 409, 'La condivisione è cambiata. Ricarica la pagina.');
        if (!app()->runningInConsole()) {
            abort_unless(\App\Support\StrutturaAccess::query(auth()->user(), true)->whereKey($sid)->exists(), 403);
        }
        return $locked;
    }

    public function version(Customers $customer): ?int
    {
        if (!$customer->exists || !$this->groupFor((int) $customer->struttura_id)) return null;
        $person = DB::table('customer_person_links as l')->join('customer_persons as p', 'p.id', '=', 'l.person_id')
            ->where('l.customer_id', $customer->id)->whereNull('l.detached_at')->first(['p.version', 'p.residence_status']);
        $customer->sharingResidenceStatus = $person?->residence_status;
        return $person ? (int) $person->version : null;
    }

    public function save(Customers $customer, callable $persist): bool
    {
        $group = $this->lockForStructure((int) $customer->struttura_id);
        if (!$group) return $persist();
        $changed = array_intersect(array_keys($customer->getDirty()), self::FIELDS);
        $consentFields = ['privacy_consent', 'privacy_consent_at', 'marketing_consent', 'marketing_consent_at', 'communication_consent', 'communication_consent_at'];
        $consentChanged = array_intersect(array_keys($customer->getDirty()), $consentFields);
        $confirmResidence = !app()->runningInConsole() && request()->boolean('confirm_shared_residence');
        $mutating = $customer->isDirty() || $confirmResidence;
        $link = $customer->exists ? DB::table('customer_person_links')->where('customer_id', $customer->id)->lockForUpdate()->first() : null;
        if ($link) {
            $person = DB::table('customer_persons')->where('id', $link->person_id)->where('group_id', $group->id)->lockForUpdate()->first();
            abort_unless($person, 409, 'Collegamento cliente da verificare.');
            if ($mutating && !$customer->wasRecentlyCreated && !app()->runningInConsole()) {
                request()->validate(['customer_sharing_version' => ['required', 'integer', 'min:1']]);
                abort_unless((int) request('customer_sharing_version') === (int) $person->version, 409, 'Il cliente condiviso è cambiato. Ricarica e verifica i dati.');
            }
            if ($mutating && $customer->sharingVersion !== null && (int) $person->version !== $customer->sharingVersion) {
                abort(409, 'Il cliente è stato aggiornato da un altro hotel. Ricarica la scheda.');
            }
        }
        $result = $persist();
        if (!$result) return false;
        if (!$link) {
            $personId = $this->attach($customer, (int) $group->id, true);
        } else {
            $personId = (int) $person->id;
            if ($changed) {
                $before = $this->decode($person->data);
                $after = array_replace($before, array_intersect_key($customer->getAttributes(), array_flip($changed)));
                DB::table('customer_persons')->where('id', $personId)->update(array_merge($this->keys($after), ['data' => $this->encode($after), 'version' => $person->version + 1, 'updated_at' => now()]));
                $this->log((int) $group->id, 'aggiornamento', ['persona' => $personId, 'hotel' => $customer->struttura_id, 'prima' => $before, 'dopo' => $after]);
                if (array_intersect($changed, self::RESIDENCE)) {
                    DB::table('customer_persons')->where('id', $personId)->update(['residence_status' => $person->residence_status === 'da_confermare' ? 'da_confermare' : 'archivio', 'residence_confirmed_at' => null]);
                    $this->recordResidence($personId, (int) $customer->struttura_id, $after);
                }
            }
        }
        if ($link && !$customer->wasRecentlyCreated && $consentChanged) {
            $localIds = DB::table('customer_person_links')->where('person_id', $personId)->where('struttura_id', $customer->struttura_id)->whereNull('detached_at')->pluck('customer_id');
            DB::table('clienti')->whereIn('id', $localIds)->where('struttura_id', $customer->struttura_id)->update(array_intersect_key($customer->getAttributes(), array_flip($consentFields)));
            $this->log((int) $group->id, 'consensi_locali', ['persona' => $personId, 'hotel' => $customer->struttura_id]);
        }
        if ($link && $mutating && !$changed && !$confirmResidence) {
            DB::table('customer_persons')->where('id', $personId)->increment('version');
        }
        if ($confirmResidence) {
            request()->validate(['confirm_shared_residence' => ['accepted']]);
            if (!$customer->country || !$customer->city) throw ValidationException::withMessages(['confirm_shared_residence' => 'Completa almeno nazione e comune prima di confermare la residenza.']);
            DB::table('customer_persons')->where('id', $personId)->update(['residence_status' => 'confermata', 'residence_confirmed_at' => now()]);
            DB::table('customer_persons')->where('id', $personId)->increment('version');
            $this->log((int) $group->id, 'conferma_residenza', ['persona' => $personId, 'hotel' => $customer->struttura_id]);
        }
        $this->project($personId);
        $customer->sharingVersion = $this->version($customer);
        return true;
    }

    private function attach(Customers $customer, int $groupId, bool $origin): int
    {
        $existingLink = DB::table('customer_person_links')->where('customer_id', $customer->id)->lockForUpdate()->first();
        if ($existingLink) return (int) $existingLink->person_id;
        $data = array_intersect_key($customer->getAttributes(), array_flip(self::FIELDS));
        $matches = [];
        $keys = $this->keys($data);
        $possible = DB::table('customer_persons')->where('group_id', $groupId)->whereNull('merged_into_id')->where(function ($q) use ($keys) {
            $q->whereRaw('1 = 0');
            foreach (['document_key', 'birth_key'] as $field) if ($keys[$field]) $q->orWhere($field, $keys[$field]);
        })->lockForUpdate()->get();
        foreach ($possible as $person) {
            if ($this->sameIdentity($data, $this->decode($person->data))) $matches[] = $person;
        }
        if (count($matches) === 1) {
            $person = $matches[0];
            $stored = $this->decode($person->data);
            $previousResidence = array_intersect_key($stored, array_flip(self::RESIDENCE));
            // Integra solo i mancanti; blocchi discordanti restano nel registro cifrato.
            $residenceConflict = $person->residence_status !== 'confermata' && $this->conflicts($stored, $data, self::RESIDENCE);
            $preparing = DB::table('customer_sharing_groups')->where('id', $groupId)->value('stato') === 'preparazione';
            $primary = $preparing && count(array_filter($data, fn ($v) => $v !== null && $v !== '')) > count(array_filter($stored, fn ($v) => $v !== null && $v !== '')) ? $data : $stored;
            $stored = $this->complete($primary, $primary === $data ? $stored : $data);
            if ($residenceConflict) foreach (self::RESIDENCE as $field) $stored[$field] = null;
            // Non ripopolare una residenza già in revisione con un'altra origine.
            if ($person->residence_status === 'da_confermare') foreach (self::RESIDENCE as $field) $stored[$field] = null;
            $residenceChanged = $previousResidence !== array_intersect_key($stored, array_flip(self::RESIDENCE));
            $residenceStatus = $residenceConflict || $person->residence_status === 'da_confermare' ? 'da_confermare' : ($residenceChanged ? 'archivio' : $person->residence_status);
            DB::table('customer_persons')->where('id', $person->id)->update(array_merge($this->keys($stored), ['data' => $this->encode($stored), 'version' => $person->version + 1, 'updated_at' => now(),
                'residence_status' => $residenceStatus, 'residence_confirmed_at' => $residenceChanged || $residenceStatus === 'da_confermare' ? null : $person->residence_confirmed_at]));
            $personId = (int) $person->id;
        } else {
            $personId = DB::table('customer_persons')->insertGetId(array_merge($keys, ['group_id' => $groupId, 'data' => $this->encode($data), 'version' => 1, 'created_at' => now(), 'updated_at' => now()]));
        }
        DB::table('customer_person_links')->insert(['person_id' => $personId, 'customer_id' => $customer->id, 'struttura_id' => $customer->struttura_id, 'provenienza' => $origin, 'created_at' => now(), 'updated_at' => now()]);
        $this->recordResidence($personId, (int) $customer->struttura_id, $data);
        $this->log($groupId, 'collegamento', ['persona' => $personId, 'cliente' => $customer->id, 'hotel' => $customer->struttura_id, 'codice_originale' => $customer->numero_cliente, 'originale' => $customer->getAttributes()]);
        return $personId;
    }

    public function sameIdentity(array $a, array $b): bool
    {
        $norm = fn ($v) => mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) $v)));
        foreach (['name', 'surname', 'nac_reg'] as $field) if (empty($a[$field]) || empty($b[$field]) || $norm($a[$field]) !== $norm($b[$field])) return false;
        $birthDate = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $a['nac_reg']);
        if (!$birthDate || $birthDate->format('Y-m-d') !== $a['nac_reg'] || $birthDate > new \DateTimeImmutable('today')) return false;
        foreach (['sex', 'country_reg', 'city_reg', 'prov_reg'] as $field) if (!empty($a[$field]) && !empty($b[$field]) && $norm($a[$field]) !== $norm($b[$field])) return false;
        $document = !empty($a['num_doc_reg']) && !empty($a['type_doc_reg']) && $norm($a['num_doc_reg']) === $norm($b['num_doc_reg'] ?? '') && $norm($a['type_doc_reg']) === $norm($b['type_doc_reg'] ?? '');
        $birth = !empty($a['city_reg']) && !empty($a['country_reg']) && $norm($a['city_reg']) === $norm($b['city_reg'] ?? '') && $norm($a['country_reg']) === $norm($b['country_reg'] ?? '');
        return $document || $birth;
    }

    private function project(int $personId): void
    {
        $person = DB::table('customer_persons')->where('id', $personId)->lockForUpdate()->first();
        abort_unless($person && $person->merged_into_id === null, 409, 'Identità non più attiva. Ricarica il cliente.');
        $data = $this->decode($person->data);
        foreach ($this->members((int) $person->group_id, true) as $sid) {
            $links = DB::table('customer_person_links')->where('person_id', $personId)->where('struttura_id', $sid)->whereNull('detached_at')->orderBy('customer_id')->lockForUpdate()->get();
            if ($links->isEmpty()) {
                // Riferimento locale: nessun consenso, classificazione, nota o soggiorno copiato.
                $code = 'COND-'.$personId.'-'.$sid;
                for ($attempt = 0; strlen($code) > 20 || DB::table('clienti')->where('struttura_id', $sid)->where('numero_cliente', $code)->lockForUpdate()->first(['id']) !== null; $attempt++) {
                    if ($attempt >= 10) throw new \RuntimeException('Impossibile assegnare un codice cliente locale univoco.');
                    $code = 'C'.substr(hash_hmac('sha256', $personId.'|'.$sid.'|'.$attempt, (string) config('app.key')), 0, 19);
                }
                $id = DB::table('clienti')->insertGetId(array_replace($data, ['struttura_id' => $sid, 'type_housed' => 'Componente', 'numero_cliente' => $code, 'created_at' => now(), 'updated_at' => now()]));
                DB::table('customer_person_links')->insert(['person_id' => $personId, 'customer_id' => $id, 'struttura_id' => $sid, 'provenienza' => false, 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('clienti')->whereIn('id', $links->pluck('customer_id'))->where('struttura_id', $sid)->update($data);
                if ($links->count() > 1) {
                    $rows = DB::table('clienti')->whereIn('id', $links->pluck('customer_id'))->where('struttura_id', $sid)->lockForUpdate()->get();
                    $consents = [];
                    foreach (['privacy', 'marketing', 'communication'] as $kind) {
                        $flag = $kind.'_consent'; $date = $flag.'_at';
                        $granted = $rows->every(fn ($row) => (bool) $row->$flag);
                        $dates = $rows->pluck($date)->unique();
                        $consents[$flag] = $granted;
                        $consents[$date] = $granted && $dates->count() === 1 ? $dates->first() : null;
                    }
                    DB::table('clienti')->whereIn('id', $links->pluck('customer_id'))->where('struttura_id', $sid)->update($consents);
                }
            }
        }
    }

    public function uniqueQuery(\Illuminate\Database\Eloquent\Builder $query, int $sid): \Illuminate\Database\Eloquent\Builder
    {
        $this->visibleQuery($query);
        if (!$this->groupFor($sid)) return $query;
        return $query->whereNotIn('clienti.id', DB::table('customer_person_links as l')
            ->join('customer_person_links as first', function ($join) { $join->on('first.person_id', '=', 'l.person_id')->on('first.struttura_id', '=', 'l.struttura_id')->on('first.customer_id', '<', 'l.customer_id'); })
            ->where('l.struttura_id', $sid)->whereNull('l.detached_at')->whereNull('first.detached_at')->select('l.customer_id'));
    }

    public function visibleQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        if (!$this->available()) return $query;
        return $query->whereNotIn('clienti.id', DB::table('customer_person_links')->whereNotNull('detached_at')->select('customer_id'));
    }

    public function packageQuery(\Illuminate\Database\Eloquent\Builder $query, int $sid): \Illuminate\Database\Eloquent\Builder
    {
        if (!$this->groupFor($sid)) return $query;
        return $this->uniqueQuery($query, $sid)->where(function ($q) use ($sid) {
            $q->whereNotIn('clienti.id', DB::table('customer_person_links')->select('customer_id'))
                ->orWhereExists(function ($local) use ($sid) {
                    $local->selectRaw('1')->from('customer_person_links as representative')
                        ->join('customer_person_links as origin', function ($join) { $join->on('origin.person_id', '=', 'representative.person_id')->on('origin.struttura_id', '=', 'representative.struttura_id'); })
                        ->whereColumn('representative.customer_id', 'clienti.id')->where('origin.struttura_id', $sid)->whereNull('origin.detached_at')
                        ->where(fn ($used) => $used->where('origin.provenienza', true)->orWhereExists(fn ($stay) => $stay->selectRaw('1')->from('schedina')
                            ->whereColumn('schedina.customer_id', 'origin.customer_id')->where('schedina.struttura_id', $sid)->where('schedina.is_arrive', false)
                            ->where(fn ($circuit) => $circuit->where('schedina.circuito', 'schedina')->orWhereNull('schedina.circuito'))));
                });
        });
    }

    public function assertDeletable(Customers $customer): void
    {
        if ($this->available() && (DB::table('customer_person_links')->where('customer_id', $customer->id)->exists()
            || DB::table('customer_sharing_packages')->where('customer_id', $customer->id)->exists())) {
            throw ValidationException::withMessages(['cliente' => 'Cliente condiviso: conserva i collegamenti e usa la gestione del proprietario.']);
        }
    }

    public function registerOrigin(Customers $customer, array $payload): void
    {
        $group = $this->lockForStructure((int) $customer->struttura_id);
        if (!$group) return;
        $link = DB::table('customer_person_links')->where('customer_id', $customer->id)->whereNull('detached_at')->lockForUpdate()->first();
        if (!$link) {
            $customer->save();
            $link = DB::table('customer_person_links')->where('customer_id', $customer->id)->whereNull('detached_at')->lockForUpdate()->first();
        }
        DB::table('customer_person_links')->where('id', $link->id)->update(['provenienza' => true, 'updated_at' => now()]);
        $this->log((int) $group->id, 'provenienza_import', ['persona' => $link->person_id, 'cliente' => $customer->id, 'hotel' => $customer->struttura_id, 'sorgente' => $payload]);
    }

    private function owner(User $user): int
    {
        abort_unless($user->attivo && $user->isProprietario() && $user->proprietario_id, 403);
        return (int) $user->proprietario_id;
    }

    public function fingerprint(array $ids, bool $current = false): string
    {
        sort($ids);
        $rows = DB::table('clienti')->whereIn('struttura_id', $ids)->orderBy('id')->when($current, fn ($q) => $q->lockForUpdate())->get();
        return hash_hmac('sha256', json_encode([$ids, $rows], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function configure(User $user, ?int $groupId, int $version, string $name, array $ids, ?string $fingerprint = null): int
    {
        $owner = $this->owner($user);
        abort_unless(config('customer_sharing.enabled', false), 409, 'Creazione e ampliamento dei gruppi non ancora abilitati. I gruppi già attivi restano operativi.');
        abort_unless($this->available(), 409, 'Schema condivisione non ancora installato.');
        $ids = array_values(array_unique(array_map('intval', $ids))); sort($ids);
        if (count($ids) < 2) throw ValidationException::withMessages(['strutture' => 'Seleziona almeno due strutture.']);
        return DB::transaction(function () use ($owner, $groupId, $version, $name, $ids, $fingerprint) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            if ($groupId) {
                $group = DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->lockForUpdate()->first();
                abort_unless($group, 404);
                abort_unless((int) $group->version === $version && $group->stato === 'attivo', 409, 'Gruppo aggiornato: ricarica il riepilogo.');
                $previous = $this->members($groupId, true);
                if (array_diff($previous, $ids)) throw ValidationException::withMessages(['strutture' => 'Per rimuovere un hotel usa la separazione con conferma del pacchetto.']);
            } else {
                $previous = [];
            }
            $valid = DB::table('struttura')->whereIn('id', $ids)->where('proprietario_id', $owner)->where('attiva', true)->whereNull('deleted_at')->orderBy('id')->lockForUpdate()->get()->pluck('id')->map(fn ($id) => (int) $id)->all();
            abort_unless($valid === $ids, 403);
            if ($fingerprint !== null) abort_unless(hash_equals($fingerprint, $this->fingerprint($ids, true)), 409, 'I clienti sono cambiati dopo il riepilogo. Ripeti la verifica.');
            $foreign = DB::table('customer_sharing_memberships')->whereIn('struttura_id', $ids)->when($groupId, fn ($q) => $q->where('group_id', '<>', $groupId))->exists();
            if ($foreign) throw ValidationException::withMessages(['strutture' => 'Una struttura partecipa già a un altro gruppo.']);
            if (!$groupId) $groupId = DB::table('customer_sharing_groups')->insertGetId(['proprietario_id' => $owner, 'nome' => $name, 'stato' => 'preparazione', 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
            foreach (array_diff($ids, $previous) as $sid) DB::table('customer_sharing_memberships')->insert(['group_id' => $groupId, 'struttura_id' => $sid, 'proprietario_id' => $owner, 'created_at' => now(), 'updated_at' => now()]);
            foreach ($this->visibleQuery(Customers::withoutGlobalScopes())->whereIn('struttura_id', $ids)->orderBy('id')->lockForUpdate()->get() as $customer) $this->attach($customer, $groupId, true);
            DB::table('customer_sharing_groups')->where('id', $groupId)->update(['stato' => 'attivo', 'nome' => $name, 'version' => $groupId && isset($group) ? $version + 1 : 1, 'updated_at' => now()]);
            foreach (DB::table('customer_persons')->where('group_id', $groupId)->whereNull('merged_into_id')->pluck('id') as $personId) $this->project((int) $personId);
            $this->log($groupId, 'configurazione', ['strutture_prima' => $previous, 'strutture_dopo' => $ids]);
            return $groupId;
        }, 3);
    }

    public function separate(User $user, int $groupId, int $sid, int $version, bool $deliver): void
    {
        $owner = $this->owner($user);
        DB::transaction(function () use ($owner, $groupId, $sid, $version, $deliver) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            $group = DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->lockForUpdate()->first();
            abort_unless($group, 404);
            abort_unless((int) $group->version === $version && in_array($sid, $this->members($groupId, true), true), 409, 'Condivisione cambiata: ricarica.');
            // Mantiene il pacchetto locale; i soli riferimenti di consultazione non lo ampliano.
            $links = DB::table('customer_person_links')->where('struttura_id', $sid)->whereNull('detached_at')->get();
            $kept = []; $removed = [];
            $originalsByCustomer = [];
            foreach (DB::table('customer_sharing_operations')->where('group_id', $groupId)->whereIn('tipo', ['collegamento', 'provenienza_import'])->get() as $operation) {
                $origin = $this->decode($operation->payload);
                if ((int) $origin['hotel'] === $sid) $originalsByCustomer[(int) $origin['cliente']][] = ['tipo_fonte' => $operation->tipo,
                    'codice_originale' => $origin['codice_originale'] ?? ($origin['sorgente']['numero_cliente'] ?? null),
                    'dati' => $origin['originale'] ?? $origin['sorgente']];
            }
            foreach ($links as $link) {
                $used = DB::table('schedina')->where('struttura_id', $sid)->where('customer_id', $link->customer_id)->exists()
                    || DB::table('componenti')->where('struttura_id', $sid)->where('customer_id', $link->customer_id)->exists()
                    || DB::table('cestino_items')->where('struttura_id', $sid)->where(fn ($q) => $q->where('payload->customer_id', $link->customer_id)->orWhere('payload->schedina->customer_id', $link->customer_id))->exists()
                    || DB::table('customer_import_rows as r')->join('customer_import_batches as b', 'b.id', '=', 'r.batch_id')->where('b.struttura_id', $sid)
                        ->where(fn ($q) => $q->where('r.imported_customer_id', $link->customer_id)->orWhere('r.duplicate_customer_id', $link->customer_id))->exists();
                if ($link->provenienza || $used) {
                    if ($deliver) {
                        $person = DB::table('customer_persons')->where('id', $link->person_id)->first();
                        $versions = DB::table('customer_person_versions')->whereIn('person_id', $this->aliases($groupId, (int) $link->person_id))->get()->map(fn ($v) => [
                            'hotel_fonte' => $v->struttura_id, 'dati' => $this->decode($v->data), 'registrata_il' => $v->created_at])->all();
                        $originals = $originalsByCustomer[(int) $link->customer_id] ?? [];
                        DB::table('customer_sharing_packages')->insert(['group_id' => $groupId, 'struttura_id' => $sid, 'customer_id' => $link->customer_id,
                            'data' => $this->encode(['anagrafica_locale' => (array) DB::table('clienti')->where('id', $link->customer_id)->first(),
                                'fonti_locali_originali' => $originals, 'residenze' => $versions, 'stato_residenza' => $person->residence_status, 'confermata_il' => $person->residence_confirmed_at]),
                            'created_at' => now(), 'updated_at' => now()]);
                    }
                    DB::table('customer_person_links')->where('id', $link->id)->delete();
                    $kept[] = $link->customer_id;
                }
                else {
                    // Oscura la sola proiezione di consultazione, senza cancellare dati o riferimenti.
                    DB::table('customer_person_links')->where('id', $link->id)->update(['detached_at' => now(), 'updated_at' => now()]);
                    $removed[] = $link->customer_id;
                }
            }
            DB::table('customer_sharing_memberships')->where('group_id', $groupId)->where('struttura_id', $sid)->delete();
            DB::table('customer_sharing_groups')->where('id', $groupId)->update(['version' => $version + 1, 'updated_at' => now()]);
            $this->log($groupId, 'separazione', ['hotel' => $sid, 'consegna_confermata' => $deliver, 'clienti_conservati' => $kept, 'proiezioni_tecniche_oscurate' => $removed]);
        }, 3);
    }

    public function history(Customers $customer): ?array
    {
        $group = $this->groupFor((int) $customer->struttura_id);
        if (!$group) return null;
        $link = DB::table('customer_person_links')->where('customer_id', $customer->id)->first();
        if (!$link) return null;
        return DB::table('customer_person_links')->where('person_id', $link->person_id)->whereNull('detached_at')->whereIn('struttura_id', $this->members((int) $group->id))->pluck('customer_id')->all();
    }

    public function linkStay(\App\Models\Schedina $sheet): void
    {
        if ($sheet->is_arrive || !in_array($sheet->circuito, [null, 'schedina'], true)) return;
        if (!$this->lockForStructure((int) $sheet->struttura_id)) return;
        $customer = app(CustomerImportService::class)->clienteDaSchedinaWeb($sheet);
        if ((int) $sheet->customer_id !== (int) $customer->id) $sheet->forceFill(['customer_id' => $customer->id])->save();
    }

    public function candidates(int $groupId, bool $includeReviewed = false): array
    {
        $pairs = DB::table('customer_persons as a')->join('customer_persons as b', function ($join) {
            $join->on('a.group_id', '=', 'b.group_id')->on('a.id', '<', 'b.id')->on('a.name_key', '=', 'b.name_key');
        })->where('a.group_id', $groupId)->whereNull('a.merged_into_id')->whereNull('b.merged_into_id')
            ->when(!$includeReviewed, fn ($q) => $q->whereNotExists(function ($review) {
                $review->selectRaw('1')->from('customer_duplicate_reviews as r')->whereColumn('r.group_id', 'a.group_id')
                    ->whereColumn('r.first_id', 'a.id')->whereColumn('r.second_id', 'b.id')
                    ->whereColumn('r.first_version', 'a.version')->whereColumn('r.second_version', 'b.version');
            }))
            ->orderBy('a.id')->orderBy('b.id')->limit(100)->get(['a.id as first_id', 'b.id as second_id', 'a.data as first_data', 'b.data as second_data', 'a.version as first_version', 'b.version as second_version']);
        return $pairs->map(fn ($pair) => ['prima' => $pair->first_id, 'seconda' => $pair->second_id,
            'versione_prima' => $pair->first_version, 'versione_seconda' => $pair->second_version,
            'dati_prima' => $this->decode($pair->first_data), 'dati_seconda' => $this->decode($pair->second_data)])->all();
    }

    public function keepSeparate(User $user, int $groupId, int $aId, int $bId, int $aVersion, int $bVersion): void
    {
        $owner = $this->owner($user);
        DB::transaction(function () use ($owner, $groupId, $aId, $bId, $aVersion, $bVersion) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            abort_unless(DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->where('stato', 'attivo')->lockForUpdate()->first(), 404);
            abort_unless($aId !== $bId, 422);
            if ($aId > $bId) { [$aId, $bId] = [$bId, $aId]; [$aVersion, $bVersion] = [$bVersion, $aVersion]; }
            $people = DB::table('customer_persons')->where('group_id', $groupId)->whereNull('merged_into_id')->whereIn('id', [$aId, $bId])->get()->keyBy('id');
            abort_unless($people->count() === 2, 404);
            abort_unless((int) $people[$aId]->version === $aVersion && (int) $people[$bId]->version === $bVersion, 409, 'Le schede sono cambiate: rivedi il confronto.');
            DB::table('customer_duplicate_reviews')->updateOrInsert(['group_id' => $groupId, 'first_id' => $aId, 'second_id' => $bId],
                ['first_version' => $aVersion, 'second_version' => $bVersion, 'created_at' => now(), 'updated_at' => now()]);
            $this->log($groupId, 'mantieni_separati', ['prima' => $aId, 'seconda' => $bId, 'versioni' => [$aVersion, $bVersion]]);
        }, 3);
    }

    public function merge(User $user, int $groupId, int $primaryId, int $secondaryId, int $primaryVersion, int $secondaryVersion): void
    {
        $owner = $this->owner($user);
        DB::transaction(function () use ($owner, $groupId, $primaryId, $secondaryId, $primaryVersion, $secondaryVersion) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            $group = DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->where('stato', 'attivo')->lockForUpdate()->first();
            abort_unless($group && $primaryId !== $secondaryId, 404);
            $persons = DB::table('customer_persons')->where('group_id', $groupId)->whereIn('id', [$primaryId, $secondaryId])->whereNull('merged_into_id')->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($persons->count() === 2, 404);
            $primary = $persons[$primaryId]; $secondary = $persons[$secondaryId];
            abort_unless((int) $primary->version === $primaryVersion && (int) $secondary->version === $secondaryVersion, 409, 'Le schede sono cambiate. Rivedi il confronto.');
            $a = $this->decode($primary->data); $b = $this->decode($secondary->data);
            // Le identità discordanti richiedono prima la correzione della singola scheda.
            foreach (['name', 'surname', 'nac_reg', 'sex', 'country_reg', 'city_reg', 'prov_reg'] as $field) {
                if (!empty($a[$field]) && !empty($b[$field]) && mb_strtoupper(trim((string) $a[$field])) !== mb_strtoupper(trim((string) $b[$field]))) {
                    throw ValidationException::withMessages(['fusione' => 'Identità discordanti: verifica e correggi prima il campo '.$field.'.']);
                }
            }
            $merged = $this->complete($a, $b);
            $residenceConflict = $this->conflicts($a, $b, self::RESIDENCE) || $primary->residence_status === 'da_confermare' || $secondary->residence_status === 'da_confermare';
            if ($residenceConflict) foreach (self::RESIDENCE as $field) $merged[$field] = null;
            $sourceLinks = DB::table('customer_person_links')->where('person_id', $secondaryId)->whereNull('detached_at')->pluck('id')->all();
            DB::table('customer_person_links')->whereIn('id', $sourceLinks)->update(['person_id' => $primaryId, 'updated_at' => now()]);
            DB::table('customer_persons')->where('id', $primaryId)->update(array_merge($this->keys($merged), ['data' => $this->encode($merged), 'version' => $primaryVersion + 1, 'updated_at' => now(),
                'residence_status' => $residenceConflict ? 'da_confermare' : $primary->residence_status, 'residence_confirmed_at' => $residenceConflict ? null : $primary->residence_confirmed_at]));
            DB::table('customer_persons')->where('id', $secondaryId)->update(['merged_into_id' => $primaryId, 'version' => $secondaryVersion + 1, 'updated_at' => now()]);
            $this->project($primaryId);
            $this->log($groupId, 'fusione', ['principale' => $primaryId, 'secondaria' => $secondaryId, 'versione_dopo' => $primaryVersion + 1, 'versione_secondaria_dopo' => $secondaryVersion + 1,
                'prima' => $a, 'seconda' => $b, 'link_secondaria' => $sourceLinks,
                'residenza_prima' => [$primary->residence_status, $primary->residence_confirmed_at], 'residenza_seconda' => [$secondary->residence_status, $secondary->residence_confirmed_at]]);
            DB::table('customer_sharing_groups')->where('id', $groupId)->increment('version');
        }, 3);
    }

    public function undoMerge(User $user, int $groupId, int $operationId): void
    {
        $owner = $this->owner($user);
        DB::transaction(function () use ($owner, $groupId, $operationId) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            $group = DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->where('stato', 'attivo')->lockForUpdate()->first();
            abort_unless($group, 404);
            $operation = DB::table('customer_sharing_operations')->where('id', $operationId)->where('group_id', $groupId)->where('tipo', 'fusione')->first();
            abort_unless($operation, 404);
            $p = $this->decode($operation->payload);
            $a = DB::table('customer_persons')->where('id', $p['principale'])->where('group_id', $groupId)->lockForUpdate()->first();
            $b = DB::table('customer_persons')->where('id', $p['secondaria'])->where('group_id', $groupId)->lockForUpdate()->first();
            abort_unless($a && $b && (int) $a->version === $p['versione_dopo'] && (int) $b->version === $p['versione_secondaria_dopo'] && (int) $b->merged_into_id === (int) $a->id, 409, 'La fusione ha aggiornamenti successivi: serve una revisione dei collegamenti.');
            $links = DB::table('customer_person_links')->whereIn('id', $p['link_secondaria'])->where('person_id', $a->id)->whereNull('detached_at')->get();
            abort_unless($links->count() === count($p['link_secondaria']), 409, 'I collegamenti sono cambiati.');
            $ids = DB::table('customer_person_links')->whereIn('person_id', [$a->id, $b->id])->pluck('customer_id');
            $newStay = DB::table('schedina')->whereIn('customer_id', $ids)->where('created_at', '>=', $operation->created_at)->exists()
                || DB::table('componenti')->whereIn('customer_id', $ids)->where('created_at', '>=', $operation->created_at)->exists();
            abort_if($newStay, 409, 'Sono presenti nuove registrazioni: verificane prima l’attribuzione.');
            DB::table('customer_person_links')->whereIn('id', $p['link_secondaria'])->update(['person_id' => $b->id, 'updated_at' => now()]);
            foreach ([[$a, $p['prima']], [$b, $p['seconda']]] as [$person, $data]) {
                $residence = (int) $person->id === (int) $a->id ? $p['residenza_prima'] : $p['residenza_seconda'];
                DB::table('customer_persons')->where('id', $person->id)->update(array_merge($this->keys($data), ['data' => $this->encode($data), 'merged_into_id' => null, 'version' => $person->version + 1, 'updated_at' => now(), 'residence_status' => $residence[0], 'residence_confirmed_at' => $residence[1]]));
                $this->project((int) $person->id);
            }
            $this->log($groupId, 'annullamento_fusione', ['operazione' => $operationId]);
            DB::table('customer_sharing_groups')->where('id', $groupId)->increment('version');
        }, 3);
    }

    private function keys(array $data): array
    {
        $hash = function (array $fields) use ($data) {
            $values = [];
            foreach ($fields as $field) {
                $value = mb_strtoupper(preg_replace('/\s+/u', ' ', trim((string) ($data[$field] ?? ''))));
                if ($value === '') return null;
                $values[] = $value;
            }
            return hash_hmac('sha256', json_encode($values, JSON_THROW_ON_ERROR), (string) config('app.key'));
        };
        return ['document_key' => $hash(['name', 'surname', 'nac_reg', 'type_doc_reg', 'num_doc_reg']),
            'birth_key' => $hash(['name', 'surname', 'nac_reg', 'country_reg', 'city_reg']), 'name_key' => $hash(['name', 'surname'])];
    }

    private function complete(array $primary, array $secondary): array
    {
        $blocks = [
            ['country', 'city', 'region', 'province', 'cap', 'address', 'number'],
            ['type_doc_reg', 'num_doc_reg', 'date_pub_reg', 'expire_reg', 'country_doc_reg', 'city_doc_reg'],
        ];
        $blocked = [];
        foreach ($blocks as $fields) {
            $conflict = false;
            foreach ($fields as $field) if (!empty($primary[$field]) && !empty($secondary[$field]) && mb_strtoupper(trim((string) $primary[$field])) !== mb_strtoupper(trim((string) $secondary[$field]))) $conflict = true;
            if ($conflict) $blocked = array_merge($blocked, $fields);
        }
        foreach (self::FIELDS as $field) if (!in_array($field, $blocked, true) && empty($primary[$field]) && !empty($secondary[$field])) $primary[$field] = $secondary[$field];
        return $primary;
    }

    private function conflicts(array $a, array $b, array $fields): bool
    {
        foreach ($fields as $field) if (!empty($a[$field]) && !empty($b[$field]) && mb_strtoupper(trim((string) $a[$field])) !== mb_strtoupper(trim((string) $b[$field]))) return true;
        return false;
    }

    private function recordResidence(int $personId, int $sid, array $data): void
    {
        $residence = array_intersect_key($data, array_flip(self::RESIDENCE));
        if (!array_filter($residence, fn ($v) => $v !== null && $v !== '')) return;
        DB::table('customer_person_versions')->insert(['person_id' => $personId, 'struttura_id' => $sid, 'data' => $this->encode($residence), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function aliases(int $groupId, int $personId): array
    {
        $ids = [$personId];
        do {
            $found = DB::table('customer_persons')->where('group_id', $groupId)->whereIn('merged_into_id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $next = array_values(array_unique(array_merge($ids, $found)));
            $changed = count($next) > count($ids); $ids = $next;
        } while ($changed);
        return $ids;
    }

    public function residences(int $groupId): array
    {
        return DB::table('customer_persons')->where('group_id', $groupId)->whereNull('merged_into_id')->where('residence_status', 'da_confermare')->orderBy('id')->limit(30)->get()->map(function ($person) use ($groupId) {
            $versions = DB::table('customer_person_versions as v')->join('struttura as s', 's.id', '=', 'v.struttura_id')
                ->whereIn('v.person_id', $this->aliases($groupId, (int) $person->id))->orderBy('v.id')->get(['v.id', 'v.data', 's.nome_struttura']);
            return ['id' => $person->id, 'version' => $person->version, 'data' => $this->decode($person->data),
                'alternative' => $versions->map(fn ($v) => ['id' => $v->id, 'hotel' => $v->nome_struttura, 'data' => $this->decode($v->data)])->all()];
        })->all();
    }

    public function confirmResidence(User $user, int $groupId, int $personId, int $versionId, int $expectedVersion): void
    {
        $owner = $this->owner($user);
        DB::transaction(function () use ($owner, $groupId, $personId, $versionId, $expectedVersion) {
            DB::table('proprietari')->where('id', $owner)->lockForUpdate()->first();
            $group = DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->where('stato', 'attivo')->lockForUpdate()->first();
            abort_unless($group, 404);
            $person = DB::table('customer_persons')->where('id', $personId)->where('group_id', $groupId)->whereNull('merged_into_id')->lockForUpdate()->first();
            abort_unless($person, 404);
            abort_unless((int) $person->version === $expectedVersion, 409, 'Il cliente è cambiato. Rivedi le alternative.');
            $selected = DB::table('customer_person_versions')->where('id', $versionId)->whereIn('person_id', $this->aliases($groupId, $personId))->first();
            abort_unless($selected, 404);
            $residence = $this->decode($selected->data);
            if (empty($residence['country']) || empty($residence['city'])) throw ValidationException::withMessages(['residenza' => 'La versione scelta è incompleta: completala dalla scheda cliente.']);
            $data = array_replace($this->decode($person->data), array_fill_keys(self::RESIDENCE, null), $residence);
            DB::table('customer_persons')->where('id', $personId)->update(['data' => $this->encode($data), 'version' => $expectedVersion + 1, 'residence_status' => 'confermata', 'residence_confirmed_at' => now(), 'updated_at' => now()]);
            $this->project($personId);
            $this->log($groupId, 'conferma_residenza', ['persona' => $personId, 'versione_residenza' => $versionId, 'prima' => $this->decode($person->data)]);
        }, 3);
    }

    public function sourceArchive(User $user, int $groupId): array
    {
        $owner = $this->owner($user);
        abort_unless(DB::table('customer_sharing_groups')->where('id', $groupId)->where('proprietario_id', $owner)->exists(), 404);
        return DB::table('customer_sharing_operations')->where('group_id', $groupId)->whereIn('tipo', ['collegamento', 'provenienza_import'])->orderBy('id')->get()->map(function ($operation) {
            $payload = $this->decode($operation->payload);
            return ['tipo_fonte' => $operation->tipo, 'persona' => $payload['persona'], 'hotel' => $payload['hotel'], 'cliente_locale' => $payload['cliente'],
                'codice_originale' => $payload['codice_originale'] ?? ($payload['sorgente']['numero_cliente'] ?? null),
                'registrato_il' => $operation->created_at, 'dati_originali' => isset($payload['originale']) ? array_intersect_key($payload['originale'], array_flip(self::FIELDS)) : $payload['sorgente']];
        })->all();
    }

    public function encode(array $value): string { return Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR)); }
    public function decode(string $value): array { return json_decode(Crypt::decryptString($value), true, 512, JSON_THROW_ON_ERROR); }
    private function log(int $groupId, string $type, array $payload): void
    {
        DB::table('customer_sharing_operations')->insert(['group_id' => $groupId, 'user_id' => auth()->id(), 'tipo' => $type, 'payload' => $this->encode($payload), 'created_at' => now(), 'updated_at' => now()]);
    }
}
