<?php

namespace App\Http\Controllers;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebCheckinController extends SchedinaController
{
    public function index()
    {
        session()->forget(['success', 'warning', 'error']);
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (!$strutturaId) {
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
                $like = '%' . $q . '%';
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
            $richiesta = $this->ensureRichiestaToken($richiesta);
            return $this->ensureLinkedSchedina($richiesta);
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
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        return view('web-checkin.form', [
            'richiesta' => new WebCheckinRichiesta(),
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
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        if (!$strutturaId) {
            return back()->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.'])->withInput();
        }

        $data = $this->validateRichiesta($request);
        $struttura = Struttura::findOrFail($strutturaId);

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

        $schedina->update([
            'name' => $richiesta->nome_referente,
        ]);

        return redirect()
            ->route('web_checkin.edit', ['id' => $richiesta->id])
            ->with('success', 'Web Check-in creato. Link e testo email pronti per l\'invio.');
    }

    public function edit(int $id)
    {
        $richiesta = $this->ensureLinkedSchedina(
            $this->ensureRichiestaToken($this->findOwnedRichiesta($id))
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
        $richiesta = $this->findOwnedRichiesta($id);
        $data = $this->validateRichiesta($request);

        $richiesta->fill($data)->save();

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
    }

    public function destroy(int $id)
    {
        $richiesta = $this->findOwnedRichiesta($id);
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

        return redirect()->route('schedina.web')->with('success', 'Richiesta Web Check-in eliminata.');
    }

    public function publicShow(string $token)
    {
        $richiesta = WebCheckinRichiesta::query()->with(['schedina', 'struttura'])->where('token', $token)->first();
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        abort_if(!$schedina, 404);

        if (($richiesta->stato ?? '') === 'convertito') {
            return response()->view('web-checkin.completed', [
                'richiesta' => $richiesta,
                'schedina' => $schedina,
                'strutturaInfo' => $richiesta->struttura,
                'editUrl' => null,
                'componentiCount' => $schedina->componenti()->count(),
                'isLockedAfterConversion' => true,
            ]);
        }

        $richiesta->forceFill([
            'ultimo_accesso_at' => now(),
            'stato' => in_array($richiesta->stato, ['da_inviare', 'inviato'], true) ? 'in_compilazione' : $richiesta->stato,
        ])->save();

        $componenti = $schedina->componenti()->get();
        [$tassaConfig, $esenzioni] = $this->loadTassaContext($richiesta->struttura);
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
    }

    public function publicInvite(string $token)
    {
        $richiesta = $this->findPublicRichiesta($token);
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);

        return response()->view('web-checkin.invite', [
            'richiesta' => $richiesta,
            'strutturaInfo' => $richiesta->struttura,
            'checkinUrl' => route('web_checkin.public.show', ['token' => $richiesta->token]),
        ]);
    }

    public function publicStoreShort(Request $request, string $access)
    {
        $richiesta = $this->findPublicRichiesta($access);
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');

        return $this->publicStore($request, (string) $richiesta->token);
    }

    public function publicCompletedShort(string $access)
    {
        $richiesta = $this->findPublicRichiesta($access);
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');

        return $this->publicCompleted((string) $richiesta->token);
    }

    public function publicStore(Request $request, string $token)
    {
        $richiesta = WebCheckinRichiesta::query()->with(['schedina', 'struttura'])->where('token', $token)->first();
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        abort_if(!$schedina, 404);

        if (($richiesta->stato ?? '') === 'convertito') {
            return response()->view('web-checkin.completed', [
                'richiesta' => $richiesta,
                'schedina' => $schedina,
                'strutturaInfo' => $richiesta->struttura,
                'editUrl' => null,
                'componentiCount' => $schedina->componenti()->count(),
                'isLockedAfterConversion' => true,
            ]);
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
            'arrivo' => $schedina->arrive,
            'partenza' => $schedina->departure,
            'quantita_persone' => $schedina->cant_people,
            'email' => $schedina->customer_email ?: $richiesta->email,
            'whatsapp' => $schedina->customer_cellphone ?: $richiesta->whatsapp,
            'nome_referente' => trim(($schedina->name ?? '') . ' ' . ($schedina->surname ?? '')) ?: ($schedina->name ?: $richiesta->nome_referente),
        ])->save();

        return $this->renderCompletedView($richiesta);
    }

    public function publicCompleted(string $token)
    {
        $richiesta = WebCheckinRichiesta::query()->with(['schedina', 'struttura'])->where('token', $token)->first();
        abort_if(!$richiesta, 404, 'Link Web Check-in non valido o non più disponibile.');
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        abort_if(!$schedina, 404);

        return $this->renderCompletedView($richiesta);
    }

    public function toSchedina(Request $request, int $id)
    {
        $richiesta = $this->findOwnedRichiesta($id);
        $richiesta = $this->ensureLinkedSchedina($richiesta);
        $schedina = $richiesta->schedina;
        if (!$schedina) {
            return redirect()->route('schedina.web')->with('error', 'Schedina Web non trovata.');
        }
        return redirect()
            ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-base'])
            ->with('warning', 'Completa e salva la schedina web dal form prima di inviarla nel circuito operativo.');
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
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        return WebCheckinRichiesta::query()
            ->with(['schedina', 'struttura'])
            ->where('struttura_id', $strutturaId)
            ->findOrFail($id);
    }

    private function ensureRichiestaToken(WebCheckinRichiesta $richiesta): WebCheckinRichiesta
    {
        if (!filled($richiesta->token)) {
            $richiesta->forceFill([
                'token' => Str::random(64),
            ])->save();
        }

        return $richiesta;
    }

    private function ensureLinkedSchedina(WebCheckinRichiesta $richiesta): WebCheckinRichiesta
    {
        $linkedSchedina = null;
        if ($richiesta->schedina_id) {
            $linkedSchedina = Schedina::withoutGlobalScopes()->find($richiesta->schedina_id);
        }

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

        return 'WC' . ($max + 1);
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
            . "abbiamo preparato il tuo invito al Web Check-in per {$struttura}.\n"
            . "Apri questa pagina personale per iniziare in modo semplice e guidato:\n\n"
            . "{$url}\n\n"
            . "Troverai il logo della struttura, il riepilogo del soggiorno e il pulsante per aprire il tuo Web Check-in.\n\n"
            . "Grazie per la collaborazione.\n"
            . "A presto,\n"
            . "Reception {$struttura}";
    }

    private function publicUrl(WebCheckinRichiesta $richiesta): string
    {
        return route('web_checkin.public.short.show', ['token' => $this->publicAccessKey($richiesta)]);
    }

    private function mailToUrl(WebCheckinRichiesta $richiesta): string
    {
        return 'mailto:' . rawurlencode((string) $richiesta->email)
            . '?subject=' . rawurlencode($this->mailSubject($richiesta))
            . '&body=' . rawurlencode($this->mailBody($richiesta));
    }

    private function whatsappBody(WebCheckinRichiesta $richiesta): string
    {
        $struttura = $richiesta->struttura?->nome_struttura ?: 'la struttura';

        return "Gentile {$richiesta->nome_referente},\n\n"
            . "abbiamo preparato il tuo invito al Web Check-in per {$struttura}.\n"
            . "Apri questa pagina personale per iniziare:\n\n"
            . $this->publicUrl($richiesta)
            . "\n\nGrazie.\nReception {$struttura}";
    }

    private function publicAccessKey(WebCheckinRichiesta $richiesta): string
    {
        return $richiesta->codice . '-' . substr((string) $richiesta->token, 0, 8);
    }

    private function findPublicRichiesta(string $access): ?WebCheckinRichiesta
    {
        $query = WebCheckinRichiesta::query()->with(['schedina', 'struttura']);

        if (Str::contains($access, '-')) {
            [$codice, $prefix] = array_pad(explode('-', $access, 2), 2, null);

            if (filled($codice) && filled($prefix)) {
                return (clone $query)
                    ->where('codice', $codice)
                    ->where('token', 'like', $prefix . '%')
                    ->first();
            }
        }

        return $query->where('token', $access)->first();
    }

    private function whatsAppUrl(WebCheckinRichiesta $richiesta): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) ($richiesta->whatsapp ?? ''));
        if ($phone === '') {
            return null;
        }

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->whatsappBody($richiesta));
    }

    private function renderCompletedView(WebCheckinRichiesta $richiesta)
    {
        $schedina = $richiesta->schedina;
        abort_if(!$schedina, 404);
        $schedina->loadMissing('componenti');

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
