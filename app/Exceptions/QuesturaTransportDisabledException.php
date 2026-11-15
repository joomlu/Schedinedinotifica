<?php

namespace App\Exceptions;

final class QuesturaTransportDisabledException extends \RuntimeException
{
    public const RESPONSE_HEADER = 'X-Questura-Transport-Disabled';

    public const MESSAGE = 'Web Service Questura disabilitato per la struttura.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
