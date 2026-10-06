<?php

namespace Tests\Feature;

use App\Models\Struttura;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

// DDL MySQL non deve essere eseguito dentro la transazione di RefreshDatabase.
class QuesturaLegacyMigrationTest extends TestCase
{
    use StrutturaFixtures;

    public function test_migration_legacy_persistita_e_rieseguibile(): void
    {
        $model = $this->structureFor(null);
        try {
            DB::table('struttura')->where('id', $model->id)->update(['questura_password' => 'legacy-sintetico', 'questura_wskey' => '']);
            $migration = require database_path('migrations/2026_10_06_100000_encrypt_questura_credentials.php');
            $migration->up();
            $cipher = DB::table('struttura')->where('id', $model->id)->value('questura_password');
            $this->assertNotSame('legacy-sintetico', $cipher);
            $this->assertSame('legacy-sintetico', $model->fresh()->questura_password);
            $migration->up();
            $this->assertSame($cipher, DB::table('struttura')->where('id', $model->id)->value('questura_password'));
            $this->assertNull($model->fresh()->questura_wskey);
            $foreign = new \Illuminate\Encryption\Encrypter(random_bytes(32), 'AES-256-CBC');
            $ambiguous = $foreign->encryptString('sintetico');
            DB::table('struttura')->where('id', $model->id)->update(['questura_wskey' => $ambiguous]);
            try { $migration->up(); $this->fail('Ciphertext ambiguo convertito'); }
            catch (\RuntimeException) { $this->assertSame($ambiguous, DB::table('struttura')->where('id', $model->id)->value('questura_wskey')); }
        } finally {
            DB::table('struttura')->where('id', $model->id)->delete();
        }
    }
}
