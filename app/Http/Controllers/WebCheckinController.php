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
    public function index()
    {
        session()->forget(['success', 'warning', 'error']);
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $q = trim((string) request()->query('q', ''));
        $stato = trim((string) request()->query('stato', ''));
        $this->syncCircuitNumbering($strutturaId, null, 'web');

        $baseQuery = WebCheckinRichiesta::query()
            ->with('schedina')
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
            return $this->ensureRichiestaToken($this->ensureLinkedSchedina($richiesta));
        });

        return view('web-checkin.index', [
            'richieste' => $richieste,
            'statoFiltro' => $stato,
            'totaliWebCheckin' => [
                'da_inviare' => $totali->where('stato', 'da_inviare')->count(),
                'in_compilazione' => $totali->where('stato', 'in_compilazione')->count(),
                'compilato' => $totali->where('stato', 'compilato')->count(),
                'convertito' => $totali->where('stato', 'convertito')->count(),
            ],
        ]);
    }

    public function create()
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        return view('web-checkin.form', [
            'richiesta' => new WebCheckinRichiesta,
            'publicUrl' => null,
            'mailSubject' => null,
            'mailBody' => null,
            'mailToUrl' => null,
            'whatsappBody' => null,
            'whatsAppUrl' => null,
        ]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $strutturaId = \App\Support\StrutturaAccess::resolve($request)->id;
            if (! $strutturaId) {
                return back()->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.'])->withInput();
            }

            $data = $this->validateRichiesta($request);
            $struttura = Struttura::whereKey($strutturaId)->lockForUpdate()->firstOrFail();

            $schedina = Schedina::query()->create([
                'struttura_id' => $strutturaId,
                'circuito' => 'web',
                'scheda' => $this->nextSchedaCode($strutturaId, 'web'),
                'arrive' => $data['arrivo'],
                'departure' => $data['partenza'],
                'cant_people' => $data['quantita_persone'],
                'room' => 1,
                'beds' => $data['quantita_persone'],
                'customer_email' => $data['email'],
                'customer_cellphone' => $data['whatsapp'] ?? null,
                'fonte_prenotazione' => 'WEB CHECK-IN',
                'id_prenotazione_esterna' => $data['numero_prenotazione'],
                'is_arrive' => 0,
            ]);
            $this->syncCircuitNumbering($strutturaId, null, 'web');

            $richiesta = WebCheckinRichiesta::query()->create([
                'struttura_id' => $strutturaId,
                'schedina_id' => $schedina->id,
                'codice' => $this->nextRichiestaCode($strutturaId),
                'numero_prenotazione' => $data['numero_prenotazione'],
                'email' => $data['email'],
                'whatsapp' => $data['whatsapp'] ?? null,
                'nome_referente' => $data['nome_referente'],
                'arrivo' => $data['arrivo'],
                'partenza' => $data['partenza'],
                'quantita_persone' => $data['quantita_persone'],
                'note' => $data['note'] ?? null,
                'token' => Str::random(64),
                'stato' => 'da_inviare',
            ]);

            app(\App\Services\WebCheckinLink::class)->issue($richiesta);

            $schedina->update([
                'name' => $richiesta->nome_referente,
            ]);

            return redirect()
                ->route('web_checkin.edit', ['id' => $richiesta->id])
                ->with('success', 'Web Check-in creato. Link e testo email pronti per l\'invio.');
        });
    }

    public function edit(int $id)
    {
        $richiesta = $this->ensureRichiestaToken(
            $this->ensureLinkedSchedina($this->findOwnedRichiesta($id))
        );

        return view('web-checkin.form', [
            'richiesta' => $richiesta,
            'publicUrl' => $this->publicUrl($richiesta),
            'mailSubject' => $this->mailSubject($richiesta),
            'mailBody' => $this->mailBody($richiesta),
            'mailToUrl' => $this->mailToUrl($richiesta),
            'whatsappBody' => $this->whatsappBody($richiesta),
            'whatsAppUrl' => $this->whatsAppUrl($richiesta),
        ]);
    }

    public function update(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $richiesta = $this->findOwnedRichiesta($id);
            Struttura::withoutGlobalScopes()->whereKey($richiesta->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::whereKey($richiesta->id)->lockForUpdate()->firstOrFail();
            $links = app(\App\Services\WebCheckinLink::class);
            $active = $links->valid($richiesta);
            $recipient = [$richiesta->email, $richiesta->whatsapp, $richiesta->nome_referente];
            $data = $this->validateRichiesta($request);
            $richiesta->fill($data);
            if (! $active || $recipient !== [$richiesta->email, $richiesta->whatsapp, $richiesta->nome_referente]) {
                $richiesta->link_revoked_at = $richiesta->link_revoked_at ?? now();
            } else {
                $richiesta->link_expires_at = $links->deadline($richiesta);
            }
            $richiesta->save();

            if ($richiesta->schedina) {
                $richiesta->schedina->fill([
                    'arrive' => $data['arrivo'],
                    'departure' => $data['partenza'],
                    'cant_people' => $data['quantita_persone'],
                    'room' => $richiesta->schedina->room ?: 1,
                    'beds' => $richiesta->schedina->beds ?: $data['quantita_persone'],
                    'fonte_prenotazione' => 'WEB CHECK-IN',
                    'id_prenotazione_esterna' => $data['numero_prenotazione'],
                    'name' => $data['nome_referente'],
                    'customer_email' => $data['email'],
                    'customer_cellphone' => $data['whatsapp'] ?? null,
                ])->save();
            }

            return redirect()
                ->route('web_checkin.edit', ['id' => $richiesta->id])
                ->with('success', 'Richiesta Web Check-in aggiornata.');
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {
            $richiesta = $this->findOwnedRichiesta($id);
            Struttura::withoutGlobalScopes()->whereKey($richiesta->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::whereKey($richiesta->id)->lockForUpdate()->firstOrFail();
            $richiesta->setRelation('schedina', $this->linkedSchedinaForRichiesta($richiesta));
            $schedina = $richiesta->schedina;
            $schedinaCircuit = $this->normalizeSchedaCircuit($schedina);

            app(CestinoService::class)->archiveModel($richiesta, [
                'source' => 'Web Check-in',
                'circuito' => 'web',
            ]);

            if ($schedina && $schedinaCircuit === 'web') {
                $schedina->delete();
            }

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

    public function publicShow(string $token)
    {
        return DB::transaction(function () use ($token) {
            $richiesta = $this->findPublicRichiesta($token);
            abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
            $richiesta = $this->ensureLinkedSchedina($richiesta);
            $schedina = $richiesta->schedina;
            abort_if(! $schedina, 404);

            Struttura::withoutGlobalScopes()->whereKey($richiesta->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::whereKey($richiesta->id)->lockForUpdate()->firstOrFail();
            abort_unless(app(\App\Services\WebCheckinLink::class)->valid($richiesta) && hash_equals((string) $richiesta->token, $token), 404);
            $schedina = $this->linkedSchedinaForRichiesta($richiesta);
            $richiesta->load('struttura');
            if ($richiesta->stato === 'convertito') {
                return $this->received();
            }

            $richiesta->forceFill([
                'ultimo_accesso_at' => now(),
                'stato' => in_array($richiesta->stato, ['da_inviare', 'inviato'], true) ? 'in_compilazione' : $richiesta->stato,
            ])->save();

            $schedina->setRelation('camere', $this->camereForSchedina($schedina)->get());
            $componenti = $this->componentiForSchedina($schedina)->get();
            [$tassaConfig, $esenzioni] = $this->loadPublicTassaContext($richiesta);
            $tassaDettaglio = $this->tassaService->dettaglioSchedina($schedina, $componenti, $tassaConfig, $esenzioni, $richiesta->struttura);

            return view('web-checkin.public', array_merge(
                $this->commonFormData(),
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
        $richiesta = $this->findPublicRichiesta($token);
        abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        if ($richiesta->stato === 'convertito') {
            return $this->received();
        }

        return response()->view('web-checkin.invite', [
            'richiesta' => $richiesta,
            'strutturaInfo' => $richiesta->struttura,
            'checkinUrl' => route('web_checkin.public.show', ['token' => $richiesta->token]),
        ]);
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
        return DB::transaction(function () use ($request, $token) {
            $richiesta = $this->findPublicRichiesta($token);
            abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
            $richiesta = $this->ensureLinkedSchedina($richiesta);
            $schedina = $richiesta->schedina;
            abort_if(! $schedina, 404);

            Struttura::withoutGlobalScopes()->whereKey($richiesta->struttura_id)->lockForUpdate()->firstOrFail();
            $richiesta = WebCheckinRichiesta::whereKey($richiesta->id)->lockForUpdate()->firstOrFail();
            $schedina = $this->linkedSchedinaForRichiesta($richiesta);
            $richiesta->setRelation('schedina', $schedina);
            $richiesta->load('struttura');
            abort_unless(app(\App\Services\WebCheckinLink::class)->valid($richiesta) && hash_equals((string) $richiesta->token, $token), 404);
            if ($richiesta->stato === 'convertito') {
                return $this->received();
            }

            $payload = $this->buildSchedinaPayload($request, []);
            $payload['circuito'] = 'web';
            $payload['scheda'] = $schedina->scheda ?: $this->nextSchedaCode((int) $schedina->struttura_id, 'web');
            $payload['struttura_id'] = $schedina->struttura_id;
            $payload['is_arrive'] = 0;

            $schedina->fill($payload)->save();
            $this->syncCamere($schedina, $request);
            $this->syncComponenti($schedina, $request);

            $richiesta->forceFill([
                'stato' => 'in_compilazione',
                'compilato_at' => null,
                'ultimo_accesso_at' => now(),
                'quantita_persone' => $schedina->cant_people,
                'email' => $schedina->customer_email ?: $richiesta->email,
                'whatsapp' => $schedina->customer_cellphone ?: $richiesta->whatsapp,
                'nome_referente' => trim(($schedina->name ?? '').' '.($schedina->surname ?? '')) ?: ($schedina->name ?: $richiesta->nome_referente),
            ])->save();

            return $this->renderCompletedView($richiesta);
        });
    }

    public function publicCompleted(string $token)
    {
        $richiesta = $this->findPublicRichiesta($token);
        abort_if(! $richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        abort_if(! $schedina, 404);

        return $this->renderCompletedView($richiesta);
    }

    public function toSchedina(Request $request, int $id)
    {
        $richiesta = $this->findOwnedRichiesta($id);
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        if (! $schedina) {
            return redirect()->route('schedina.web')->with('error', 'Schedina Web non trovata.');
        }

        return redirect()
            ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-base'])
            ->with('warning', 'Completa e salva la schedina web dal form prima di inviarla nel circuito operativo.');
    }

    private function received()
    {
        return response('Check-in recibido', 200)->header('Content-Type', 'text/plain; charset=UTF-8')->header('Cache-Control', 'no-store');
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
        return DB::transaction(function () use ($id, $issue) {
            $owned = $this->findOwnedRichiesta($id);
            Struttura::withoutGlobalScopes()->whereKey($owned->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::whereKey($owned->id)->lockForUpdate()->firstOrFail();
            $links = app(\App\Services\WebCheckinLink::class);
            if ($issue) {
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
        return $request->validate([
            'numero_prenotazione' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'nome_referente' => ['required', 'string', 'max:150'],
            'arrivo' => ['required', 'date'],
            'partenza' => ['required', 'date', 'after_or_equal:arrivo'],
            'quantita_persone' => ['required', 'integer', 'min:1', 'max:50'],
            'note' => ['nullable', 'string'],
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
            'room' => 1,
            'beds' => (int) ($richiesta->quantita_persone ?: 1),
            'customer_email' => $richiesta->email,
            'customer_cellphone' => $richiesta->whatsapp,
            'fonte_prenotazione' => 'WEB CHECK-IN',
            'id_prenotazione_esterna' => $richiesta->numero_prenotazione,
            'name' => $richiesta->nome_referente,
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

        $schedina = Schedina::withoutGlobalScopes()->find($richiesta->schedina_id);
        abort_unless($schedina
            && (int) $schedina->struttura_id === (int) $richiesta->struttura_id, 404);

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

    private function nextRichiestaCode(int $strutturaId): string
    {
        $codes = WebCheckinRichiesta::query()
            ->where('struttura_id', $strutturaId)
            ->pluck('codice');

        $max = 0;
        foreach ($codes as $code) {
            if (preg_match('/^WC(\d+)$/i', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'WC'.($max + 1);
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
            return $this->received();
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
            'isLockedAfterConversion' => false,
        ]);
    }
}
