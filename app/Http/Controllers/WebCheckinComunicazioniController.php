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
                WebCheckinModello::withTrashed()->firstOrCreate(['struttura_id' => $r->struttura_id, 'iniziale' => $key], $values);
            }
        });

        return response()->view('web-checkin.comunicazioni', [
            'ripresa' => $this->ripresa($r),
            'comunicazioneUI' => app(\App\Services\WebCheckinComunicazioneStato::class)->of($r),
            'richiesta' => $r,
            'modelli' => WebCheckinModello::where('struttura_id', $r->struttura_id)->orderBy('id')->get(),
            'storico' => WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->whereNull('nascosta_at')->latest('id')->paginate(10),
            'variabili' => WebCheckinMessaggio::VARIABLES,
            'testoBreve' => "{{struttura}} · Check-in online\nGentile {{referente}}, arrivo {{arrivo}}, partenza {{partenza}}.\n{{link}}\nPuoi salvare e continuare più tardi. Accesso fino al {{scadenza}}.",
            'oggettoDefault' => '{{struttura}} · Il tuo check-in online',
            'identita' => app(\App\Services\WebCheckinIdentita::class)->of($r->struttura),
        ])->header('Cache-Control', 'no-store');
    }

    public function modello(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:191'],
            'oggetto' => ['required', 'string', 'max:255'],
            'corpo' => ['required', 'string', 'max:12000'],
            'impostazioni' => ['nullable', 'array:saluto,testo,pulsante,firma'],
            'impostazioni.saluto' => ['required_with:impostazioni', 'string', 'max:500'],
            'impostazioni.testo' => ['required_with:impostazioni', 'string', 'max:4000'],
            'impostazioni.pulsante' => ['required_with:impostazioni', 'string', 'max:80'],
            'impostazioni.firma' => ['required_with:impostazioni', 'string', 'max:1000'],
            'modello_id' => ['nullable', 'integer', 'min:1'],
            'versione' => ['nullable', 'integer', 'min:1'],
            'azione' => ['required', 'in:nuovo,aggiorna,duplica'],
        ], self::MESSAGES);
        app(WebCheckinMessaggio::class)->render($data['oggetto'], $data['corpo'], $r, $data['impostazioni'] ?? []);
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
            'impostazioni' => ['nullable', 'array:saluto,testo,pulsante,firma'],
            'impostazioni.saluto' => ['required_with:impostazioni', 'string', 'max:500'],
            'impostazioni.testo' => ['required_with:impostazioni', 'string', 'max:4000'],
            'impostazioni.pulsante' => ['required_with:impostazioni', 'string', 'max:80'],
            'impostazioni.firma' => ['required_with:impostazioni', 'string', 'max:1000'],
            'modello_id' => ['nullable', 'integer', 'min:1'],
            'idempotenza' => ['required', 'uuid'],
            'canale' => ['required', 'in:testo,email,whatsapp'],
        ], self::MESSAGES);
        $c = DB::transaction(function () use ($request, $r, $data) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $r->struttura_id)->lockForUpdate()->findOrFail($r->id);
            $previous = WebCheckinComunicazione::where('richiesta_id', $r->id)->where('struttura_id', $r->struttura_id)->where('idempotenza', $data['idempotenza'])->first();
            if ($previous) return $previous;
            $uncertain = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->where('canale', 'email')->latest('id')->first();
            if ($data['canale'] === 'email' && $uncertain && in_array($uncertain->stato, ['invio_in_corso', 'esito_incerto'], true)) {
                abort_if($uncertain->stato === 'invio_in_corso', 422, 'Invio già in corso. Attendi e ricarica la pagina.');
                $request->validate(['conferma_nuovo_tentativo' => ['accepted']], ['accepted' => 'Verifica il tentativo incerto e conferma il nuovo invio volontario.']);
            }
            abort_unless(app(WebCheckinLink::class)->valid($r) && $r->stato !== 'convertito', 422, 'Il link non è disponibile per un nuovo invito.');
            if (! empty($data['modello_id'])) WebCheckinModello::where('struttura_id', $r->struttura_id)->findOrFail($data['modello_id']);
            $rendered = app(WebCheckinMessaggio::class)->render($data['oggetto'], $data['corpo'], $r, $data['impostazioni'] ?? []);
            abort_if($data['canale'] === 'email' && ! filter_var($r->email, FILTER_VALIDATE_EMAIL), 422, 'Indirizzo email mancante o non valido.');
            abort_if($data['canale'] === 'whatsapp' && ! preg_match('/\A\+?[0-9 ()-]{6,50}\z/', (string) $r->whatsapp), 422, 'Numero WhatsApp mancante o non valido.');

            return WebCheckinComunicazione::create([
                'struttura_id' => $r->struttura_id, 'richiesta_id' => $r->id, 'utente_id' => $request->user()->id,
                'modello_id' => $data['modello_id'] ?? null, 'idempotenza' => $data['idempotenza'],
                'canale' => $data['canale'], 'stato' => 'preparato',
                'contenuto' => $rendered + ['sorgente' => \Illuminate\Support\Arr::only($data, ['oggetto', 'corpo', 'impostazioni']), 'impronta_invito' => app(WebCheckinMessaggio::class)->improntaInvito($r), 'destinatario' => $data['canale'] === 'whatsapp' ? $r->whatsapp : ($data['canale'] === 'email' ? $r->email : null)],
            ]);
        });

        if ($c->nascosta_at) return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Preparazione già registrata e rimossa dalla cronologia visibile. Nessun duplicato creato.');

        if ($request->boolean('vista_modale') && $request->expectsJson() && $c->canale === 'email') {
            return response()->json(['dialogo' => view('web-checkin.partials.email-dialog', [
                'richiesta' => $r, 'comunicazione' => $c,
                'impedimentoEmail' => app(\App\Services\WebCheckinComunicazioneStato::class)->impedimento($r, $c),
            ])->render()])->header('Cache-Control', 'no-store');
        }
        return redirect()->route('web_checkin.comunicazione', [$id, $c->id]);
    }

    public function show(Request $request, int $id, int $communication)
    {
        $r = $this->owned($request, $id);
        $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->whereNull('nascosta_at')->findOrFail($communication);

        return response()->view('web-checkin.messaggio', ['richiesta' => $r, 'comunicazione' => $c, 'impedimentoEmail' => app(\App\Services\WebCheckinComunicazioneStato::class)->impedimento($r, $c), 'comunicazioneUI' => app(\App\Services\WebCheckinComunicazioneStato::class)->of($r)])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
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
            abort_unless($c->canale === 'whatsapp' && ! $c->nascosta_at, 422, 'Per l’email utilizza l’invio HTML, non un programma esterno.');
            if ($c->stato === 'preparato') $c->update(['stato' => 'apertura_richiesta']);

            return $c;
        });
        $content = $c->contenuto;
        $url = 'https://wa.me/'.preg_replace('/\D/', '', $content['destinatario']).'?text='.rawurlencode($content['corpo']);

        return redirect()->away($url)->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function eliminaModello(Request $request, int $id, int $model)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate(['versione' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($r, $model, $data) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $m = WebCheckinModello::where('struttura_id', $r->struttura_id)->lockForUpdate()->findOrFail($model);
            abort_unless((int)$m->versione === (int)$data['versione'], 409, 'Modello cambiato: ricarica prima di eliminarlo.');
            $m->delete();
        });
        return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Modello eliminato. Non verrà ricreato automaticamente.');
    }

    public function ripristinaModelli(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        DB::transaction(function () use ($r) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            foreach (WebCheckinMessaggio::iniziali() as $key => $values) {
                $m = WebCheckinModello::withTrashed()->firstOrCreate(['struttura_id' => $r->struttura_id, 'iniziale' => $key], $values);
                if ($m->trashed()) { $m->versione++; $m->save(); $m->restore(); }
            }
        });
        return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Modelli iniziali ripristinati; personalizzazioni conservate.');
    }

    public function nascondi(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate(['comunicazioni' => ['required', 'array', 'min:1', 'max:100'], 'comunicazioni.*' => ['required', 'integer', 'distinct', 'min:1']]);
        DB::transaction(function () use ($r, $data) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $q = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->whereIn('id', $data['comunicazioni']);
            abort_unless($q->count() === count($data['comunicazioni']), 404);
            $q->update(['nascosta_at' => now()]);
        });
        return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Comunicazioni rimosse dalla cronologia visibile. Eventuali messaggi già inviati non sono annullati. Richiesta e ospiti conservati.');
    }

    public function identita(Request $request, int $id)
    {
        $r = $this->owned($request, $id);
        $data = $request->validate(['colore' => ['required', 'regex:/\A#[a-fA-F0-9]{6}\z/D']], ['colore.required' => 'Scegli il colore della struttura.', 'colore.regex' => 'Scegli un colore valido nel formato #RRGGBB.']);
        DB::transaction(function () use ($r, $data) {
            $s = Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $s->forceFill(['web_checkin_colore' => $data['colore']])->save();
        });
        if ($request->expectsJson()) return response()->json(['colore' => $data['colore'], 'messaggio' => 'Colore salvato. Genera una nuova anteprima email.'])->header('Cache-Control', 'no-store');
        return redirect()->route('web_checkin.comunicazioni', $id)->with('success', 'Colore dell’invito e del modulo aggiornato per la sola struttura corrente. Prepara nuovamente gli inviti precedenti.');
    }

    public function anteprima(Request $request, int $id, int $communication)
    {
        $r = $this->owned($request, $id);
        $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $id)->whereNull('nascosta_at')->findOrFail($communication);
        abort_unless(isset($c->contenuto['layout'], $c->contenuto['identita']), 422, 'Prepara nuovamente questo invito per l’anteprima HTML.');
        return response()->view('emails.web-checkin', ['invito' => $c->contenuto, 'logo_src' => $c->contenuto['identita']['logo_url']])
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer')
            ->header('Content-Security-Policy', "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'");
    }

    public function invia(Request $request, int $id, int $communication)
    {
        $r = $this->owned($request, $id);
        WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->whereNull('nascosta_at')->findOrFail($communication);
        $request->validate(['conferma_destinatario' => ['accepted']]);
        $service = app(\App\Services\WebCheckinInvio::class);
        abort_if($service->impedimento(), 422, $service->impedimento());
        $c = DB::transaction(function () use ($r, $communication) {
            Struttura::whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
            $r = WebCheckinRichiesta::where('struttura_id', $r->struttura_id)->lockForUpdate()->findOrFail($r->id);
            $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->whereNull('nascosta_at')->lockForUpdate()->findOrFail($communication);
            abort_unless($c->canale === 'email', 422);
            abort_unless(app(WebCheckinLink::class)->valid($r) && $r->stato !== 'convertito', 422, 'Link scaduto, revocato o già preso in carico.');
            $content = $c->contenuto;
            abort_unless(isset($content['layout'], $content['identita']) && hash_equals(app(WebCheckinMessaggio::class)->improntaInvito($r), (string)($content['impronta_invito'] ?? '')), 422, 'Dati o identità modificati: prepara un nuovo invito.');
            abort_unless(app(\App\Services\WebCheckinIdentita::class)->of($r->struttura)['logo_path'], 422, 'Configura il logo reale della struttura prima dell’invio.');
            abort_unless(filter_var($content['destinatario'] ?? null, FILTER_VALIDATE_EMAIL), 422, 'Destinatario non valido.');
            if ($c->tentativo_at) return null;
            $c->update(['stato' => 'invio_in_corso', 'tentativo_at' => now()]);
            return $c;
        });
        if ($c) $service->invia($c);
        $level = $c ? match ($c->stato) { 'accettato' => 'success', 'non_riuscito' => 'error', default => 'warning' } : 'warning';
        $message = $c ? \App\Services\WebCheckinComunicazioneStato::esito($c) : 'Tentativo già registrato: nessun duplicato inviato. Consulta l’esito del tentativo esistente.';
        return redirect()->route('web_checkin.comunicazione', [$id, $communication])->with($level, $message);
    }

    private function ripresa(WebCheckinRichiesta $r): array
    {
        $c = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->where('canale', 'email')->whereNull('nascosta_at')->latest('id')->first();
        if (! $c) return [];
        $content = $c->contenuto;
        // Le vecchie preparazioni conservano il testo personalizzato; il link viene sempre risolto nuovamente.
        return ($content['sorgente'] ?? ['oggetto' => $content['oggetto'], 'corpo' => 'Apri {{link}}', 'impostazioni' => $content['layout'] ?? WebCheckinMessaggio::layoutIniziale()]) + ['canale' => 'email', 'modello_id' => $c->modello_id];
    }

    private function owned(Request $request, int $id): WebCheckinRichiesta
    {
        $sid = StrutturaAccess::resolve($request)->id;

        return WebCheckinRichiesta::with('struttura')->where('struttura_id', $sid)->findOrFail($id);
    }
}
