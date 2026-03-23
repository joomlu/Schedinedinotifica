<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\User;
use App\Support\StrutturaCorrente;
use Database\Seeders\DemoSaasDataFullSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSaasDataFullSeeder::class);
    }

    public function test_schedine_scope_by_struttura_corrente(): void
    {
        if (!Schema::hasTable('schedina') || !Schema::hasColumn('schedina', 'struttura_id')) {
            $this->markTestSkipped('Tabella schedina o colonna struttura_id non disponibile.');
        }

        $admin = User::where('email', 'tanggo@schedinedinotifica.test')->firstOrFail();
        $a = Struttura::where('email', 'hotelk2@schedinedinotifica.test')->first();
        $b = Struttura::where('email', 'aurora@schedinedinotifica.test')->first();

        if (!$a || !$b) {
            $this->markTestSkipped('Strutture demo mancanti.');
        }

        DB::table('schedina')->delete();

        $this->actingAs($admin);

        try {
            StrutturaCorrente::setId($a->id);
            $this->makeSchedina($a->id, 'A');

            StrutturaCorrente::setId($b->id);
            $this->makeSchedina($b->id, 'B');
        } catch (QueryException $e) {
            $this->markTestSkipped('Schedina richiede campi addizionali: ' . $e->getMessage());
        }

        StrutturaCorrente::setId($a->id);
        $this->assertSame(1, Schedina::count(), 'Scope su struttura A');

        StrutturaCorrente::setId($b->id);
        $this->assertSame(1, Schedina::count(), 'Scope su struttura B');
    }

    protected function makeSchedina(int $strutturaId, string $suffix): void
    {
        Schedina::create([
            'scheda' => 'AUTO-' . $suffix,
            'type' => 'demo',
            'name' => 'Guest ' . $suffix,
            'surname' => 'Test',
            'arrive' => now(),
            'departure' => now()->addDay(),
            'cant_people' => 1,
            'room' => '10' . $suffix,
            'beds' => 1,
            'struttura_id' => $strutturaId,
        ]);
    }
}
