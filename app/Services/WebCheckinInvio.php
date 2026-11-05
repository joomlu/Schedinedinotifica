<?php

namespace App\Services;

use App\Mail\WebCheckinInvito;
use App\Models\WebCheckinComunicazione;
use Illuminate\Support\Facades\Mail;

class WebCheckinInvio
{
    public function impedimento(): ?string
    {
        $name = config('web_checkin.mailer');
        $mailer = config('mail.mailers.'.$name, []);
        $from = $mailer['from']['address'] ?? config('mail.from.address');
        if (! config('web_checkin.email_abilitata') || ! in_array($mailer['transport'] ?? null, ['smtp', 'web_checkin_smtps'], true)
            || blank($mailer['host'] ?? null) || ! filter_var($from, FILTER_VALIDATE_EMAIL)
            || str_ends_with((string) $from, '@example.com')
            || (($mailer['transport'] ?? null) === 'web_checkin_smtps' && (! function_exists('curl_init')
                || ! in_array('smtps', curl_version()['protocols'], true) || ($mailer['scheme'] ?? null) !== 'smtps'
                || empty($mailer['username']) || empty($mailer['password'])))) {
            return 'Invio email non configurato: servono trasporto SMTP Web Check-in abilitato e mittente autorizzato. Contatta l’amministratore per verificare server, porta, autenticazione e mittente del servizio Web Check-in. L’anteprima non equivale a un invio.';
        }

        return null;
    }

    /** La prenotazione del tentativo viene già commessa sotto lock dal controller. */
    public function invia(WebCheckinComunicazione $c): void
    {
        try {
            $mailer = Mail::mailer(config('web_checkin.mailer'));
            $sent = $mailer->to($c->contenuto['destinatario'])->send(new WebCheckinInvito($c->contenuto));
            // Anche un trasporto senza ricevuta non autorizza un secondo tentativo automatico.
            $c->update($sent ? ['stato' => 'accettato', 'accettato_at' => now(), 'errore_codice' => null]
                : ['stato' => 'esito_incerto', 'errore_codice' => 'nessuna_ricevuta']);
        } catch (\App\Mail\Transport\WebCheckinSmtpRifiutato $e) {
            $c->update(['stato' => 'non_riuscito', 'errore_codice' => $e->motivo]);
        } catch (\Throwable) {
            // Non registrare eccezioni SMTP: possono contenere destinatari o messaggi con token.
            $c->update(['stato' => 'esito_incerto', 'errore_codice' => 'esito_non_verificabile']);
        }
    }
}
