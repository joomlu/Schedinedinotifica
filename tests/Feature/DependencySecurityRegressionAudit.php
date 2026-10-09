<?php

namespace Tests\Feature;

use League\Flysystem\CorruptedPathDetected;
use League\Flysystem\WhitespacePathNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Tests\TestCase;

class DependencySecurityRegressionAudit extends TestCase
{
    public function test_path_info_non_perde_il_confine_della_barra(): void
    {
        $request = Request::create('/api/index.phpfoo', 'GET', [], [], [], [
            'SCRIPT_FILENAME' => '/var/www/api/index.php',
            'SCRIPT_NAME' => '/api/index.php',
            'PHP_SELF' => '/api/index.php',
        ]);
        $this->assertSame('/api/index.php', $request->getBaseUrl());
        $this->assertSame('/foo', $request->getPathInfo());
    }

    public function test_path_storage_rifiuta_utf8_malformato_e_controlli(): void
    {
        $normalizer = new WhitespacePathNormalizer;
        foreach (["sintetico/\xff.txt", "sintetico/\x00.txt"] as $path) {
            try {
                $normalizer->normalizePath($path);
                $this->fail('Percorso storage malformato accettato');
            } catch (CorruptedPathDetected $error) {
                $this->assertInstanceOf(CorruptedPathDetected::class, $error);
            }
        }
        $this->assertSame('sintetico/valido.txt', $normalizer->normalizePath('sintetico/valido.txt'));
    }
}
