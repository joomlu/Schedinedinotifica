<?php

namespace Tests\Feature;

use App\Http\Controllers\StrutturaController;
use App\Models\Struttura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class QuesturaCredentialEncryptionTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures;

    public function test_questura_credentials_are_encrypted_at_rest_and_decrypted_in_memory(): void
    {
        $struttura = Struttura::query()->create([
            'nome_struttura' => 'Hotel Test',
            'tipologia_generale' => 'Alberghiera',
            'tipologia_struttura' => 'Hotel',
            'regione' => 'Lazio',
            'provincia' => 'RM',
            'citta' => 'Roma',
            'indirizzo' => 'Via Roma 1',
            'cap' => '00100',
            'latitudine' => 41.90,
            'longitudine' => 12.49,
            'telefono' => '1234567890',
            'email' => 'hotel@example.com',
            'questura_username' => 'user-questura',
            'questura_password' => 'secret-password',
            'questura_wskey' => 'secret-wskey',
        ]);

        $raw = DB::table('struttura')->where('id', $struttura->getKey())->first();

        $this->assertNotSame('secret-password', $raw->questura_password);
        $this->assertNotSame('secret-wskey', $raw->questura_wskey);
        $this->assertSame('secret-password', $struttura->fresh()->questura_password);
        $this->assertSame('secret-wskey', $struttura->fresh()->questura_wskey);
    }

    public function test_questura_credentials_are_not_rehydrated_in_the_form(): void
    {
        $struttura = Struttura::query()->create([
            'nome_struttura' => 'Hotel Test',
            'tipologia_generale' => 'Alberghiera',
            'tipologia_struttura' => 'Hotel',
            'regione' => 'Lazio',
            'provincia' => 'RM',
            'citta' => 'Roma',
            'indirizzo' => 'Via Roma 1',
            'cap' => '00100',
            'latitudine' => 41.90,
            'longitudine' => 12.49,
            'telefono' => '1234567890',
            'email' => 'hotel@example.com',
            'questura_username' => 'user-questura',
            'questura_password' => 'secret-password',
            'questura_wskey' => 'secret-wskey',
        ]);

        View::share('errors', new \Illuminate\Support\ViewErrorBag());

        $html = view('struttura.form', [
            'struttura' => $struttura,
            'tipologieGenerali' => collect(),
            'tipologieStruttura' => collect(),
            'classificazioni' => collect(),
            'zoneOptions' => collect(),
            'localitaOptions' => collect(),
        ])->render();

        $this->assertStringNotContainsString('value="secret-password"', $html);
        $this->assertStringNotContainsString('value="secret-wskey"', $html);
    }

    public function test_blank_questura_credentials_keep_existing_values(): void
    {
        $struttura = Struttura::query()->create([
            'nome_struttura' => 'Hotel Test',
            'tipologia_generale' => 'Alberghiera',
            'tipologia_struttura' => 'Hotel',
            'regione' => 'Lazio',
            'provincia' => 'RM',
            'citta' => 'Roma',
            'indirizzo' => 'Via Roma 1',
            'cap' => '00100',
            'latitudine' => 41.90,
            'longitudine' => 12.49,
            'telefono' => '1234567890',
            'email' => 'hotel@example.com',
            'questura_username' => 'user-questura',
            'questura_password' => 'secret-password',
            'questura_wskey' => 'secret-wskey',
        ]);

        $controller = new StrutturaController();

        $payload = $controller->preserveExistingQuesturaSecrets(['questura_password' => '', 'questura_wskey' => ''], $struttura);

        $this->assertArrayNotHasKey('questura_password', $payload);
        $this->assertArrayNotHasKey('questura_wskey', $payload);
        $struttura->update($payload);
        $this->assertSame('secret-password', $struttura->fresh()->questura_password);
    }
    public function test_serializzazione_e_migrazione_legacy_senza_perdita(): void
    {
        $model = new Struttura(['questura_password' => 'segreto-sintetico', 'questura_wskey' => 'chiave-sintetica']);
        $this->assertArrayNotHasKey('questura_password', $model->toArray());
        $this->assertArrayNotHasKey('questura_wskey', $model->toArray());
        $this->assertStringNotContainsString('segreto-sintetico', $model->toJson());
        $helper = \App\Support\Questura\LegacyCredentials::class;
        $cipher = $helper::encrypted('legacy-sintetico');
        $this->assertSame('legacy-sintetico', \Illuminate\Support\Facades\Crypt::decryptString($cipher));
        $this->assertSame($cipher, $helper::encrypted($cipher));
        $this->assertNull($helper::encrypted(null));
        $this->assertNull($helper::encrypted(''));
        $foreign = new \Illuminate\Encryption\Encrypter(random_bytes(32), 'AES-256-CBC');
        $this->expectException(\RuntimeException::class);
        $helper::encrypted($foreign->encryptString('segreto-altra-chiave'));
    }
    public function test_update_reale_vuoto_preserva_e_sostituzione_cifra_senza_flash(): void
    {
        $model = $this->structureFor(null);
        $model->update(['questura_password' => 'prima-sintetica', 'questura_wskey' => 'chiave-prima']);
        $before = $model->fresh()->getRawOriginal('questura_password');
        $this->actingAs($this->actor('struttura_user', null, $model->id));
        $data = $this->validStructurePayload($model) + ['questura_password' => '', 'questura_wskey' => ''];
        $this->put('/struttura', $data)->assertSessionHasNoErrors();
        $this->assertSame($before, $model->fresh()->getRawOriginal('questura_password'));
        $data['questura_password'] = 'nuova-sintetica';
        $this->put('/struttura', $data)->assertSessionHasNoErrors();
        $this->assertSame('nuova-sintetica', $model->fresh()->questura_password);
        $this->assertNotSame('nuova-sintetica', $model->fresh()->getRawOriginal('questura_password'));
        $data['nome_struttura'] = '';
        $this->put('/struttura', $data)->assertSessionHasErrors('nome_struttura');
        $this->assertNull(session('_old_input.questura_password'));
        $this->assertNull(session('_old_input.questura_wskey'));
    }
}
