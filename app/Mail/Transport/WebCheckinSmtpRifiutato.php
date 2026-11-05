<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;

/** Solo esiti che escludono l'accettazione; nessun dettaglio SMTP riservato. */
final class WebCheckinSmtpRifiutato extends TransportException
{
    public function __construct(public readonly string $motivo)
    {
        parent::__construct('Invio Web Check-in non accettato.');
    }
}
