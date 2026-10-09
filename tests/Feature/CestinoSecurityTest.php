<?php

namespace Tests\Feature;

use App\Models\CestinoItem;
use App\Models\Customers;
use App\Models\LicenzaAssegnazione;
use App\Models\Struttura;
use App\Models\Titolo;
use App\Models\User;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CestinoSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function contesto(): array
    {
        $adminA = $this->actor('admin');
        $ownerA = $this->ownerFor($adminA);
        $a = $this->structureFor($ownerA);
        $adminB = $this->actor('admin');
        $ownerB = $this->ownerFor($adminB);
        $b = $this->structureFor($ownerB);
        $reception = $this->actor('struttura_user', null, $a->id);
        $reception->update(['ruolo_operativo' => 'reception']);
        $proprietario = $this->actor('proprietario', $ownerA->id);
        $vuoto = $this->actor('proprietario', $this->ownerFor($adminA)->id);
        $super = $this->actor('super_admin');

        return compact('adminA', 'ownerA', 'a', 'adminB', 'ownerB', 'b', 'reception', 'proprietario', 'vuoto', 'super');
    }

    private function archiviaAdmin(array $c): CestinoItem
    {
        $c['adminB']->forceFill(['remember_token' => 'REMEMBER-SINTETICO-B'])->save();
        $this->actingAs($c['super'])->delete('/superadmin/amministratori/'.$c['adminB']->id)->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $c['adminB']->id]);
        $item = CestinoItem::where('entity_class', User::class)->where('original_id', $c['adminB']->id)->sole();
        $this->assertNull($item->struttura_id);

        return $item;
    }

    private function credenziali(): array
    {
        return [
            'istat_username' => 'ISTAT-UTENTE-SINTETICO',
            'istat_password' => 'ISTAT-PASSWORD-SINTETICA',
            'questura_username' => 'QUESTURA-UTENTE-SINTETICO',
            'questura_password' => 'QUESTURA-PASSWORD-SINTETICA',
            'questura_wskey' => 'WSKEY-SINTETICA',
            'questura_puk' => 'PUK-SINTETICO',
            'questura_codici' => 'CODICI-AUTH-SINTETICI',
        ];
    }

    private function archiviaStruttura(array $c): CestinoItem
    {
        $c['b']->update($this->credenziali());
        $this->actingAs($c['super'])->delete('/superadmin/strutture/'.$c['b']->id)->assertRedirect();
        $this->assertSoftDeleted('struttura', ['id' => $c['b']->id]);
        $item = CestinoItem::where('entity_class', Struttura::class)->where('original_id', $c['b']->id)->sole();
        $this->assertNull($item->struttura_id);

        return $item;
    }

    private function archiviaCliente(array $c, string $tenant = 'b'): CestinoItem
    {
        $this->actingAs($c['super']);
        $cliente = Customers::create(['struttura_id' => $c[$tenant]->id, 'name' => 'CLIENTE-SINTETICO-'.$tenant, 'surname' => 'Cestino']);
        $item = app(CestinoService::class)->archiveModel($cliente);
        $cliente->delete();

        return $item;
    }

    private function statoDatabase(): string
    {
        $state = [];
        foreach (['cestino_items', 'users', 'proprietari', 'struttura', 'clienti', 'licenza_assegnazioni', 'crm_leads'] as $table) {
            $state[$table] = hash('sha256', DB::table($table)->orderBy('id')->get()->toJson());
        }

        return hash('sha256', json_encode($state, JSON_THROW_ON_ERROR));
    }

    private function mutazioneNegata(CestinoItem $item, string $azione, int $status = 404): void
    {
        $before = $this->statoDatabase();
        $response = $azione === 'restore'
            ? $this->post('/cestino/'.$item->id.'/ripristina')
            : $this->delete('/cestino/'.$item->id);
        // Controlla anche il DB prima dell'asserzione HTTP, per non mascherare scritture.
        $this->assertSame($before, $this->statoDatabase(), 'Il rifiuto deve lasciare il database invariato.');
        $response->assertStatus($status);
    }

    public static function ruoliTenant(): array
    {
        return [['reception'], ['proprietario']];
    }

    #[DataProvider('ruoliTenant')]
    public function test_ruolo_tenant_non_legge_snapshot_globali(string $ruolo): void
    {
        $c = $this->contesto();
        $admin = $this->archiviaAdmin($c);
        $structure = $this->archiviaStruttura($c);
        $response = $this->actingAs($c[$ruolo])->get('/cestino')->assertOk();
        $this->assertSame([], $response->viewData('items')->getCollection()->pluck('id')->all());
        $response->assertDontSee($admin->payload['password'])->assertDontSee($structure->title);
        foreach ($this->credenziali() as $value) {
            $response->assertDontSee($value);
        }
    }

    public static function globaliMutazioni(): array
    {
        $cases = [];
        foreach (['reception', 'proprietario'] as $ruolo) {
            foreach (['admin', 'struttura'] as $tipo) {
                foreach (['restore', 'purge'] as $azione) {
                    $cases[$ruolo.'-'.$tipo.'-'.$azione] = [$ruolo, $tipo, $azione];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('globaliMutazioni')]
    public function test_ruolo_tenant_non_muta_snapshot_globali(string $ruolo, string $tipo, string $azione): void
    {
        $c = $this->contesto();
        $item = $tipo === 'admin' ? $this->archiviaAdmin($c) : $this->archiviaStruttura($c);
        $this->actingAs($c[$ruolo]);
        $this->mutazioneNegata($item, $azione);
    }

    public function test_proprietario_senza_strutture_non_legge_cliente_estero(): void
    {
        $c = $this->contesto();
        $this->archiviaCliente($c);
        $response = $this->actingAs($c['vuoto'])->get('/cestino')->assertOk();
        $this->assertFalse(str_contains($response->getContent(), 'CLIENTE-SINTETICO-b'), 'Cliente estero esposto in HTML.');
        $this->assertSame(0, $response->viewData('items')->total());
    }

    public static function azioni(): array
    {
        return [['restore'], ['purge']];
    }

    #[DataProvider('azioni')]
    public function test_proprietario_senza_strutture_non_muta_cliente_estero(string $azione): void
    {
        $c = $this->contesto();
        $item = $this->archiviaCliente($c);
        $this->actingAs($c['vuoto']);
        $this->mutazioneNegata($item, $azione);
    }

    public static function ruoliConTenant(): array
    {
        return [['reception'], ['proprietario'], ['adminA']];
    }

    #[DataProvider('ruoliConTenant')]
    public function test_operazioni_legittime_sul_cliente_proprio(string $ruolo): void
    {
        $c = $this->contesto();
        $item = $this->archiviaCliente($c, 'a');
        $this->actingAs($c[$ruolo])->get('/cestino')->assertOk()->assertSee('CLIENTE-SINTETICO-a');
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $this->assertDatabaseHas('clienti', ['id' => $item->original_id, 'struttura_id' => $c['a']->id]);
        $this->assertDatabaseMissing('cestino_items', ['id' => $item->id]);
        $this->actingAs($c['super']);
        $cliente = Customers::withoutGlobalScopes()->findOrFail($item->original_id);
        $second = app(CestinoService::class)->archiveModel($cliente);
        $cliente->delete();
        $this->actingAs($c[$ruolo])->delete('/cestino/'.$second->id)->assertRedirect();
        $this->assertDatabaseMissing('cestino_items', ['id' => $second->id]);
        $this->assertDatabaseMissing('clienti', ['id' => $item->original_id]);
    }

    #[DataProvider('ruoliConTenant')]
    public function test_cliente_estero_escluso_anche_con_sid_e_sessione_estranei(string $ruolo): void
    {
        $c = $this->contesto();
        $item = $this->archiviaCliente($c);
        $this->actingAs($c[$ruolo])->withSession(['struttura_corrente_id' => $c['b']->id])
            ->get('/cestino?sid='.$c['b']->id)->assertOk()->assertDontSee('CLIENTE-SINTETICO-b');
        $this->mutazioneNegata($item, 'restore');
        $this->mutazioneNegata($item, 'purge');
    }

    public function test_admin_non_accede_struttura_globale_e_licenza_resta_sola_lettura(): void
    {
        $c = $this->contesto();
        $item = $this->archiviaStruttura($c);
        $licenza = CestinoItem::create(['entity_class' => LicenzaAssegnazione::class, 'entity_type' => 'Licenza',
            'struttura_id' => $c['a']->id, 'title' => 'Licenza sintetica', 'payload' => [], 'deleted_at' => now()]);
        $this->actingAs($c['adminA'])->get('/cestino')->assertOk()->assertDontSee($item->title)->assertSee('Licenza sintetica');
        $this->mutazioneNegata($item, 'restore');
        $this->mutazioneNegata($item, 'purge');
        $this->mutazioneNegata($licenza, 'restore', 403);
        $this->mutazioneNegata($licenza, 'purge', 403);
    }

    public function test_anonimo_e_csrf_errato_restano_esclusi(): void
    {
        $c = $this->contesto();
        $item = $this->archiviaAdmin($c);
        auth()->logout();
        $this->get('/cestino')->assertRedirect('/login');
        $before = $this->statoDatabase();
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect('/login');
        $this->delete('/cestino/'.$item->id)->assertRedirect('/login');
        $this->assertSame($before, $this->statoDatabase());
        $this->actingAs($c['super'])->post('/cestino/'.$item->id.'/ripristina', ['_token' => 'ERRATO'])->assertStatus(419);
        $this->assertSame($before, $this->statoDatabase());
    }

    public function test_superadmin_non_riceve_segreti_nell_html_anche_da_snapshot_legacy(): void
    {
        $c = $this->contesto();
        $admin = $this->archiviaAdmin($c);
        $structure = $this->archiviaStruttura($c);
        // Simula vecchi snapshot: il filtro HTML deve proteggere anche il preesistente.
        $admin->update(['payload' => array_merge($admin->payload, ['remember_token' => 'REMEMBER-LEGACY-SINTETICO',
            'api_secret' => 'SECRET-LEGACY-SINTETICO', 'access_token' => 'ACCESS-LEGACY-SINTETICO'])]);
        $before = $this->statoDatabase();
        $response = $this->actingAs($c['super'])->get('/cestino')->assertOk()->assertSee($structure->title);
        foreach ([$c['adminB']->getRawOriginal('password'), 'REMEMBER-LEGACY-SINTETICO', 'SECRET-LEGACY-SINTETICO',
            'ACCESS-LEGACY-SINTETICO', ...array_values($this->credenziali())] as $value) {
            $this->assertFalse(str_contains($response->getContent(), $value), 'Valore sensibile esposto in HTML.');
        }
        $this->assertSame($before, $this->statoDatabase(), 'La proiezione non modifica gli snapshot legacy.');
    }

    public function test_snapshot_user_minimizza_token_ma_preserva_hash_necessario_al_restore(): void
    {
        $c = $this->contesto();
        $item = $this->archiviaAdmin($c);
        $this->assertArrayNotHasKey('remember_token', $item->payload);
        $this->assertSame($c['adminB']->getRawOriginal('password'), $item->payload['password']);
    }

    public function test_superadmin_ripristina_admin_e_struttura_con_credenziali_intatte(): void
    {
        $c = $this->contesto();
        $admin = $this->archiviaAdmin($c);
        $structure = $this->archiviaStruttura($c);
        $this->actingAs($c['super'])->post('/cestino/'.$admin->id.'/ripristina')->assertRedirect();
        $this->assertNotSoftDeleted('users', ['id' => $c['adminB']->id]);
        $this->assertSame($c['adminB']->id, $c['ownerB']->fresh()->admin_id);
        $this->assertSame($c['adminB']->getRawOriginal('password'), User::findOrFail($c['adminB']->id)->getRawOriginal('password'));
        $this->post('/cestino/'.$structure->id.'/ripristina')->assertRedirect();
        $this->assertNotSoftDeleted('struttura', ['id' => $c['b']->id]);
        foreach ($this->credenziali() as $key => $value) {
            $this->assertSame($value, Struttura::findOrFail($c['b']->id)->getAttribute($key));
        }
        $this->assertDatabaseMissing('cestino_items', ['id' => $admin->id]);
        $this->assertDatabaseMissing('cestino_items', ['id' => $structure->id]);
    }

    public function test_superadmin_puo_ricreare_admin_assente_con_hash_originale(): void
    {
        $c = $this->contesto();
        $item = $this->archiviaAdmin($c);
        User::withTrashed()->findOrFail($c['adminB']->id)->forceDelete();
        $this->actingAs($c['super'])->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $this->assertSame($c['adminB']->getRawOriginal('password'), User::findOrFail($c['adminB']->id)->getRawOriginal('password'));
    }

    public function test_superadmin_purge_struttura_e_ripristina_catalogo_globale(): void
    {
        $c = $this->contesto();
        $structure = $this->archiviaStruttura($c);
        $this->actingAs($c['super'])->delete('/cestino/'.$structure->id)->assertRedirect();
        $this->assertDatabaseMissing('struttura', ['id' => $c['b']->id]);
        $titolo = Titolo::create(['nome' => 'Titolo sintetico', 'attivo' => true]);
        $item = app(CestinoService::class)->archiveModel($titolo);
        $titolo->delete();
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $this->assertDatabaseHas('titolo', ['id' => $titolo->id, 'nome' => 'Titolo sintetico']);
    }

    public function test_webcheckin_non_archivia_vecchio_token_e_restore_ne_genera_uno_nuovo(): void
    {
        $c = $this->contesto();
        $this->actingAs($c['super']);
        $richiesta = WebCheckinRichiesta::create(['struttura_id' => $c['a']->id, 'codice' => 'WC1',
            'numero_prenotazione' => 'SINTETICA', 'email' => 'fixture@example.invalid', 'nome_referente' => 'Fixture',
            'arrivo' => '2026-10-05', 'partenza' => '2026-10-06', 'token' => 'TOKEN-WEB-SINTETICO', 'stato' => 'da_inviare']);
        $item = app(CestinoService::class)->archiveModel($richiesta);
        $richiesta->delete();
        $this->assertArrayNotHasKey('token', $item->payload);
        $this->actingAs($c['reception'])->get('/cestino')->assertOk()->assertDontSee('TOKEN-WEB-SINTETICO');
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $restored = WebCheckinRichiesta::where('struttura_id', $c['a']->id)->sole();
        $this->assertNotSame('TOKEN-WEB-SINTETICO', $restored->token);
        $this->assertSame(64, strlen($restored->token));
        $this->assertNotNull($restored->link_revoked_at);
        $this->get('/checkin/'.$restored->token)->assertNotFound();
    }

    public function test_classi_sconosciute_e_ruoli_sconosciuti_negati(): void
    {
        $c = $this->contesto();
        $item = CestinoItem::create(['struttura_id' => $c['a']->id, 'entity_class' => 'ClasseNonAutorizzata',
            'entity_type' => 'Cliente', 'title' => 'Snapshot non supportato', 'payload' => [], 'deleted_at' => now()]);
        $this->actingAs($c['super'])->get('/cestino')->assertOk()->assertDontSee('Snapshot non supportato');
        $this->mutazioneNegata($item, 'restore');
        $this->mutazioneNegata($item, 'purge');
        $cliente = $this->archiviaCliente($c, 'a');
        $unknown = $this->actor('unknown', null, $c['a']->id);
        $this->actingAs($unknown)->get('/cestino')->assertOk()->assertDontSee('CLIENTE-SINTETICO-a');
        $this->mutazioneNegata($cliente, 'restore');
        $this->mutazioneNegata($cliente, 'purge');
    }

    public function test_classe_globale_con_tenant_e_cliente_senza_tenant_non_aprono_accesso(): void
    {
        $c = $this->contesto();
        $global = $this->archiviaAdmin($c);
        $global->update(['struttura_id' => $c['a']->id]);
        $cliente = $this->archiviaCliente($c, 'a');
        $cliente->update(['struttura_id' => null]);
        $this->actingAs($c['reception'])->get('/cestino')->assertOk()->assertDontSee('CLIENTE-SINTETICO-a')->assertDontSee($global->title);
        foreach ([$global, $cliente] as $item) {
            $this->mutazioneNegata($item, 'restore');
            $this->mutazioneNegata($item, 'purge');
        }
    }
}
