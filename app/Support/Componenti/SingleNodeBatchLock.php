<?php

namespace App\Support\Componenti;

use Closure;
use RuntimeException;

final class SingleNodeBatchLock
{
    public const LOCK_NOT_ACQUIRED = 'LOCK_NOT_ACQUIRED';

    private Closure $acquire;
    private Closure $release;

    public function __construct(Closure $acquire, Closure $release)
    {
        $this->acquire = $acquire;
        $this->release = $release;
    }

    /**
     * Esegue una sezione critica protetta da lock locale single-node.
     */
    public function execute(callable $criticalSection)
    {
        if (!(bool) ($this->acquire)()) {
            throw new RuntimeException(self::LOCK_NOT_ACQUIRED);
        }

        try {
            return $criticalSection();
        } finally {
            ($this->release)();
        }
    }
}
