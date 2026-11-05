<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

/** Diagnostico indipendente delle relazioni camere: nessuna correzione implicita. */
class WebCheckinCameraBoundaryAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_salvataggio_anonimo_valido_sostituisce_camere_senza_duplicarle(): void
    {
        $s = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'Principale sintetico', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 1]);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'CAMERA', 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 1, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);
        DB::table('schedina_camere')->insert(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'numero_camera' => '11', 'posti_letto' => 1]);
        $this->assertSame(1, DB::table('schedina_camere')->where('schedina_id', $p->id)->count());
        $this->post('/checkin/'.$r->token, ['name' => 'Principale sintetico', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 1, 'camere' => [['numero_camera' => '12', 'posti_letto' => 1]]])->assertOk();
        $this->assertSame(1, DB::table('schedina_camere')->where('schedina_id', $p->id)->count(), 'Salvataggio valido: camera precedente non rimossa e nuova camera aggiunta');
        $this->assertSame('12', DB::table('schedina_camere')->where('schedina_id', $p->id)->value('numero_camera'));
    }
}
