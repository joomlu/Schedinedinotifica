<?php

namespace App\Http\Controllers;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebCheckinController extends SchedinaController
{
    private const CAMPI_RISERVATI = ['struttura_id', 'customer_id', 'fonte_prenotazione', 'id_prenotazione_esterna', 'agganciata_il', 'agganciata_da', 'observation', 'customer_observation', 'customer_anag_observation', 'customer_type_housed', 'customer_group', 'customer_subgroup', 'customer_subgroup1', 'room', 'beds', 'camere'];

    public function index()
    {
        session()->forget(['success', 'warning', 'error']);
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $q = trim((string) request()->query('q', ''));
        $stato = trim((string) request()->query('stato', ''));


        $storico = request()->routeIs('web_checkin.storico');
        $baseQuery = WebCheckinRichiesta::query()
            ->with(['schedina', 'struttura'])
            ->when($storico, fn ($q) => $q->storiche(), fn ($q) => $q->whereNotIn('id', WebCheckinRichiesta::query()->storiche()->select('id')))
            ->where('struttura_id', $strutturaId)
            ->when($stato !== '', fn ($query) => $query->where('stato', $stato))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('codice', 'like', $like)
                        ->orWhere('numero_prenotazione', 'like', $like)
                        ->orWhere('nome_referente', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('whatsapp', 'like', $like)
                        ->orWhere('stato', 'like', $like);
                });
            });

        $totali = (clone $baseQuery)->get(['id', 'stato']);

        $richieste = $baseQuery
            ->orderByDesc('codice')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $richieste->getCollection()->transform(function (WebCheckinRichiesta $richiesta) {
            $richiesta->setRelation('schedina', $this->linkedSchedinaForRichiesta($richiesta));
            $richiesta->progresso = $this->progresso($richiesta);
            return $richiesta;
        });

        return response()->view('web-checkin.index', [
            'richieste' => $richieste,
            'storico' => $storico,
            'statoFiltro' => $stato,
            'schedePrecedenti' => $storico ? null : $this->schedeWebPrecedenti((int) $strutturaId)->orderByDesc('id')->simplePaginate(10, ['*'], 'schede_precedenti'),
            'totaliWebCheckin' => [
                'da_inviare' => $totali->where('stato', 'da_inviare')->count(),
                'in_compilazione' => $totali->where('stato', 'in_compilazione')->count(),
                'compilato' => $totali->where('stato', 'compilato')->count(),
                'convertito' => $totali->where('stato', 'convertito')->count(),
            ],
        ])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function create()
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        request()->validate(['scheda_precedente' => ['nullable', 'integer', 'min:1']]);
        $precedente = request()->filled('scheda_precedente') ? $this->schedeWebPrecedenti((int) $strutturaId)->findOrFail(request('scheda_precedente')) : null;
        $richiesta = new WebCheckinRichiesta;
        if ($precedente) $richiesta->fill(['numero_prenotazione' => $precedente->id_prenotazione_esterna, 'email' => $precedente->customer_email, 'arrivo' => $precedente->arrive, 'partenza' => $precedente->departure, 'quantita_persone' => $precedente->cant_people]);
        return response()->view('web-checkin.form', [
            'richiesta' => $richiesta,
            'schedaPrecedente' => $precedente,
            'versioneSchedaPrecedente' => $precedente ? $this->versioneSchedaPrecedente($precedente) : null,
            'componentiPrecedenti' => $precedente ? $this->componentiForSchedina($precedente)->get() : collect(),
            'publicUrl' => null,
            'mailSubject' => null,
            'mailBody' => null,
            'mailToUrl' => null,
            'whatsappBody' => null,
            'whatsAppUrl' => null,
        ])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function store(Request $request)
    {
        $strutturaId = \App\Support\StrutturaAccess::resolve($request)->id;

        return DB::transaction(function () use ($request, $strutturaId) {
            if (! $strutturaId) {
                return back()->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.'])->withInput();
            }

            $data = $this->validateRichiesta($request);
            $struttura = Struttura::whereKey($strutturaId)->lockForUpdate()->firstOrFail();

            $request->validate(['scheda_precedente' => ['nullable', 'integer', 'min:1']]);
            if ($request->filled('scheda_precedente')) {
                $request->validate(['versione_scheda_precedente' => ['required', 'string', 'size:64']]);
                $schedina = $this->schedeWebPrecedenti((int) $strutturaId)->lockForUpdate()->findOrFail($request->input('scheda_precedente'));
                abort_unless(hash_equals($this->versioneSchedaPrecedente($schedina), $request->input('versione_scheda_precedente')), 409, 'I dati precedenti sono cambiati. Riapri la preparazione della richiesta.');
                $schedina->fill(['arrive' => $data['arrivo'], 'departure' => $data['partenza'], 'cant_people' => $data['quantita_persone'], 'id_prenotazione_esterna' => $data['numero_prenotazione']])->save();
            } else {
            $schedina = Schedina::query()->create([
                'struttura_id' => $strutturaId,
                'circuito' => 'web',
                'scheda' => $this->nextSchedaCode($strutturaId, 'web'),
                'arrive' => $data['arrivo'],
                'departure' => $data['partenza'],
                'cant_people' => $data['quantita_persone'],
                'customer_email' => $data['email'],
                'customer_cellphone' => $data['whatsapp'] ?? null,
                'fonte_prenotazione' => 'WEB CHECK-IN',
                'id_prenotazione_esterna' => $data['numero_prenotazione'],
                'is_arrive' => 0,
            ]);
            $this->syncCircuitNumbering($strutturaId, null, 'web');
            }

            $richiesta = WebCheckinRichiesta::query()->create([
                'struttura_id' => $strutturaId,
                'schedina_id' => $schedina->id,
                'codice' => $this->nextRichiestaCode(),
                'numero_prenotazione' => $data['numero_prenotazione'],
                'email' => $data['email'],
                'whatsapp' => $data['whatsapp'] ?? null,
                'nome_referente' => $data['nome_referente'],
                'arrivo' => $data['arrivo'],
                'partenza' => $data['partenza'],
                'quantita_persone' => $data['quantita_persone'],
                'numero_adulti' => $data['numero_adulti'] ?? null,
                'numero_bambini' => $data['numero_bambini'] ?? null,
                'messaggio_ospite' => $data['messaggio_ospite'] ?? null,
                'note' => $data['note'] ?? null,
                'token' => Str::random(64),
                'stato' => 'da_inviare',
            ]);

            app(\App\Services\WebCheckinLink::class)->issue($richiesta);

            return redirect()
                ->route('web_checkin.edit', ['id' => $richiesta->id])
                ->with('success', 'Richiesta creata. Prepara l’invito e verifica il canale disponibile.');
        });
    }

    public function edit(int $id)
    {
        $richiesta = $this->findOwnedRichiesta($id);

        return response()->view('web-checkin.form', [
            'richiesta' => $richiesta,
            'publicUrl' => $this->publicUrl($richiesta),
            'mailSubject' => $this->mailSubject($richiesta),
            'mailBody' => $this->mailBody($richiesta),
            'mailToUrl' => $this->mailToUrl($richiesta),
            'whatsappBody' => $this->whatsappBody($richiesta),
            'whatsAppUrl' => $this->whatsAppUrl($richiesta),
            'datiPendenti' => $this->pendingData($richiesta),
            'progresso' => $this->progresso($richiesta),
            'componentiRicevuti' => $richiesta->schedina ? $this->componentiForSchedina($richiesta->schedina)->get() : collect(),
            'comunicazioniRicevute' => $richiesta->comunicazioni()->whereNull('nascosta_at')->latest('id')->limit(20)->get(),
        ])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function update(Request $request, int $id)
    {
        $known = $this->findOwnedRichiesta($id);

        return DB::transaction(function () use ($request, $id, $known) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->whereKey($known->id)->lockForUpdate()->firstOrFail();
            $request->validate(['web_versione_richiesta' => ['required', 'string', 'size:64']], ['web_versione_richiesta.required' => 'Riapri la richiesta prima di aggiornarla.']);
            abort_unless(hash_equals(app(\App\Services\WebCheckinVersione::class)->of($richiesta), $request->input('web_versione_richiesta')), 409, 'I dati sono cambiati. Riapri la richiesta prima di aggiornarla.');
            if ($richiesta->processata()) {
                $data = $request->validate(['note' => $this->noteRules()]);
                $richiesta->update(['note' => array_key_exists('note', $data) ? $data['note'] : $richiesta->note]);

                return redirect()->route('web_checkin.edit', $id)->with('success', 'Note interne aggiornate. Per gli altri dati apri la scheda collegata.');
            }
            $links = app(\App\Services\WebCheckinLink::class);
            $active = $links->valid($richiesta);
            $recipient = [$richiesta->email, $richiesta->whatsapp, $richiesta->nome_referente];
            $previous = $richiesta->getAttributes();
            $data = $this->validateRichiesta($request);
            $linked = $richiesta->schedina;
            if ($linked && ((int) ($linked->cant_people ?? 0) !== (int) $data['quantita_persone'] || $richiesta->arrivo->toDateString() !== $data['arrivo'] || $richiesta->partenza->toDateString() !== $data['partenza']
                || (isset($data['numero_adulti']) ? (int) $data['numero_adulti'] : null) !== $richiesta->numero_adulti || (isset($data['numero_bambini']) ? (int) $data['numero_bambini'] : null) !== $richiesta->numero_bambini)) {
                $richiesta->compilato_at = null;
                $richiesta->stato = $richiesta->ultimo_accesso_at ? 'in_compilazione' : 'da_inviare';
            }
            $richiesta->fill($data);
            if (! $active || $recipient !== [$richiesta->email, $richiesta->whatsapp, $richiesta->nome_referente]) {
                $richiesta->link_revoked_at = $richiesta->link_revoked_at ?? now();
            } else {
                $richiesta->link_expires_at = $links->deadline($richiesta);
            }
            $richiesta->save();

            if ($richiesta->schedina) {
                $linked = $this->linkedSchedinaForRichiesta($richiesta);
                $sync = [];
                foreach (['arrivo' => 'arrive', 'partenza' => 'departure', 'quantita_persone' => 'cant_people', 'numero_prenotazione' => 'id_prenotazione_esterna'] as $source => $target) {
                    if ((string) $data[$source] !== (string) ($previous[$source] ?? '')) $sync[$target] = $data[$source];
                }
                // Referente dell’invito e titolare ricevuto sono informazioni distinte.
                foreach (['email' => 'customer_email', 'whatsapp' => 'customer_cellphone'] as $source => $target) {
                    if ((string) ($data[$source] ?? '') !== (string) ($previous[$source] ?? '')
                        && (blank($linked->{$target}) || (string) $linked->{$target} === (string) ($previous[$source] ?? ''))) $sync[$target] = $data[$source] ?? null;
                }
                if ($sync) $linked->fill($sync)->save();
            }

            return redirect()
                ->route('web_checkin.edit', ['id' => $richiesta->id])
                ->with('success', 'Richiesta Web Check-in aggiornata.');
        });
    }

    public function destroy(int $id)
    {
        $known = $this->findOwnedRichiesta($id);

        return DB::transaction(function () use ($known) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->whereKey($known->id)->lockForUpdate()->firstOrFail();
            $richiesta->setRelation('schedina', $this->linkedSchedinaForRichiesta($richiesta));
            $schedina = $richiesta->schedina;
            $schedinaCircuit = $this->normalizeSchedaCircuit($schedina);

            app(CestinoService::class)->archiveModel($richiesta, [
                'source' => 'Web Check-in',
                'circuito' => 'web',
            ]);

            // La richiesta nel Cestino conserva il riferimento; nessuna schedina o persona viene eliminata.

            $richiesta->delete();

            return redirect()->route('schedina.web')->with('success', 'Richiesta Web Check-in spostata nel cestino.');
        });
    }

    protected function componentiForSchedina(Schedina $schedina): \Illuminate\Database\Eloquent\Builder
    {
        abort_unless($schedina->exists && (int) $schedina->struttura_id > 0, 404);

        return parent::componentiForSchedina($schedina)
            ->withoutGlobalScope('struttura')
            ->where('struttura_id', $schedina->struttura_id);
    }

    protected function camereForSchedina(Schedina $schedina): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        abort_unless($schedina->exists && (int) $schedina->struttura_id > 0, 404);
        $camere = parent::camereForSchedina($schedina);
        $camere->getQuery()->withoutGlobalScope('struttura')
            ->where('schedina_camere.struttura_id', $schedina->struttura_id);

        return $camere;
    }

    public function publicShow(string $token, bool $preview = false)
    {
        $known = $preview ? WebCheckinRichiesta::where('token', $token)->first() : $this->findPublicRichiesta($token);
        abort_unless($known, 404);
        if ($preview) $known = $this->findOwnedRichiesta($known->id);

        return DB::transaction(function () use ($token, $known, $preview) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->whereKey($known->id)->lockForUpdate()->firstOrFail();
            abort_unless(($preview || app(\App\Services\WebCheckinLink::class)->valid($richiesta)) && hash_equals((string) $richiesta->token, $token), 404);
            $schedina = $this->linkedSchedinaForRichiesta($richiesta);
            abort_unless($schedina, 404);
            $richiesta->load('struttura');
            if ($richiesta->stato === 'convertito' || $schedina->circuito !== 'web') {
                return $this->received($richiesta);
            }

            $oldContext = session()->getOldInput('web_checkin_context');
            $ownInput = is_string($oldContext) && hash_equals(app(\App\Services\WebCheckinVersione::class)->contesto($richiesta), $oldContext);
            if (! $ownInput) session()->forget(['_old_input', 'errors', 'success', 'warning', 'error']);
            $publicErrors = $ownInput ? session('errors', new \Illuminate\Support\ViewErrorBag) : new \Illuminate\Support\ViewErrorBag;
            view()->share('errors', $publicErrors);

            if (! $preview) $richiesta->forceFill([
                'ultimo_accesso_at' => now(),
                'stato' => in_array($richiesta->stato, ['da_inviare', 'inviato'], true) ? 'in_compilazione' : $richiesta->stato,
            ])->save();

            $schedina->setRelation('camere', $this->camereForSchedina($schedina)->get());
            $componenti = $this->componentiForSchedina($schedina)->get();
            [$tassaConfig, $esenzioni] = $this->loadPublicTassaContext($richiesta);
            $tassaDettaglio = $this->tassaService->dettaglioSchedina($schedina, $componenti, $tassaConfig, $esenzioni, $richiesta->struttura);

            return view('web-checkin.public', array_merge(
                $this->commonFormData((int) $richiesta->struttura_id),
                [
                    'schedina' => $schedina,
                    'strutturaInfo' => $richiesta->struttura,
                    'tassaConfig' => $tassaConfig,
                    'esenzioni' => $esenzioni,
                    'geoEndpoints' => [],
                    'tassaDettaglio' => $tassaDettaglio,
                    'componenti' => $componenti,
                    'prefilledCustomer' => null,
                    'nextSchedaCode' => null,
                    'circuitoCorrente' => 'web',
                    'formAction' => route('web_checkin.public.store', ['token' => $richiesta->token]),
                    'formTitle' => 'Web Check-in',
                    'showCircuitSaveButtons' => false,
                    'primarySaveLabel' => 'Salva Web Check-in',
                    'showPrintTassa' => false,
                    'geoEndpointBase' => '/geo-public',
                    'webCheckinRichiesta' => $richiesta,
                    'webPreview' => $preview,
                    'identitaHotel' => app(\App\Services\WebCheckinIdentita::class)->of($richiesta->struttura),
                    'campiPendentiOspite' => $this->pendingData($richiesta, true, true),
                    'webVersione' => app(\App\Services\WebCheckinVersione::class)->of($richiesta),
                    'errors' => $publicErrors,
                ]
            ));
        });
    }

    public function publicShowShort(string $token)
    {
        if (WebCheckinRichiesta::query()->where('token', $token)->exists()) {
            return $this->publicShow($token);
        }

        return $this->publicInvite($token);
    }

    public function publicInvite(string $token)
    {
        return $this->publicRead($token, fn ($richiesta) => response()->view('web-checkin.invite', [
            'richiesta' => $richiesta, 'strutturaInfo' => $richiesta->struttura,
            'identitaHotel' => app(\App\Services\WebCheckinIdentita::class)->of($richiesta->struttura),
            'checkinUrl' => route('web_checkin.public.show', ['token' => $richiesta->token]),
        ]));
    }

    public function publicStoreShort(Request $request, string $access)
    {
        $richiesta = $this->findPublicRichiesta($access);
        abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');

        return $this->publicStore($request, (string) $richiesta->token);
    }

    public function publicCompletedShort(string $access)
    {
        $richiesta = $this->findPublicRichiesta($access);
        abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');

        return $this->publicCompleted((string) $richiesta->token);
    }

    public function publicStore(Request $request, string $token)
    {
        $known = $this->findPublicRichiesta($token);
        abort_unless($known, 404);

        return DB::transaction(function () use ($request, $token, $known) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->whereKey($known->id)->lockForUpdate()->firstOrFail();
            $schedina = $this->linkedSchedinaForRichiesta($richiesta);
            $richiesta->setRelation('schedina', $schedina);
            $richiesta->load('struttura');
            abort_unless(app(\App\Services\WebCheckinLink::class)->valid($richiesta) && hash_equals((string) $richiesta->token, $token), 404);
            if ($richiesta->stato === 'convertito') {
                return $this->received($richiesta);
            }

            abort_unless($schedina->circuito === 'web', 409, 'La reception ha già preso in carico i dati. Contatta la struttura per correggerli.');
            $request->merge(['web_checkin_context' => app(\App\Services\WebCheckinVersione::class)->contesto($richiesta)]);
            $request->validate(['web_versione' => ['required', 'string', 'size:64'], 'save_mode' => ['nullable', 'in:web,web_complete']], ['web_versione.required' => 'Riapri il link per aggiornare il modulo prima di salvare.', 'web_versione.size' => 'Versione del modulo non valida. Riapri il link.']);
            abort_unless(hash_equals(app(\App\Services\WebCheckinVersione::class)->of($richiesta), $request->input('web_versione')), 409, 'I dati sono cambiati. Riapri il link prima di salvare.');
            $complete = $request->input('save_mode') === 'web_complete';
            $request->validate([
                'componenti' => ['nullable', 'array', 'max:49'],
                'componenti.*.id' => ['nullable', 'integer', 'min:1'],
                'componenti.*.date_nac' => ['nullable', 'date', 'before_or_equal:today'],
                'camere' => ['nullable', 'array', 'max:50'],
                'oa_date_nac' => ['nullable', 'date', 'before_or_equal:today'],
            ]);
            // Il soggiorno previsto e i riferimenti interni sono gestiti dalla reception.
            $request->merge(['arrive' => $richiesta->arrivo->toDateString(), 'departure' => $richiesta->partenza->toDateString()]);
            $request->replace($this->soliDatiOspite($request->all()));
            $this->validateSchedinaRequest($request, $complete, (int) $richiesta->struttura_id, $schedina, true);
            if (! $complete) app(\App\Services\SchedinaAnagraficaValidation::class)->validate($request, (int) $richiesta->struttura_id);
            $this->validateProvidedComponenti($request, (int) $richiesta->struttura_id);
            if ($complete) {
                $request->validate(['cant_people' => ['required', 'integer', 'in:'.$richiesta->quantita_persone]], ['cant_people.in' => 'Il numero degli ospiti deve corrispondere alla richiesta. Contatta la reception per modificarlo.']);
                if (! $request->exists('componenti')) $request->merge(['componenti' => $this->componentiForSchedina($schedina)->get()->map->getAttributes()->all()]);
                $this->validateComponentiRows($request);
                $this->validatePeopleConsistency($request);
                app(\App\Services\WebCheckinCircuito::class)->verificaPersone($richiesta, $request);
            }
            $payload = $this->buildSchedinaPayload($request, $schedina->getAttributes());
            $internal = self::CAMPI_RISERVATI;
            foreach (array_keys($payload) as $field) {
                if (in_array($field, $internal, true) || (! $request->exists($field) && ! in_array($field, ['arrive', 'departure'], true))) unset($payload[$field]);
            }
            $payload['circuito'] = 'web';
            $payload['scheda'] = $schedina->scheda ?: $this->nextSchedaCode((int) $schedina->struttura_id, 'web');
            $payload['struttura_id'] = $schedina->struttura_id;
            $payload['is_arrive'] = 0;

            $schedina->fill($payload)->save();
            if ($request->exists('camere')) $this->syncCamere($schedina, $request);
            if ($request->exists('componenti')) $this->syncComponenti($schedina, $request);

            $richiesta->forceFill([
                'compilazione_snapshot' => app(\App\Services\WebCheckinCircuito::class)->snapshot($schedina),
                'stato' => $complete ? 'compilato' : 'in_compilazione',
                'compilato_at' => $complete ? now() : null,
                'ultimo_accesso_at' => now(),
            ])->save();

            return $this->renderCompletedView($richiesta);
        });
    }

    public function publicCompleted(string $token)
    {
        return $this->publicRead($token, fn ($richiesta) => $this->renderCompletedView($richiesta));
    }

    private function publicRead(string $access, \Closure $render)
    {
        $known = $this->findPublicRichiesta($access);
        abort_unless($known, 404);

        return DB::transaction(function () use ($access, $render, $known) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->whereKey($known->id)->lockForUpdate()->firstOrFail();
            $current = app(\App\Services\WebCheckinLink::class)->resolve($access);
            abort_unless($current && $current->id === $r->id, 404);
            $r->setRelation('schedina', $this->linkedSchedinaForRichiesta($r));
            $r->load('struttura');
            if ($r->stato === 'convertito' || $r->schedina?->circuito !== 'web') return $this->received($r);

            return $render($r);
        });
    }

    private function validateProvidedComponenti(Request $request, int $sid): void
    {
        $map = ['country_nac' => 'country_reg', 'regione_nac' => 'region_reg', 'province_nac' => 'prov_reg', 'comune_nac' => 'city_reg', 'cap_nac' => 'cap_reg', 'city_nac' => 'ciudadania_reg', 'country' => 'country', 'regione' => 'region', 'province' => 'province', 'city' => 'city', 'cap' => 'cap', 'typeaway' => 'typeaway'];
        foreach ((array) $request->input('componenti', []) as $index => $row) {
            $values = ['save_mode' => 'to_schedina'];
            foreach ($map as $source => $target) $values[$target] = $row[$source] ?? null;
            try {
                app(\App\Services\CustomerWizardValidation::class)->validate(new Request($values), null, $sid);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $reverse = array_flip($map); $errors = [];
                foreach ($e->errors() as $field => $messages) $errors['componenti.'.$index.'.'.($reverse[$field] ?? $field)] = $messages;
                throw \Illuminate\Validation\ValidationException::withMessages($errors);
            }
        }
    }

    private function soliDatiOspite(array $values): array
    {
        return array_filter($values, fn ($key) => !in_array($key, self::CAMPI_RISERVATI, true) && !str_starts_with($key, 'istat_'), ARRAY_FILTER_USE_KEY);
    }

    private function pendingData(WebCheckinRichiesta $r, bool $publicGuest = false, bool $fields = false): array
    {
        $sheet = $this->linkedSchedinaForRichiesta($r);
        if (! $sheet || ! in_array($sheet->circuito, ['web', 'bozza'], true)) return [];
        $values = $sheet->getAttributes();
        $values['componenti'] = $this->componentiForSchedina($sheet)->get()->map->getAttributes()->all();
        if ($publicGuest) $values = $this->soliDatiOspite($values);
        $form = Request::create('/checkin/SINTETICO', 'POST', $values);
        $route = new \Illuminate\Routing\Route(['POST'], '/checkin/{token}', []);
        $route->name('web_checkin.public.store')->bind($form);
        $form->setRouteResolver(fn () => $route);
        $errors = [];
        foreach ([fn () => $this->validateSchedinaRequest($form, true, (int) $r->struttura_id, $sheet, $publicGuest), fn () => $this->validateComponentiRows($form), fn () => $this->validatePeopleConsistency($form)] as $check) {
            try { $check(); }
            catch (\Illuminate\Validation\ValidationException $e) { $errors = array_merge($errors, $e->errors()); }
        }

        return $fields ? $errors : array_values(array_unique(collect($errors)->flatten()->all()));
    }

    public function toSchedina(Request $request, int $id)
    {
        $known = $this->findOwnedRichiesta($id);
        return DB::transaction(function () use ($request, $known) {
            Struttura::withoutGlobalScopes()->whereKey($known->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $known->struttura_id)->lockForUpdate()->findOrFail($known->id);
            $sheet = $this->linkedSchedinaForRichiesta($r);
            abort_unless($sheet, 409, 'Il collegamento ai dati ospite è da verificare. La richiesta originale resta consultabile.');
            $r->setRelation('schedina', $sheet);
            if ($r->processata()) return redirect()->route('schedina.edit', $sheet->id)->with('success', 'Check-in già effettuato: aperta la schedina esistente.');
            $request->validate(['web_versione_richiesta' => ['required', 'string', 'size:64']]);
            abort_unless(hash_equals(app(\App\Services\WebCheckinVersione::class)->of($r), $request->input('web_versione_richiesta')), 409, 'I dati sono cambiati. Riapri e verifica la richiesta.');
            $values = array_merge($sheet->getAttributes(), ['componenti' => $this->componentiForSchedina($sheet)->get()->map->getAttributes()->all(),
                'save_mode' => 'full', 'web_versione_gestione' => app(\App\Services\WebCheckinVersione::class)->of($r), 'schedina_struttura_id' => $r->struttura_id]);
            // Solo i dati operativi espliciti sono aggiungibili qui; nessun documento dall'URL.
            foreach (['room', 'beds'] as $field) if ($request->filled($field)) $values[$field] = $request->input($field);
            $form = Request::create('/schedine/'.$sheet->id, 'PUT', $values);
            $route = new \Illuminate\Routing\Route(['PUT'], '/schedine/{id}', []);
            $route->name('schedina.update')->bind($form);
            $form->setRouteResolver(fn () => $route);
            $form->setUserResolver(fn () => $request->user());
            $form->setLaravelSession($request->session());
            parent::update($form, $sheet->id);
            return redirect()->route('web_checkin.edit', $r->id)->with('success', 'Check-in effettuato. Schedina creata una sola volta e richiesta nello storico.');
        });
    }

    private function schedeWebPrecedenti(int $strutturaId)
    {
        $archiviate = \App\Models\CestinoItem::where('struttura_id', $strutturaId)->where('entity_class', WebCheckinRichiesta::class)->get()
            ->map(fn ($item) => (int) ($item->payload['schedina_id'] ?? 0))->filter()->all();
        return Schedina::withoutGlobalScopes()->where('struttura_id', $strutturaId)->where('circuito', 'web')
            ->whereNotIn('id', $archiviate)->whereNotExists(fn ($q) => $q->selectRaw('1')->from('web_checkin_richieste as wr')
                ->whereColumn('wr.schedina_id', 'schedina.id')->whereColumn('wr.struttura_id', 'schedina.struttura_id'));
    }

    private function versioneSchedaPrecedente(Schedina $sheet): string
    {
        return app(\App\Services\WebCheckinVersione::class)->of(new WebCheckinRichiesta(['struttura_id' => $sheet->struttura_id, 'schedina_id' => $sheet->id]));
    }

    private function progresso(WebCheckinRichiesta $r): array
    {
        $sheet = $r->schedina;
        if (! $sheet) return ['previste' => (int) $r->quantita_persone, 'caricate' => 0, 'complete' => 0, 'pendenti' => (int) $r->quantita_persone];
        $people = $this->componentiForSchedina($sheet)->get();
        $errors = $this->pendingData($r, true, true);
        $headComplete = ! collect(array_keys($errors))->contains(fn ($k) => ! str_starts_with($k, 'componenti') && ! in_array($k, ['cant_people', 'room', 'beds'], true));
        $complete = $headComplete ? 1 : 0;
        foreach ($people as $person) {
            $rows = \App\Support\Componenti\DatiComponenteNormalizzati::normalizzaRighe([$person->getAttributes()], true);
            if (! \App\Support\Componenti\DatiComponenteNormalizzati::validaRighe($rows)) $complete++;
        }
        $loaded = (filled($sheet->name) || filled($sheet->surname) ? 1 : 0) + $people->count();
        $complete = min($complete, (int) $r->quantita_persone);
        return ['previste' => (int) $r->quantita_persone, 'caricate' => $loaded, 'complete' => $complete, 'pendenti' => max(0, (int) $r->quantita_persone - $complete)];
    }

    private function received(WebCheckinRichiesta $r)
    {
        return response()->view('web-checkin.received', ['identitaHotel' => app(\App\Services\WebCheckinIdentita::class)->of($r->struttura)])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function anteprimaModulo(int $id)
    {
        $r = $this->findOwnedRichiesta($id);
        return $this->publicShow($r->token, true);
    }

    public function revoke(int $id)
    {
        return $this->changeLink($id, false);
    }

    public function regenerate(int $id)
    {
        return $this->changeLink($id, true);
    }

    private function changeLink(int $id, bool $issue)
    {
        $owned = $this->findOwnedRichiesta($id);

        return DB::transaction(function () use ($id, $issue, $owned) {
            Struttura::withoutGlobalScopes()->whereKey($owned->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $owned->struttura_id)->whereKey($owned->id)->lockForUpdate()->firstOrFail();
            $links = app(\App\Services\WebCheckinLink::class);
            if ($issue) {
                abort_if($r->stato === 'convertito' || $this->linkedSchedinaForRichiesta($r)?->circuito !== 'web', 409, 'La richiesta è già stata presa in carico: il link non può essere riaperto.');
                $links->issue($r);
            } else {
                $links->revoke($r);
            }

            return redirect()->route('web_checkin.edit', ['id' => $r->id])->with('success', $issue ? 'Nuovo link emesso.' : 'Link revocato.');
        });
    }

    private function validateTotalePersone(Request $request): void
    {
        $this->validatePeopleConsistency($request);
    }

    private function applyOperationalArriviDatesToSchedina(Schedina $schedina): void
    {
        $today = now()->startOfDay();
        $arrive = $schedina->arrive ? Carbon::parse($schedina->arrive)->startOfDay() : null;
        $departure = $schedina->departure ? Carbon::parse($schedina->departure)->startOfDay() : null;

        $nights = 1;
        if ($arrive && $departure && $departure->greaterThan($arrive)) {
            $nights = max(1, $arrive->diffInDays($departure));
        }

        $schedina->arrive = $today->toDateString();
        $schedina->departure = $today->copy()->addDays($nights)->toDateString();
    }

    private function validateRichiesta(Request $request): array
    {
        if ($request->filled('numero_adulti') && $request->filled('numero_bambini')) {
            $request->validate(['numero_adulti' => ['integer', 'min:0', 'max:50'], 'numero_bambini' => ['integer', 'min:0', 'max:50']]);
            $sum = (int) $request->input('numero_adulti') + (int) $request->input('numero_bambini');
            if ($request->filled('quantita_persone') && (int) $request->input('quantita_persone') !== $sum) throw \Illuminate\Validation\ValidationException::withMessages(['quantita_persone' => 'Il totale deve corrispondere alla somma di adulti e bambini.']);
            $request->merge(['quantita_persone' => $sum]);
        }
        return $request->validate([
            'numero_prenotazione' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'nome_referente' => ['required', 'string', 'max:150'],
            'arrivo' => ['required', 'date'],
            'partenza' => ['required', 'date', 'after:arrivo'],
            'numero_adulti' => ['nullable', 'required_with:numero_bambini', 'integer', 'min:0', 'max:50'],
            'numero_bambini' => ['nullable', 'required_with:numero_adulti', 'integer', 'min:0', 'max:50'],
            'messaggio_ospite' => ['nullable', 'string', 'max:4000'],
            'quantita_persone' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => $this->noteRules(),
        ], [
            'required' => 'Campo obbligatorio.',
            'email' => 'Inserisci un indirizzo email valido.',
            'date' => 'Inserisci una data valida.',
            'after_or_equal' => 'La partenza deve essere uguale o successiva all\'arrivo.',
            'integer' => 'Inserisci un numero valido.',
        ], [
            'numero_prenotazione' => 'Numero prenotazione',
            'nome_referente' => 'Referente',
            'quantita_persone' => 'Quantità persone',
        ]);
    }

    private function noteRules(): array
    {
        return ['nullable', 'string', static function ($attribute, $value, $fail) {
            if (is_string($value) && strlen($value) > 65535) $fail('Le note interne superano la capacità del campo. Riduci il testo prima di salvare.');
        }];
    }

    private function findOwnedRichiesta(int $id): WebCheckinRichiesta
    {
        $strutturaId = \App\Support\StrutturaAccess::resolve(request())->id;
        $richiesta = WebCheckinRichiesta::query()
            ->with(['schedina', 'struttura'])
            ->where('struttura_id', $strutturaId)
            ->findOrFail($id);
        $richiesta->setRelation('schedina', $this->linkedSchedinaForRichiesta($richiesta));

        return $richiesta;
    }

    private function ensureRichiestaToken(WebCheckinRichiesta $richiesta): WebCheckinRichiesta
    {
        return $richiesta;
    }

    private function ensureLinkedSchedina(WebCheckinRichiesta $richiesta): WebCheckinRichiesta
    {
        $linkedSchedina = $this->linkedSchedinaForRichiesta($richiesta);

        if ($linkedSchedina) {
            $richiesta->setRelation('schedina', $linkedSchedina);

            return $richiesta;
        }

        $schedina = Schedina::query()->create([
            'struttura_id' => $richiesta->struttura_id,
            'circuito' => 'web',
            'scheda' => $this->nextSchedaCode((int) $richiesta->struttura_id, 'web'),
            'arrive' => optional($richiesta->arrivo)->toDateString(),
            'departure' => optional($richiesta->partenza)->toDateString(),
            'cant_people' => (int) ($richiesta->quantita_persone ?: 1),
            'customer_email' => $richiesta->email,
            'customer_cellphone' => $richiesta->whatsapp,
            'fonte_prenotazione' => 'WEB CHECK-IN',
            'id_prenotazione_esterna' => $richiesta->numero_prenotazione,
            'is_arrive' => 0,
        ]);

        $richiesta->forceFill([
            'schedina_id' => $schedina->id,
        ])->save();

        $this->syncCircuitNumbering((int) $richiesta->struttura_id, null, 'web');

        $richiesta->schedina_id = $schedina->id;
        $richiesta->setRelation('schedina', $schedina);

        return $richiesta;
    }

    private function linkedSchedinaForRichiesta(WebCheckinRichiesta $richiesta): ?Schedina
    {
        if (! $richiesta->schedina_id) {
            return null;
        }

        $schedina = Schedina::withoutGlobalScopes()->where('struttura_id', $richiesta->struttura_id)->find($richiesta->schedina_id);

        return $schedina;
    }

    private function loadPublicTassaContext(WebCheckinRichiesta $richiesta): array
    {
        $struttura = $richiesta->struttura;
        $schedina = $this->linkedSchedinaForRichiesta($richiesta);
        abort_unless($struttura && $schedina
            && (int) $struttura->id === (int) $richiesta->struttura_id, 404);

        $tassaConfig = TassaDiSoggiorno::withoutGlobalScope('struttura')
            ->where('struttura_id', $struttura->id)->first();
        $esenzioni = collect();
        if (Schema::hasTable('tassa_esenzioni')) {
            $esenzioni = TassaEsenzione::withoutGlobalScope('struttura')
                ->where('struttura_id', $struttura->id)
                ->where('attivo', true)->where('codice', '<>', '777')
                ->orderBy('ordine')->orderBy('codice')->get();
        }

        return [$tassaConfig, $this->tassaService->catalogoBellaria($struttura, $esenzioni)];
    }

    private function nextRichiestaCode(): string
    {
        // Il vincolo SQL è globale: un identificativo alfanumerico di28caratteri
        // evita collisioni fra strutture e resta compatibile con gli alias legacy.
        // Nessun codice già assegnato viene rinumerato.
        return 'WC'.(string) Str::ulid();
    }

    private function mailSubject(WebCheckinRichiesta $richiesta): string
    {
        $struttura = $richiesta->struttura?->nome_struttura ?: 'la struttura';

        return "Benvenuto in {$struttura} | Completa il tuo Web Check-in";
    }

    private function mailBody(WebCheckinRichiesta $richiesta): string
    {
        $struttura = $richiesta->struttura?->nome_struttura ?: 'la struttura';
        $url = $this->publicUrl($richiesta);

        return "Gentile {$richiesta->nome_referente},\n\n"
            ."abbiamo preparato il tuo invito al Web Check-in per {$struttura}.\n"
            ."Apri questa pagina personale per iniziare in modo semplice e guidato:\n\n"
            ."{$url}\n\n"
            ."Troverai il logo della struttura, il riepilogo del soggiorno e il pulsante per aprire il tuo Web Check-in.\n\n"
            ."Grazie per la collaborazione.\n"
            ."A presto,\n"
            ."Reception {$struttura}";
    }

    private function publicUrl(WebCheckinRichiesta $richiesta): string
    {
        return route('web_checkin.public.short.show', ['token' => $this->publicAccessKey($richiesta)]);
    }

    private function mailToUrl(WebCheckinRichiesta $richiesta): string
    {
        return 'mailto:'.rawurlencode((string) $richiesta->email)
            .'?subject='.rawurlencode($this->mailSubject($richiesta))
            .'&body='.rawurlencode($this->mailBody($richiesta));
    }

    private function whatsappBody(WebCheckinRichiesta $richiesta): string
    {
        $struttura = $richiesta->struttura?->nome_struttura ?: 'la struttura';

        return "Gentile {$richiesta->nome_referente},\n\n"
            ."abbiamo preparato il tuo invito al Web Check-in per {$struttura}.\n"
            ."Apri questa pagina personale per iniziare:\n\n"
            .$this->publicUrl($richiesta)
            ."\n\nGrazie.\nReception {$struttura}";
    }

    private function publicAccessKey(WebCheckinRichiesta $richiesta): string
    {
        return $richiesta->short_token ?: $richiesta->codice.'-'.substr((string) $richiesta->token, 0, 8);
    }

    private function findPublicRichiesta(string $access): ?WebCheckinRichiesta
    {
        $r = app(\App\Services\WebCheckinLink::class)->resolve($access);
        if (! $r) {
            return null;
        }
        $r->load(['schedina', 'struttura']);
        $r->setRelation('schedina', $this->linkedSchedinaForRichiesta($r));
        abort_unless($r->schedina, 404);

        return $r;
    }

    private function whatsAppUrl(WebCheckinRichiesta $richiesta): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) ($richiesta->whatsapp ?? ''));
        if ($phone === '') {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->whatsappBody($richiesta));
    }

    private function renderCompletedView(WebCheckinRichiesta $richiesta)
    {
        if ($richiesta->stato === 'convertito') {
            return $this->received($richiesta);
        }
        $schedina = $richiesta->schedina;
        abort_if(! $schedina, 404);
        $schedina->setRelation('componenti', $this->componentiForSchedina($schedina)->get());

        return response()->view('web-checkin.completed', [
            'richiesta' => $richiesta,
            'schedina' => $schedina,
            'strutturaInfo' => $richiesta->struttura,
            'editUrl' => route('web_checkin.public.show', ['token' => $richiesta->token]),
            'componentiCount' => $schedina->componenti->count(),
            'identitaHotel' => app(\App\Services\WebCheckinIdentita::class)->of($richiesta->struttura),
            'campiPendentiOspite' => $richiesta->compilato_at ? [] : $this->pendingData($richiesta, true),
            'isLockedAfterConversion' => false,
        ]);
    }
}
