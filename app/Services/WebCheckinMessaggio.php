<?php

namespace App\Services;

use App\Models\WebCheckinRichiesta;
use Illuminate\Validation\ValidationException;

/** Sostituzione testuale: nessuna valutazione Blade, PHP o HTML. */
class WebCheckinMessaggio
{
    public const VARIABLES = ['struttura', 'referente', 'prenotazione', 'arrivo', 'partenza', 'persone', 'link', 'scadenza', 'email_struttura', 'telefono_struttura', 'messaggio_ospite'];

    public function improntaInvito(WebCheckinRichiesta $request): string
    {
        return hash_hmac('sha256', json_encode([$request->only(['struttura_id', 'nome_referente', 'numero_prenotazione', 'email', 'whatsapp', 'arrivo', 'partenza', 'quantita_persone', 'messaggio_ospite', 'numero_adulti', 'numero_bambini', 'token', 'short_token', 'link_expires_at']), app(WebCheckinIdentita::class)->of($request->struttura)], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function render(string $subject, string $body, WebCheckinRichiesta $request, array $layout = []): array
    {
        $values = [
            'messaggio_ospite' => $request->messaggio_ospite ?: 'Completa i dati prima dell’arrivo. Puoi salvare e continuare più tardi.',
            'struttura' => $request->struttura?->nome_struttura,
            'referente' => $request->nome_referente,
            'prenotazione' => $request->numero_prenotazione,
            'arrivo' => $request->arrivo?->format('d/m/Y'),
            'partenza' => $request->partenza?->format('d/m/Y'),
            'persone' => $request->quantita_persone,
            'link' => filled($request->short_token) ? route('web_checkin.public.short.show', ['token' => $request->short_token]) : route('web_checkin.public.show', ['token' => $request->token]),
            'scadenza' => ($request->link_expires_at ?? app(WebCheckinLink::class)->deadline($request))?->timezone('Europe/Rome')->format('d/m/Y H:i:s T'),
            'email_struttura' => $request->struttura?->email,
            'telefono_struttura' => $request->struttura?->telefono,
        ];
        $replace = function (string $text) use ($values): string {
            if (preg_match('/\{!!|<\?(?:php|=)|@(?:php|include|extends|inject)\b/i', $text)) {
                throw ValidationException::withMessages(['messaggio' => 'Usa testo semplice e le variabili disponibili.']);
            }
            $rendered = preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/', function ($match) use ($values) {
                $name = $match[1];
                if (! array_key_exists($name, $values)) {
                    throw ValidationException::withMessages(['messaggio' => 'Variabile sconosciuta: '.$name.'.']);
                }
                if (blank($values[$name])) {
                    throw ValidationException::withMessages(['messaggio' => 'Dato mancante per la variabile '.$name.'.']);
                }

                return (string) $values[$name];
            }, $text);
            if (str_contains($rendered, '{{') || str_contains($rendered, '}}')) {
                throw ValidationException::withMessages(['messaggio' => 'Sintassi di una variabile non valida.']);
            }

            return $rendered;
        };
        if (! str_contains($body, '{{link}}') && ! preg_match('/\{\{\s*link\s*\}\}/', $body)) {
            throw ValidationException::withMessages(['corpo' => 'Inserisci la variabile {{link}} nel messaggio.']);
        }

        $layout = array_replace(self::layoutIniziale(), array_intersect_key($layout, self::layoutIniziale()));
        foreach ($layout as $key => $text) $layout[$key] = $replace($text);
        $renderedSubject = $replace($subject);
        if (strpbrk($renderedSubject, "\r\n") !== false || mb_strlen($renderedSubject) > 255) throw ValidationException::withMessages(['oggetto' => 'L’oggetto deve stare su una sola riga, entro255 caratteri.']);
        if (blank(trim($layout['pulsante'])) || mb_strlen($layout['pulsante']) > 80) throw ValidationException::withMessages(['impostazioni.pulsante' => 'Inserisci un’etichetta del pulsante entro80 caratteri.']);
        $values['link'] = route('web_checkin.public.show', ['token' => $request->token]);

        return ['oggetto' => $renderedSubject, 'corpo' => $replace($body), 'layout' => $layout,
            'dati' => $values, 'identita' => app(WebCheckinIdentita::class)->of($request->struttura)];
    }

    public static function layoutIniziale(): array
    {
        return ['saluto' => 'Gentile {{referente}},',
            'testo' => "il tuo soggiorno a {{struttura}} si avvicina!\nCompleta il check-in online prima dell’arrivo:\nci aiuterai ad accoglierti più velocemente.",
            'pulsante' => 'Completa il web check-in',
            'firma' => "Ti aspettiamo!\nLa Reception · {{struttura}}"];
    }

    public static function iniziali(): array
    {
        $common = "Gentile {{referente}},\nla struttura {{struttura}} ti invita a preparare il Web Check-in per la prenotazione {{prenotazione}}.\nArrivo: {{arrivo}}; partenza: {{partenza}}; persone previste: {{persone}}.\n\n";
        $end = "\n\nApri {{link}}\nPuoi salvare e riprendere i dati incompleti. Il link scade il {{scadenza}}. La reception verificherà i dati prima della registrazione definitiva.";

        return [
            'singolo' => ['nome' => 'Invito Web Check-in — Ospite singolo', 'oggetto' => '{{struttura}} · Il tuo check-in online', 'corpo' => $common.'Inserisci i tuoi dati personali e del documento.'.$end],
            'famiglia' => ['nome' => 'Invito Web Check-in — Famiglia / referente familiare', 'oggetto' => 'Web Check-in famiglia — {{struttura}} — {{prenotazione}}', 'corpo' => $common.'Come referente familiare, inserisci i tuoi dati e quelli di ogni familiare ospitato, aggiungendo i componenti. Se non conosci un dato, salva e completalo in seguito con la reception.'.$end],
            'gruppo' => ['nome' => 'Invito Web Check-in — Gruppo / più ospiti', 'oggetto' => 'Web Check-in gruppo — {{struttura}} — {{prenotazione}}', 'corpo' => $common.'Come referente del gruppo, inserisci i tuoi dati e quelli degli altri ospiti nei componenti. Verifica il numero delle persone e segnala alla reception le informazioni ancora mancanti.'.$end],
        ];
    }
}
