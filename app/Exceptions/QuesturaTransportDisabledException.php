<?php

namespace App\Exceptions;

final class QuesturaTransportDisabledException extends \RuntimeException
{
    public const RESPONSE_HEADER = 'X-Questura-Transport-Disabled';

    public const MESSAGE = 'Trasporto Questura globalmente disabilitato.';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
