<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Models\WebCheckinComunicazione;
use App\Models\WebCheckinModello;
use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinLink;
use App\Services\WebCheckinMessaggio;
use App\Support\StrutturaAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebCheckinComunicazioniController extends Controller
{
    private const MESSAGES = [
        'required' => 'Compila il campo :attribute.', 'string' => 'Il campo :attribute deve contenere testo.',
        'max' => 'Il campo :attribute supera il limite di :max caratteri.', 'integer' => 'Inserisci un numero intero valido per :attribute.',
        'min' => 'Il campo :attribute non è valido.', 'in' => 'Seleziona un valore disponibile per :attribute.',
        'uuid' => 'Ricarica la pagina prima di preparare una nuova comunicazione.',
    ];
    public function index(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        DB::transaction(function () use ($r) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            foreach (WebCheckinMessaggio::iniziali() as $key => $values) {
                WebCheckinModello::firstOrCreate(['struttura_id' => $r->struttura_id, 'iniziale' => $key], $values);
            }
        });

        return response()->view('web-checkin.comunicazioni', [
            'richiesta' => $r,
            'modelli' => WebCheckinModello::where('struttura_id', $r->struttura_id)->orderBy('id')->get(),
            'storico' => WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->latest('id')->paginate(10),
            'variabili' => WebCheckinMessaggio::VARIABLES,
        ])->header('Cache-Control', 'no-store');
    }

    public function modello(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:191'],
            'oggetto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string', 'max:12000'],
            'modello_id' => ['nullable', 'integer', 'min:1'],
            'versione' => ['nullable', 'integer', 'min:1'],
            'azione' => ['required', 'in:nuovo,aggiorna,duplica'],
        ], self::MESSAGES);
        app(WebCheckinMessaggio::class)->render($data['oggetto'], $data['corpo'], $r);
        DB::transaction(function () use ($r, $data) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $source = ! empty($data['modello_id']) ? WebCheckinModello::where('struttura_id', $r->struttura_id)->lockForUpdate()->findOrFail($data['modello_id']) : null;
            if ($data['azione'] === 'aggiorna') {
                abort_unless($source && (int) $source->versione === (int) ($data['versione'] ?? 0), 409, 'Il modello è stato aggiornato. Ricarica prima di modificarlo.');
                $source->fill($data);
                $source->versione++;
                $source->save();
            } else {
                abort_if($data['azione'] === 'duplica' && ! $source, 422);
                $m = new WebCheckinModello($data);
                $m->struttura_id = $r->struttura_id;
                $m->save();
            }
        });

        return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Modello salvato nella sola struttura corrente.');
    }

    public function prepara(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate([
            'oggetto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string', 'max:12000'],
            'modello_id' => ['nullable', 'integer', 'min:1'],
            'idempotenza' => ['required', 'uuid'],
            'canale' => ['required', 'in:testo,email,whatsapp'],
        ], self::MESSAGES);
        $c = DB::transaction(function () use ($request, $r, $data) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $r->struttura_id)->lockForUpdate()->findOrFail($r->id);
            $previous = WebCheckinComunicazione::where('richiesta_id', $r->id)->where('struttura_id', $r->struttura_id)->where('idempotenza', $data['idempotenza'])->first();
            if ($previous) return $previous;
            abort_unless(app(WebCheckinLink::class)->valid($r) && $r->stato !== 'convertito', 422, 'Il link non è disponibile per un nuovo invito.');
            if (! empty($data['modello_id'])) WebCheckinModello::where('struttura_id', $r->struttura_id)->findOrFail($data['modello_id']);
            $rendered = app(WebCheckinMessaggio::class)->render($data['oggetto'], $data['corpo'], $r);
            abort_if($data['canale'] === 'email' && ! filter_var($r->email, FILTER_VALIDATE_EMAIL), 422, 'Indirizzo email mancante o non valido.');
            abort_if($data['canale'] === 'whatsapp' && ! preg_match('/\A\+?[0-9 ()-]{6,50}\z/', (string) $r->whatsapp), 422, 'Numero WhatsApp mancante o non valido.');

            return WebCheckinComunicazione::create([
                'struttura_id' => $r->struttura_id, 'richiesta_id' => $r->id, 'utente_id' => $request->user()->id,
                'modello_id' => $data['modello_id'] ?? null, 'idempotenza' => $data['idempotenza'],
                'canale' => $data['canale'], 'stato' => 'preparato',
                'contenuto' => $rendered + ['impronta_invito' => app(WebCheckinMessaggio::class)->improntaInvito($r), 'destinatario' => $data['canale'] === 'whatsapp' ? $r->whatsapp : ($data['canale'] === 'email' ? $r->email : null)],
            ]);
        });

        return redirect()->route('web_checkin.comunicazione', [$id, $c->id]);
    }

    public function show(Request $request, int $id, int $communication)
    {
        $r = $this->owned($request, $id);
        $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->findOrFail($communication);

        return response()->view('web-checkin.messaggio', ['richiesta' => $r, 'comunicazione' => $c])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function apri(Request $request, int $id, int $communication)
    {
        $r = $this->owned($request, $id);
        $c = DB::transaction(function () use ($r, $communication) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::whereKey($r->id)->lockForUpdate()->firstOrFail();
            abort_unless(app(WebCheckinLink::class)->valid($r) && $r->stato !== 'convertito', 422, 'Il link non è più utilizzabile.');
            $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->lockForUpdate()->findOrFail($communication);
            $content = $c->contenuto;
            abort_unless(hash_equals(app(WebCheckinMessaggio::class)->improntaInvito($r), (string) ($content['impronta_invito'] ?? '')), 422, 'I dati dell’invito sono cambiati. Prepara una nuova comunicazione.');
            $current = filled($r->short_token) ? route('web_checkin.public.short.show', ['token' => $r->short_token]) : route('web_checkin.public.show', ['token' => $r->token]);
            abort_unless(str_contains($content['corpo'], $current), 422, 'Il link è stato rigenerato. Prepara un nuovo messaggio.');
            abort_unless(in_array($c->canale, ['email', 'whatsapp'], true), 422);
            if ($c->stato === 'preparato') $c->update(['stato' => 'apertura_richiesta']);

            return $c;
        });
        $content = $c->contenuto;
        $url = $c->canale === 'email'
            ? 'mailto:'.rawurlencode($content['destinatario']).'?subject='.rawurlencode($content['oggetto']).'&body='.rawurlencode($content['corpo'])
            : 'https://wa.me/'.preg_replace('/\D/', '', $content['destinatario']).'?text='.rawurlencode($content['corpo']);

        return redirect()->away($url)->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    private function owned(Request $request, int $id): WebCheckinRichiesta
    {
        $sid = StrutturaAccess::resolve($request)->id;

        return WebCheckinRichiesta::with('struttura')->where('struttura_id', $sid)->findOrFail($id);
    }
}
