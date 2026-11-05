<?php

namespace Tests\Feature;

use App\Models\CustomerImportBatch;
use App\Models\CustomerImportRow;
use App\Models\Customers;
use App\Models\Schedina;
use App\Models\User;
use App\Services\CustomerImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class BaselineAcceptanceOperationalAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function operatore(): User
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $this->actingAs($u);

        return $u;
    }

    private function arrivo(string $arrive, string $departure): array
    {
        return ['save_mode' => 'to_arrivi', 'customer_privacy_consent' => 1, 'name' => 'Persona sintetica', 'surname' => 'Audit', 'sex' => 'M', 'arrive' => $arrive, 'departure' => $departure, 'cant_people' => 1, 'room' => 1, 'beds' => 1, 'relationship' => 'OSPITE SINGOLO', 'oa_country' => 'ITALIA', 'oa_region' => 'Lazio', 'oa_prov' => 'RM', 'oa_city' => 'Roma', 'oa_city_nac' => 'ITALIANA', 'oa_date_nac' => '1980-01-01', 'or_country' => 'ITALIA', 'or_region' => 'Lazio', 'or_prov' => 'RM', 'or_city' => 'Roma', 'or_cap' => '00100', 'or_typeaway' => 'Via', 'or_address' => 'Via Sintetica', 'or_num' => '1', 'or_doctype' => "CARTA DI IDENTITA'", 'or_doc' => 'SINTETICO', 'or_published_date' => '2025-01-01', 'or_expire' => '2030-01-01', 'or_published' => 'Comune sintetico', 'or_published_country' => 'ITALIA', 'or_published_city' => 'Roma'];
    }

    public function test_arrivi_conversione_e_numerazione_su_due_anni_preservano_date_complete(): void
    {
        $u = $this->operatore();
        $this->travelTo(\Carbon\Carbon::parse('2026-12-31 12:00:00'));
        $this->post('/arrivi', $this->arrivo('2026-12-31', '2027-01-03'))->assertSessionHasNoErrors()->assertRedirect();
        $first = Schedina::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->firstOrFail();
        $this->assertSame('A-26001', $first->scheda);
        $this->travelTo(\Carbon\Carbon::parse('2027-01-10 12:00:00'));
        $this->post('/arrivi', $this->arrivo('2027-01-10', '2027-01-12'))->assertSessionHasNoErrors()->assertRedirect();
        $second = Schedina::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->orderByDesc('id')->firstOrFail();
        $this->assertSame('A-27001', $second->scheda);
        $this->post('/arrivi/'.$first->id.'/a-schedina')->assertRedirect();
        $first->refresh();
        $this->assertSame('schedina', $first->circuito);
        $this->assertFalse((bool) $first->is_arrive);
        $this->assertSame('S-26001', $first->scheda);
        $this->assertSame('2026-12-31', substr((string) $first->getRawOriginal('arrive'), 0, 10));
        $this->assertSame('2027-01-03', substr((string) $first->getRawOriginal('departure'), 0, 10));
        $this->assertSame('A-27001', $second->fresh()->scheda);
        $this->assertSame(2, Schedina::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->count());
        $this->travelBack();
    }

    public function test_arrivo_esterno_non_convertibile_o_eliminabile(): void
    {
        $u = $this->operatore();
        $b = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $b->id, 'circuito' => 'arrivi', 'is_arrive' => 1, 'name' => 'RISERVATO-B', 'surname' => 'Sintetico']);
        $before = DB::table('schedina')->where('id', $p->id)->first();
        $this->post('/arrivi/'.$p->id.'/a-schedina')->assertNotFound();
        $this->post('/arrivi/'.$p->id.'/elimina')->assertNotFound();
        $this->assertEquals($before, DB::table('schedina')->where('id', $p->id)->first());
    }

    private function batch(User $u): CustomerImportBatch
    {
        $b = CustomerImportBatch::create(['struttura_id' => $u->struttura_id, 'user_id' => $u->id, 'original_name' => 'sintetico.csv', 'stored_path' => 'customer-imports/sintetico.csv', 'status' => 'draft']);
        $keys = ['numero_cliente', 'gruppo', 'subgroup', 'subgroup1', 'sesso', 'tipo_cliente', 'tipo_via_strada', 'nome', 'cognome', 'nazione_residenza', 'comune_residenza', 'provincia_residenza', 'cap_residenza', 'indirizzo_residenza', 'numero_civico_residenza', 'email', 'telefono', 'fax', 'cellulare', 'nazione_nascita', 'comune_nascita', 'provincia_nascita', 'cittadinanza', 'data_nascita', 'tipo_documento', 'numero_documento', 'data_rilascio', 'data_scadenza', 'rilasciato_da'];
        for ($i = 1; $i <= 2; $i++) {
            $payload = array_merge(array_fill_keys($keys, ''), ['nome' => 'Persona importata '.$i, 'cognome' => 'Sintetico', 'sesso' => 'M', 'data_nascita' => '1980-01-01', 'tipo_cliente' => 'Componente', 'email' => 'import-'.$i.'@example.invalid']);
            CustomerImportRow::create(['batch_id' => $b->id, 'row_number' => $i, 'status' => CustomerImportService::STATUS_VALID, 'raw_payload' => [], 'normalized_payload' => $payload]);
        }

        return $b;
    }

    public function test_conferma_importazione_ripetuta_e_confine_tenant_del_batch(): void
    {
        $u = $this->operatore();
        $batch = $this->batch($u);
        $this->post('/clienti/import/'.$batch->id.'/conferma')->assertRedirect();
        $this->assertSame(2, Customers::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->count());
        $ids = $batch->rows()->pluck('imported_customer_id')->all();
        $this->post('/clienti/import/'.$batch->id.'/conferma')->assertRedirect();
        $this->assertSame($ids, $batch->rows()->pluck('imported_customer_id')->all());
        $this->assertSame(2, Customers::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->count());
        $foreign = $this->structureFor(null);
        $v = $this->actor('struttura_user', null, $foreign->id);
        $this->actingAs($v);
        $before = DB::table('customer_import_batches')->where('id', $batch->id)->first();
        $this->get('/clienti/import/'.$batch->id)->assertNotFound();
        $this->post('/clienti/import/'.$batch->id.'/conferma')->assertNotFound();
        $this->delete('/clienti/import/'.$batch->id)->assertNotFound();
        $this->assertEquals($before, DB::table('customer_import_batches')->where('id', $batch->id)->first());
    }

    public function test_errore_nella_seconda_riga_importata_ripristina_intero_batch(): void
    {
        $u = $this->operatore();
        $batch = $this->batch($u);
        $before = $batch->rows()->get()->toJson();
        $event = 'eloquent.creating: '.Customers::class;
        $count = 0;
        Event::listen($event, function () use (&$count) {
            if (++$count === 2) {
                throw new \RuntimeException('ERRORE-SINTETICO-IMPORT');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->post('/clienti/import/'.$batch->id.'/conferma');
            $this->fail('Errore tardivo non propagato');
        } catch (\RuntimeException $e) {
            $this->assertSame('ERRORE-SINTETICO-IMPORT', $e->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame(0, Customers::withoutGlobalScopes()->where('struttura_id', $u->struttura_id)->count());
        $this->assertSame($before, $batch->rows()->get()->toJson());
        $this->assertSame('draft', $batch->fresh()->status);
    }
}
