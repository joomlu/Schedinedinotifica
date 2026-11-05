<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class WebCheckinInvito extends Mailable
{
    public function __construct(private array $invito) {}

    public static function testo(array $i): string
    {
        return implode("\n", [$i['layout']['saluto'], '', $i['layout']['testo'], '',
            $i['layout']['pulsante'].': '.$i['dati']['link'], '',
            (int)$i['dati']['persone'] === 1 ? 'Inserisci i tuoi dati.' : 'Inserisci i tuoi dati e quelli degli altri ospiti.',
            'Puoi salvare e continuare più tardi.', '', 'Prenotazione: '.$i['dati']['prenotazione'],
            'Arrivo: '.$i['dati']['arrivo'], 'Partenza: '.$i['dati']['partenza'], 'Ospiti: '.$i['dati']['persone'], '',
            $i['layout']['firma'], $i['identita']['email'] ?? '', $i['identita']['telefono'] ?? '', '',
            'Link disponibile fino al '.$i['dati']['scadenza'].'.']);
    }

    public function build(): static
    {
        return $this->subject($this->invito['oggetto'])
            ->view('emails.web-checkin')
            ->text('emails.web-checkin-text')
            ->with(['invito' => $this->invito, 'logo_src' => null, 'testo_email' => self::testo($this->invito)]);
    }
}
