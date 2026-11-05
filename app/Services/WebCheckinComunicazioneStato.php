<?php

namespace App\Services;

use App\Models\WebCheckinComunicazione;
use App\Models\WebCheckinRichiesta;

/** Proiezione dei tentativi esistenti: nessun nuovo stato persistito sulla richiesta. */
class WebCheckinComunicazioneStato
{
    public function impedimento(WebCheckinRichiesta $r, ?WebCheckinComunicazione $c = null): ?string
    {
        if ($r->stato === 'convertito') return $r->processata()
            ? 'Check-in già effettuato: richiesta consultabile nello storico.'
            : 'Richiesta già presa in carico: apri la richiesta e verifica il collegamento alla schedina con l’amministratore. Nessun nuovo invio disponibile.';
        if (! app(WebCheckinLink::class)->valid($r)) {
            if (! app(WebCheckinLink::class)->deadline($r)?->isFuture()) return ($r->link_revoked_at ? 'Link revocato. ' : '').'Date del soggiorno trascorse o non coerenti. Apri la richiesta e verifica i dati originali: una nuova emissione non prolunga la scadenza oltre le regole del soggiorno.';
            return $r->link_revoked_at
                ? 'Link revocato. Apri la richiesta ed emetti esplicitamente un nuovo link prima di preparare l’invito.'
                : 'Link scaduto o non emesso. Apri la richiesta, verifica le date ed emetti un nuovo link.';
        }
        if (! filter_var($r->email, FILTER_VALIDATE_EMAIL)) return 'Email del referente mancante o non valida. Correggila in Apri richiesta.';
        if (! app(WebCheckinIdentita::class)->of($r->struttura)['logo_path']) return 'Manca il logo della struttura. Configura il logo prima dell’invio.';
        if ($c && (! isset($c->contenuto['layout'], $c->contenuto['identita']) || ! hash_equals(app(WebCheckinMessaggio::class)->improntaInvito($r), (string) ($c->contenuto['impronta_invito'] ?? '')))) {
            return 'Dati, logo o link modificati. Modifica l’invito e prepara una nuova anteprima.';
        }
        return app(WebCheckinInvio::class)->impedimento();
    }

    public function of(WebCheckinRichiesta $r): array
    {
        $query = WebCheckinComunicazione::where('struttura_id', $r->struttura_id)->where('richiesta_id', $r->id)->where('canale', 'email');
        // La rimozione dalla cronologia non cancella un invio accettato.
        $sent = (clone $query)->where('stato', 'accettato')->whereNotNull('accettato_at')->latest('accettato_at')->latest('id')->first();
        $last = (clone $query)->whereNull('nascosta_at')->latest('id')->first();
        $attempt = (clone $query)->whereNotNull('tentativo_at')->latest('id')->first();
        if ($attempt && (! $last || $attempt->id > $last->id)) $last = $attempt;
        $block = $this->impedimento($r, $last?->stato === 'preparato' ? $last : null);
        $ready = $last?->stato === 'preparato' && ! $block;
        $label = match ($last?->stato) {
            'invio_in_corso' => 'Invio in corso',
            'esito_incerto' => 'Esito da verificare',
            'non_riuscito' => 'Invio non riuscito',
            'accettato' => 'Inviato',
            default => $ready ? 'Pronto per l’invio' : ($sent ? 'Inviato' : 'Da preparare'),
        };
        return ['stato' => $label, 'ultimo' => $last, 'inviato' => $sent, 'pronto' => $ready, 'impedimento' => $block,
            'azione' => $ready ? 'Invia Web Check-in' : ($sent ? 'Reinvia Web Check-in' : 'Prepara invito')];
    }

    public static function esito(WebCheckinComunicazione $c): string
    {
        return match ($c->stato) {
            'accettato' => 'Inviato: accettato dal trasporto email; consegna non confermata.',
            'non_riuscito' => match ($c->errore_codice) {
                'connessione' => 'Invio non riuscito: connessione sicura al server SMTP non disponibile. Verifica il servizio e riprova.',
                'rifiuto_smtp' => 'Invio non riuscito: il server SMTP ha rifiutato il messaggio o l’autenticazione. Verifica mittente e configurazione con l’amministratore, poi riprova.',
                default => 'Invio non riuscito: configurazione SMTP non valida. Contatta l’amministratore prima di riprovare.',
            },
            'esito_incerto' => 'Esito da verificare: manca la conferma SMTP. Il messaggio potrebbe essere stato accettato. Verifica con l’amministratore prima di un nuovo invio volontario.',
            'invio_in_corso' => 'Invio in corso: attendi e ricarica la pagina, senza ripetere il tentativo.',
            'preparato' => 'Pronto per la verifica: nessun invio ancora eseguito.',
            default => 'Apertura esterna o preparazione precedente: invio email non verificato.',
        };
    }
}
