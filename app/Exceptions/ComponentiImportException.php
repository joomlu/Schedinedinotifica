<?php

namespace App\Exceptions;

use RuntimeException;

class ComponentiImportException extends RuntimeException
{
    /**
     * @param array<int, string> $issues
     */
    public function __construct(
        string $message,
        public readonly array $issues = []
    ) {
        parent::__construct($message);
    }
}