<?php

namespace Tests\Feature;

use App\Http\Controllers\SchedinaController;
use App\Models\Componenti;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class QuesturaUpstreamIsolationTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures;
    use \Tests\Support\WebCheckinPublicInput;

    // Sostituisce la vecchia ricerca testuale nel controller Arrivi, ora delegato.
    // Verifica la cancellazione effettiva del sincronizzatore condiviso senza toccare altri ospiti.
    public function test_delete_residuale_rimane_scoped_alla_schedina_e_alla_struttura(): void
    {
        $s = $this->structureFor(null);
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $a = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Primo', 'surname' => 'Sintetico']);
        $b = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Secondo', 'surname' => 'Sintetico']);
        $c = Schedina::forceCreate(['struttura_id' => $other->id, 'name' => 'Altro', 'surname' => 'Sintetico']);
        $make = fn ($p) => Componenti::forceCreate(['struttura_id' => $p->struttura_id, 'schedina_id' => $p->id, 'name' => 'Componente', 'surname' => 'Sintetico']);
        $keep = $make($a);
        $remove = $make($a);
        $same = $make($b);
        $foreign = $make($c);
        $beforeSame = $same->fresh()->getRawOriginal();
        $beforeForeign = $foreign->fresh()->getRawOriginal();
        $request = Request::create('/', 'POST', ['componenti' => [['id' => $keep->id, 'name' => 'Conservato', 'surname' => 'Sintetico']]]);
        (new \ReflectionMethod(SchedinaController::class, 'syncComponenti'))->invoke(app(SchedinaController::class), $a, $request);
        $this->assertDatabaseMissing('componenti', ['id' => $remove->id]);
        $this->assertDatabaseHas('componenti', ['id' => $keep->id, 'schedina_id' => $a->id]);
        $this->assertSame($beforeSame, Componenti::withoutGlobalScopes()->findOrFail($same->id)->getRawOriginal());
        $this->assertSame($beforeForeign, Componenti::withoutGlobalScopes()->findOrFail($foreign->id)->getRawOriginal());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('percorsiPubblici')]
    public function test_ospite_non_sovrascrive_camere_riservate_alla_struttura(bool $breve): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-04-01 12:00:00', 'Europe/Rome'));
        try {
            $a = $this->structureFor(null);
            $b = $this->structureFor(null);
            $sheets = [];
            foreach ([$a->id, $a->id, $b->id] as $index => $sid) {
                $sheet = Schedina::forceCreate(['struttura_id' => $sid, 'circuito' => 'web', 'name' => 'Principale', 'surname' => 'Sintetico', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 1]);
                \Illuminate\Support\Facades\DB::table('schedina_camere')->insert(['struttura_id' => $sid, 'schedina_id' => $sheet->id, 'numero_camera' => 'QA-'.($index + 1), 'posti_letto' => 1]);
                $sheets[] = $sheet;
            }
            $r = \App\Models\WebCheckinRichiesta::create(['struttura_id' => $a->id, 'schedina_id' => $sheets[0]->id, 'codice' => 'QACAMERA', 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 1, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);
            $before = \Illuminate\Support\Facades\DB::table('schedina_camere')->orderBy('id')->get()->toJson();
            $url = $breve ? '/w/'.$r->codice.'-'.substr($r->token, 0, 8) : '/checkin/'.$r->token;
            $this->postWeb($url, ['name' => 'Nome modificato', 'surname' => 'Sintetico', 'cant_people' => 1, 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'room' => 99, 'beds' => 99, 'camere' => [['schedina_id' => $sheets[2]->id, 'struttura_id' => $b->id, 'numero_camera' => 'FORGIATA']]])->assertOk();
            $this->assertSame($before, \Illuminate\Support\Facades\DB::table('schedina_camere')->orderBy('id')->get()->toJson());
            $this->assertSame('Nome modificato', $sheets[0]->fresh()->name);
            $this->assertSame($a->id, $sheets[0]->fresh()->struttura_id);
        } finally {
            $this->travelBack();
        }
    }

    public static function percorsiPubblici(): array
    {
        return [[false], [true]];
    }
}
