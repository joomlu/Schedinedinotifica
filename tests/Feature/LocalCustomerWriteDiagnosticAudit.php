<?php

namespace Tests\Feature;

use App\Models\Customers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalCustomerWriteDiagnosticAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_put_cliente_estraneo_con_payload_valido(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        $user = $this->actor('struttura_user', null, $a->id);
        $customer = Customers::forceCreate(['struttura_id' => $b->id, 'name' => 'Riservato', 'surname' => 'Sintetico']);
        $this->actingAs($user);
        $response = $this->put('/clienti/'.$customer->id, ['name' => 'Persona sintetica', 'surname' => 'Audit', 'type_cliente' => 'Richiesta', 'privacy_consent' => 1]);
        fwrite(STDOUT, '\nDIAGNOSI_PUT_CLIENTE '.json_encode(['status' => $response->status(), 'errors' => session('errors')?->getBag('default')->getMessages()]).'\n');
        $response->assertNotFound();
        $this->assertSame('Riservato', $customer->fresh()->name);
    }
}
