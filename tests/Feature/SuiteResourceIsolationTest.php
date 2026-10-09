<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\Support\IsolatedTestStorage;
use Tests\Support\TestingEnvironment;
use Tests\TestCase;

class SuiteResourceIsolationTest extends TestCase
{
    public function test_storage_distinti_non_eliminano_file_del_caso_precedente(): void
    {
        $first = Storage::disk('local');
        $first->put('ricevuta.pdf', '%PDF-1.4 SINTETICO %%EOF');
        $path = $first->path('ricevuta.pdf');
        $hash = hash_file('sha256', $path);
        $secondRoot = IsolatedTestStorage::create($this->app);
        try {
            $this->assertFalse(Storage::disk('local')->exists('ricevuta.pdf'));
            Storage::disk('local')->put('ricevuta.pdf', 'SECONDO CASO');
            $this->assertSame($hash, hash_file('sha256', $path));
        } finally {
            IsolatedTestStorage::remove($secondRoot);
        }
        $this->assertFileExists($path);
        $this->assertSame($hash, hash_file('sha256', $path));
        $this->assertDirectoryDoesNotExist($secondRoot);
    }

    public function test_pulizia_rifiuta_directory_non_possedute_e_preserva_documenti(): void
    {
        $info = TestingEnvironment::requireIsolatedRuntime();
        $protected = $info['checkout'].'/storage/app';
        $file = $protected.'/sentinella-suite.txt';
        file_put_contents($file, 'DOCUMENTO SINTETICO PREESISTENTE');
        try {
            try {
                IsolatedTestStorage::remove($protected);
                $this->fail('Directory non posseduta accettata');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('TEST_ISOLATION_REQUIRED', $error->getMessage());
            }
            $this->assertSame('DOCUMENTO SINTETICO PREESISTENTE', file_get_contents($file));
        } finally {
            unlink($file);
        }
    }
}
