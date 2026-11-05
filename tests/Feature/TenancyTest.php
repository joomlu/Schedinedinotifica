<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\User;
use App\Support\StrutturaCorrente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor('super_admin')->update(['email' => 'tanggo@schedinedinotifica.test']);
        $this->structureFor(null)->update(['email' => 'hotelk2@schedinedinotifica.test']);
        $this->structureFor(null)->update(['email' => 'aurora@schedinedinotifica.test']);
    }

    public function test_schedine_scope_by_struttura_corrente(): void
    {
        $admin = User::where('email', 'tanggo@schedinedinotifica.test')->firstOrFail();
        $a = Struttura::where('email', 'hotelk2@schedinedinotifica.test')->first();
        $b = Struttura::where('email', 'aurora@schedinedinotifica.test')->first();

        $this->assertNotNull($a);
        $this->assertNotNull($b);

        $this->actingAs($admin);

        StrutturaCorrente::setId($a->id);
        $this->makeSchedina($a->id, 'A');
        StrutturaCorrente::setId($b->id);
        $this->makeSchedina($b->id, 'B');

        StrutturaCorrente::setId($a->id);
        $this->assertSame(1, Schedina::count(), 'Scope su struttura A');

        StrutturaCorrente::setId($b->id);
        $this->assertSame(1, Schedina::count(), 'Scope su struttura B');
    }

    protected function makeSchedina(int $strutturaId, string $suffix): void
    {
        Schedina::create([
            'scheda' => 'AUTO-'.$suffix,
            'type' => 'demo',
            'name' => 'Guest '.$suffix,
            'surname' => 'Test',
            'arrive' => now(),
            'departure' => now()->addDay(),
            'cant_people' => 1,
            'room' => '10'.$suffix,
            'beds' => 1,
            'struttura_id' => $strutturaId,
        ]);
    }
}
