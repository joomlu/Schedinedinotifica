<?php

namespace Tests\Feature;

use App\Models\CalendarioEvento;
use App\Models\Componenti;
use App\Models\CrmLead;
use App\Models\Customers;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalAcceptanceMatrixAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function context(): array
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $a = $this->structureFor($owner);
        $b = $this->structureFor($this->ownerFor($this->actor('admin')));
        $user = $this->actor('struttura_user', null, $a->id);
        $user->update(['ruolo_operativo' => 'proprietario']);
        $super = $this->actor('super_admin');

        return compact('admin', 'owner', 'a', 'b', 'user', 'super');
    }

    public static function archiveClasses(): array
    {
        return array_map(fn ($class) => [$class], [...CestinoService::TENANT_CLASSES, ...CestinoService::GLOBAL_CLASSES]);
    }

    #[DataProvider('archiveClasses')]
    public function test_cestino_restore_purge_minimizzazione_per_ogni_classe(string $class): void
    {
        $c = $this->context();
        $this->actingAs($c['super']);
        $parent = Schedina::forceCreate(['struttura_id' => $c['a']->id, 'name' => 'PADRE-SINTETICO', 'surname' => 'Audit', 'circuito' => 'web']);
        $article = LicenzaArticolo::create(['nome' => 'Articolo effimero']);
        $data = match ($class) {
            User::class => $this->actor('admin')->getAttributes(),
            Proprietario::class => $this->ownerFor($c['admin'])->getAttributes(),
            Struttura::class => $this->structureFor($c['owner'])->getAttributes(),
            Customers::class => ['struttura_id' => $c['a']->id, 'name' => 'CLIENTE-SINTETICO', 'surname' => 'Audit'],
            Schedina::class => ['struttura_id' => $c['a']->id, 'name' => 'SCHEDINA-SINTETICA', 'surname' => 'Audit'],
            Componenti::class => ['struttura_id' => $c['a']->id, 'schedina_id' => $parent->id, 'name' => 'COMPONENTE-SINTETICO', 'surname' => 'Audit'],
            WebCheckinRichiesta::class => ['struttura_id' => $c['a']->id, 'schedina_id' => $parent->id, 'codice' => 'AUDIT-C', 'numero_prenotazione' => 'EFFIMERA', 'nome_referente' => 'REFERENTE', 'email' => 'referente@example.invalid', 'arrivo' => now()->toDateString(), 'partenza' => now()->addDays(2)->toDateString(), 'quantita_persone' => 2, 'token' => str_repeat('e', 64), 'stato' => 'da_inviare'],
            LicenzaAssegnazione::class => ['articolo_id' => $article->id, 'struttura_id' => $c['a']->id, 'proprietario_id' => $c['owner']->id, 'admin_id' => $c['admin']->id],
            LicenzaArticolo::class => ['nome' => 'ARTICOLO-SINTETICO'],
            \App\Models\TassaEsenzione::class => ['struttura_id' => $c['a']->id, 'codice' => 'AUDIT', 'descrizione' => 'Esenzione sintetica'],
            \App\Models\TipoCliente::class, \App\Models\TipoDocumento::class => ['codice' => 'AUDIT', 'descrizione' => 'Catalogo sintetico'],
            \App\Models\RilasciatoDa::class => ['name' => 'RILASCIO-SINTETICO'],
            default => ['nome' => 'CATALOGO-SINTETICO'],
        };
        $model = isset($data['id']) ? $class::withoutGlobalScopes()->findOrFail($data['id']) : $class::forceCreate($data);
        $id = $model->id;
        $service = app(CestinoService::class);
        $item = $service->archiveModel($model);
        $safe = json_encode($service->displayPayload($item));
        $this->assertStringNotContainsString('password', $safe);
        $this->assertStringNotContainsString('remember_token', $safe);
        $this->assertStringNotContainsString(str_repeat('e', 64), $safe);
        $model->delete();
        $this->get('/cestino?q='.urlencode((string) $item->title))->assertOk();
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $restored = in_array($class, [Schedina::class, Componenti::class, WebCheckinRichiesta::class])
            ? ($class === WebCheckinRichiesta::class ? $class::withoutGlobalScopes()->where('nome_referente', $model->nome_referente)->where('struttura_id', $c['a']->id)->sole() : $class::withoutGlobalScopes()->where('name', $model->name)->where('struttura_id', $c['a']->id)->sole())
            : $class::withoutGlobalScopes()->findOrFail($id);
        $this->assertSame($model->name, $restored->name);
        $this->assertSame($model->struttura_id, $restored->struttura_id);
        $id = $restored->id;
        if (method_exists($restored, 'trashed')) {
            $this->assertFalse($restored->trashed());
        }
        $this->assertDatabaseMissing('cestino_items', ['id' => $item->id]);
        if ($restored instanceof WebCheckinRichiesta) {
            $this->assertNotSame($data['token'], $restored->token);
        }
        $again = $service->archiveModel($restored);
        $restored->delete();
        $this->delete('/cestino/'.$again->id)->assertRedirect();
        $this->assertDatabaseMissing($model->getTable(), ['id' => $id]);
        $this->assertDatabaseMissing('cestino_items', ['id' => $again->id]);
    }

    public static function hierarchies(): array
    {
        return [['super_admin'], ['admin'], ['proprietario'], ['struttura_user']];
    }

    #[DataProvider('hierarchies')]
    public function test_gerarchia_strutture_creazione_modifica_eliminazione_esistenti(string $role): void
    {
        $c = $this->context();
        $actor = match ($role) {
            'super_admin' => $c['super'], 'admin' => $c['admin'], 'proprietario' => $this->actor($role, $c['owner']->id), default => $c['user']
        };
        $this->actingAs($actor);
        if (! in_array($role, ['super_admin', 'admin'])) {
            $this->post('/admin/strutture', ['nome_struttura' => 'NEGATA'])->assertForbidden();
            $this->post('/superadmin/strutture', ['nome_struttura' => 'NEGATA'])->assertForbidden();

            return;
        }
        $base = $role === 'super_admin' ? '/superadmin/strutture' : '/admin/strutture';
        $payload = ['nome_struttura' => 'STRUTTURA-CREATA-AUDIT', 'indirizzo' => 'Via Sintetica', 'proprietario_id' => $c['owner']->id];
        $this->get($base.'/create')->assertOk();
        $beforeCount = Struttura::count();
        $this->post($base, $payload)->assertSessionHasErrors(['nazione', 'regione', 'citta', 'provincia', 'cap']);
        $this->assertSame($beforeCount, Struttura::count());
        $payload += ['nazione' => 'Italia', 'regione' => 'Lazio', 'citta' => 'Roma', 'provincia' => 'RM', 'cap' => '00100'];
        $this->post($base, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $created = Struttura::where('nome_struttura', $payload['nome_struttura'])->sole();
        $this->assertSame($c['owner']->id, $created->proprietario_id);
        $this->get($base.'/'.$created->id.'/edit')->assertOk();
        $this->put($base.'/'.$created->id, [...$payload, 'nome_struttura' => 'STRUTTURA-MODIFICATA-AUDIT'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('STRUTTURA-MODIFICATA-AUDIT', $created->fresh()->nome_struttura);
        if ($role === 'admin') {
            $before = $c['b']->getRawOriginal();
            $this->put($base.'/'.$c['b']->id, $payload)->assertNotFound();
            $this->delete($base.'/'.$c['b']->id)->assertNotFound();
            $this->assertSame($before, $c['b']->fresh()->getRawOriginal());
        }
        $this->delete($base.'/'.$created->id)->assertRedirect();
        $this->assertSoftDeleted('struttura', ['id' => $created->id]);
    }

    public function test_supporto_calendario_notifiche_e_confini_tenant(): void
    {
        $c = $this->context();
        $this->actingAs($c['user']);
        $this->post('/supporto', ['titolo' => 'TICKET-SINTETICO-A', 'categoria' => 'altro', 'priorita' => 'normale', 'descrizione' => 'Messaggio sintetico non trasmesso'])->assertSessionHasNoErrors()->assertRedirect();
        $ticket = SupportTicket::withoutGlobalScopes()->where('titolo', 'TICKET-SINTETICO-A')->sole();
        $this->assertSame($c['a']->id, $ticket->struttura_id);
        $this->get('/supporto/'.$ticket->id)->assertOk();
        $this->post('/supporto/'.$ticket->id.'/rispondi', ['messaggio' => 'Risposta sintetica'])->assertSessionHasNoErrors()->assertRedirect();
        $this->post('/calendario', ['titolo' => 'EVENTO-SINTETICO-A', 'data_evento' => now()->toDateString(), 'priorita' => 'normale', 'stato' => 'da_fare'])->assertSessionHasNoErrors()->assertRedirect();
        $event = CalendarioEvento::withoutGlobalScopes()->where('titolo', 'EVENTO-SINTETICO-A')->sole();
        Componenti::forceCreate(['struttura_id' => $c['a']->id, 'name' => 'COMPLEANNO-SINTETICO', 'surname' => 'Audit', 'date_nac' => now()->subYears(30)->toDateString()]);
        Componenti::forceCreate(['struttura_id' => $c['a']->id, 'name' => 'DATA-NULLA', 'surname' => 'Audit', 'date_nac' => null]);
        $this->get('/calendario')->assertOk()->assertSee('EVENTO-SINTETICO-A')->assertSee('COMPLEANNO-SINTETICO');
        $this->post('/calendario/'.$event->id.'/stato', ['stato' => 'completata'])->assertRedirect();
        $this->assertSame('completata', $event->fresh()->stato);
        $this->get('/notifiche')->assertOk();
        $foreign = $this->actor('struttura_user', null, $c['b']->id);
        $this->actingAs($foreign);
        $this->get('/supporto')->assertOk()->assertDontSee('TICKET-SINTETICO-A');
        $search = $this->get('/supporto?q=TICKET-SINTETICO-A')->assertOk();
        $this->assertSame(0, $search->viewData('tickets')->total());
        $this->get('/calendario')->assertOk()->assertDontSee('EVENTO-SINTETICO-A');
        $before = $ticket->fresh()->getRawOriginal();
        foreach (['/supporto/'.$ticket->id, '/supporto/'.$ticket->id.'/rispondi'] as $path) {
            $response = str_ends_with($path, 'rispondi') ? $this->post($path, ['messaggio' => 'NEGATO']) : $this->get($path);
            $this->assertContains($response->status(), [403, 404]);
        }
        $this->assertSame($before, $ticket->fresh()->getRawOriginal());
        $before = $event->fresh()->getRawOriginal();
        $this->post('/calendario/'.$event->id.'/stato', ['stato' => 'chiusa'])->assertForbidden();
        $this->assertSame($before, $event->fresh()->getRawOriginal());
    }

    public function test_crm_locale_e_ruolo_admin_sola_lettura_licenze(): void
    {
        $c = $this->context();
        $this->actingAs($c['super']);
        $this->post('/superadmin/crm', ['struttura' => 'PROSPECT-SINTETICO', 'nome_cognome' => 'Persona sintetica', 'email' => 'prospect@example.invalid'])->assertSessionHasNoErrors()->assertRedirect();
        $lead = CrmLead::where('email', 'prospect@example.invalid')->sole();
        $this->get('/superadmin/crm/'.$lead->id)->assertOk()->assertSee('PROSPECT-SINTETICO');
        $this->put('/superadmin/crm/'.$lead->id, ['stato' => 'in_contatto', 'note_interne' => 'Nota sintetica'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('in_contatto', $lead->fresh()->stato);
        $this->post('/superadmin/crm/'.$lead->id.'/attivita', ['tipo' => 'nota', 'direzione' => 'interna', 'titolo' => 'ATTIVITA-SINTETICA', 'stato' => 'completata'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('crm_lead_activities', ['crm_lead_id' => $lead->id, 'titolo' => 'ATTIVITA-SINTETICA']);
        $article = LicenzaArticolo::create(['nome' => 'PIANO-SINTETICO', 'prezzo_base' => 12]);
        $this->put('/superadmin/strutture/'.$c['a']->id.'/servizio', ['articolo_id' => $article->id, 'attiva' => 1, 'piano' => 'PIANO-SINTETICO', 'stato_pagamento' => 'pagato', 'scadenza_servizio' => now()->addYear()->toDateString()])->assertSessionHasNoErrors()->assertRedirect();
        $license = LicenzaAssegnazione::where('struttura_id', $c['a']->id)->sole();
        $this->assertSame($article->id, $license->articolo_id);
        $this->get('/superadmin/pagamenti/licenze/'.$license->id.'/print')->assertOk();
        $this->actingAs($c['admin'])->get('/admin/crm')->assertForbidden();
        $this->get('/admin/pagamenti')->assertOk();
        $this->post('/admin/pagamenti/licenze', [])->assertForbidden();
        $this->actingAs($c['user'])->get('/superadmin/crm')->assertForbidden();
    }

    public function test_upload_reale_componenti_normalizza_persiste_e_rifiuta_batch_consumato(): void
    {
        $c = $this->context();
        $this->actingAs($c['user']);
        $parent = Schedina::forceCreate(['struttura_id' => $c['a']->id, 'name' => 'GRUPPO-UPLOAD', 'surname' => 'Audit']);
        $f = fopen('php://temp', 'w+');

        foreach ([16 => 'OSPITE SINGOLO', 17 => 'CAPOFAMIGLIA', 18 => 'CAPOGRUPPO', 19 => 'FAMILIARE', 20 => 'MEMBRO GRUPPO'] as $code => $label) {
            \App\Models\TipoAlloggiato::firstOrCreate(['codice' => (string) $code], ['descrizione' => $label]);
        }
        \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'DE'], ['nome' => 'GERMANIA', 'cittadinanza' => 'TEDESCA', 'is_italia' => false]);
        \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'IT'], ['nome' => 'ITALIA', 'cittadinanza' => 'ITALIANA', 'is_italia' => true]);
        $headers = app(\App\Services\ComponentiImportService::class)->headersTemplate();
        $row = ['Nome' => 'IMPORT-COMPONENTE', 'Cognome' => 'Sintetico', 'Sesso' => 'M', 'Nazione nascita' => 'GERMANIA', 'Data di nascita' => '01/01/1980', 'Cittadinanza' => 'TEDESCA'];
        fputcsv($f, $headers, ';');
        fputcsv($f, array_map(fn ($key) => $row[$key] ?? '', $headers), ';');
        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);
        $response = $this->post('/schedine/'.$parent->id.'/componenti/import', ['file_import' => \Illuminate\Http\UploadedFile::fake()->createWithContent('componenti.csv', $csv)])->assertOk();
        $preview = $response->viewData('preview');
        $this->assertSame(1, $preview['righe_valide']);
        $this->assertSame(0, Componenti::withoutGlobalScopes()->where('schedina_id', $parent->id)->count());
        $this->post('/schedine/'.$parent->id.'/componenti/import/conferma', ['import_batch_token' => $preview['batch_token']])->assertRedirect();
        $saved = Componenti::withoutGlobalScopes()->where('schedina_id', $parent->id)->sole();
        $this->assertSame('1980-01-01', substr($saved->getRawOriginal('date_nac'), 0, 10));
        $this->assertSame($c['a']->id, $saved->struttura_id);
        $this->post('/schedine/'.$parent->id.'/componenti/import/conferma', ['import_batch_token' => $preview['batch_token']])->assertRedirect();
        $this->assertSame(1, Componenti::withoutGlobalScopes()->where('schedina_id', $parent->id)->count());
    }

    public static function confiniCritici(): array
    {
        return [
            ['GET', '/clienti/{customer}/modifica'], ['GET', '/clienti/{customer}/stampa'], ['GET', '/clienti/{customer}/storico'], ['PUT', '/clienti/{customer}'], ['DELETE', '/clienti/{customer}'],
            ['GET', '/schedine/{parent}/modifica'], ['GET', '/schedine/{parent}/tassa/print'], ['PUT', '/schedine/{parent}'], ['DELETE', '/schedine/{parent}'],
            ['GET', '/schedine/{parent}/componenti/import'], ['POST', '/schedine/{parent}/componenti/import'], ['POST', '/schedine/{parent}/componenti/import/prepara'], ['POST', '/schedine/{parent}/componenti/import/conferma'],
            ['GET', '/web-checkin/{web}/modifica'], ['PUT', '/web-checkin/{web}'], ['DELETE', '/web-checkin/{web}'], ['POST', '/web-checkin/{web}/converti'],
            ['GET', '/componenti/{component}/modifica'], ['PUT', '/componenti/{component}'], ['DELETE', '/componenti/{component}'],
        ];
    }

    #[DataProvider('confiniCritici')]
    public function test_route_critiche_tenant_estraneo_senza_letture_o_scritture(string $method, string $path): void
    {
        $c = $this->context();
        $this->actingAs($c['user']);
        $customer = Customers::forceCreate(['struttura_id' => $c['b']->id, 'name' => 'RISERVATO-TENANT-B', 'surname' => 'Audit']);
        $parent = Schedina::forceCreate(['struttura_id' => $c['b']->id, 'name' => 'RISERVATO-TENANT-B', 'surname' => 'Audit', 'circuito' => 'web']);
        $component = Componenti::forceCreate(['struttura_id' => $c['b']->id, 'schedina_id' => $parent->id, 'name' => 'RISERVATO-TENANT-B', 'surname' => 'Audit']);
        $web = WebCheckinRichiesta::create(['struttura_id' => $c['b']->id, 'schedina_id' => $parent->id, 'codice' => 'TENANT-B', 'numero_prenotazione' => 'SINTETICA', 'nome_referente' => 'RISERVATO-TENANT-B', 'email' => 'synthetic@example.invalid', 'arrivo' => now(), 'partenza' => now()->addDays(2), 'quantita_persone' => 2, 'token' => str_repeat('f', 64)]);
        $path = strtr($path, ['{customer}' => $customer->id, '{parent}' => $parent->id, '{component}' => $component->id, '{web}' => $web->id]);
        $matched = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($path, $method));
        $this->assertNotSame('index', $matched->getName(), 'Non usare un 404 generico del catch-all come prova tenant.');
        $tables = ['clienti', 'schedina', 'componenti', 'web_checkin_richieste', 'cestino_items'];
        $snapshot = fn () => array_map(fn ($table) => DB::table($table)->orderBy('id')->get()->toJson(), $tables);
        $before = $snapshot();
        $response = $this->call($method, $path, ['name' => 'MODIFICA-NEGATA', 'surname' => 'Audit', 'type_cliente' => 'Richiesta', 'privacy_consent' => 1, 'schedina_id' => $parent->id]);
        $this->assertContains($response->status(), [403, 404]);
        $response->assertDontSee('RISERVATO-TENANT-B');
        $this->assertSame($before, $snapshot());
    }

    public function test_ricerca_ajax_estranea_e_controllo_positivo_locale(): void
    {
        $c = $this->context();
        $this->actingAs($c['user']);
        $own = Customers::forceCreate(['struttura_id' => $c['a']->id, 'name' => 'RICERCA-SINTETICA-A', 'surname' => 'Audit']);
        Customers::forceCreate(['struttura_id' => $c['b']->id, 'name' => 'RICERCA-SINTETICA-B', 'surname' => 'Audit']);
        $json = $this->get('/search_customers?query=RICERCA-SINTETICA')->assertOk()->json();
        $this->assertCount(1, $json);
        $this->assertSame($own->id, $json[0]['id']);
    }

    public static function ruoliProprietari(): array
    {
        return [['admin'], ['super_admin']];
    }

    #[DataProvider('ruoliProprietari')]
    public function test_proprietari_crud_e_confine_amministratore(string $role): void
    {
        $c = $this->context();
        $this->actingAs($role === 'admin' ? $c['admin'] : $c['super']);
        $base = $role === 'admin' ? '/admin/proprietari' : '/superadmin/proprietari';
        $payload = ['nome' => 'PROPRIETARIO-SINTETICO-CREATO', 'email' => 'owner-audit@example.invalid', 'admin_id' => $c['admin']->id];
        $this->get($base.'/create')->assertOk();
        $this->post($base, $payload)->assertSessionHasNoErrors()->assertRedirect();
        $owner = Proprietario::where('nome', $payload['nome'])->sole();
        $this->assertSame($c['admin']->id, $owner->admin_id);
        $this->put($base.'/'.$owner->id, [...$payload, 'nome' => 'PROPRIETARIO-MODIFICATO'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('PROPRIETARIO-MODIFICATO', $owner->fresh()->nome);
        if ($role === 'admin') {
            $foreign = $c['b']->proprietario;
            $before = $foreign->getRawOriginal();
            $this->get($base.'/'.$foreign->id.'/edit')->assertNotFound();
            $this->put($base.'/'.$foreign->id, $payload)->assertNotFound();
            $this->delete($base.'/'.$foreign->id)->assertNotFound();
            $this->assertSame($before, $foreign->fresh()->getRawOriginal());
        }
        $this->delete($base.'/'.$owner->id)->assertRedirect();
        $this->assertSoftDeleted('proprietari', ['id' => $owner->id]);
    }
}
