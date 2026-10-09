<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ComponentiImportReviewStatusTest extends TestCase
{
    public function test_contratto_review_status_preservato(): void
    {
        \Tests\Support\TestingEnvironment::requireIsolatedRuntime();
        // Lo script originale resta byte-identico e viene eseguito in un
        // processo separato: nessuna funzione o facade contamina la discovery.
        $process = new Process([PHP_BINARY, dirname(__DIR__).'/Support/componenti-import-review-status.php']);
        $process->run();
        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertStringContainsString('PASS: review status integration ok', $process->getOutput());
    }
}
