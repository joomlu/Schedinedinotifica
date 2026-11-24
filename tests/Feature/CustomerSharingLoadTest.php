<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Services\CustomerSharing;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CustomerSharingLoadTest extends TestCase
{
    use StrutturaFixtures;

    protected function tearDown(): void
    {
        try {
            $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        } finally {
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
            parent::tearDown();
        }
    }

    public function test_tre_archivi_6501_schede_6162_identita_senza_perdere_origini_o_codici(): void
    {
        config(['customer_sharing.enabled' => true]);
        $owner = $this->ownerFor($this->actor('admin'));
        $user = $this->actor('proprietario', $owner->id);
        $hotels = [$this->structureFor($owner), $this->structureFor($owner), $this->structureFor($owner)];
        $rows = [];
        foreach ($hotels as $h => $hotel) {
            $people = array_merge(range($h * 2054, ($h + 1) * 2054 - 1), range((($h + 1) % 3) * 2054, (($h + 1) % 3) * 2054 + 112));
            foreach ($people as $person) $rows[] = ['struttura_id' => $hotel->id, 'name' => 'Persona '.$person, 'surname' => 'Sintetica '.$person,
                'nac_reg' => '1980-01-02', 'country_reg' => 'Italia', 'city_reg' => 'Comune sintetico', 'sex' => 'F',
                'type_housed' => 'Componente', 'numero_cliente' => 'SINT-'.$h.'-'.$person, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach (array_chunk($rows, 400) as $chunk) DB::table('clienti')->insert($chunk);
        $sourceCodes = DB::table('clienti')->whereIn('struttura_id', array_map(fn ($h) => $h->id, $hotels))->pluck('numero_cliente', 'id')->all();
        $sharing = app(CustomerSharing::class); $ids = array_map(fn ($h) => $h->id, $hotels);
        $start = microtime(true); $fingerprint = $sharing->fingerprint($ids); $previewSeconds = microtime(true) - $start;
        $start = microtime(true); $group = $sharing->configure($user, null, 0, 'Carico sintetico', $ids, $fingerprint); $seconds = microtime(true) - $start;
        $this->assertCount(6501, $sourceCodes);
        $this->assertSame(6162, DB::table('customer_persons')->where('group_id', $group)->count());
        $this->assertSame(6501, DB::table('customer_person_links')->whereIn('struttura_id', $ids)->where('provenienza', true)->count());
        foreach ($hotels as $hotel) {
            $this->assertSame(6162, $sharing->uniqueQuery(Customers::withoutGlobalScopes()->where('struttura_id', $hotel->id), $hotel->id)->count());
            $this->assertSame(2167, $sharing->packageQuery(Customers::withoutGlobalScopes()->where('struttura_id', $hotel->id), $hotel->id)->count());
        }
        $this->assertSame($sourceCodes, DB::table('clienti')->whereIn('id', array_keys($sourceCodes))->pluck('numero_cliente', 'id')->all());
        $this->assertSame(0, DB::table('schedina')->whereIn('struttura_id', $ids)->count());
        $this->assertSame([], $sharing->candidates($group));
        fwrite(STDERR, '\nCARICO_CONDIVISIONE '.json_encode(['schede' => 6501, 'persone' => 6162,
            'riferimenti_locali' => DB::table('clienti')->whereIn('struttura_id', $ids)->count(),
            'anteprima_secondi' => round($previewSeconds, 3), 'attivazione_secondi' => round($seconds, 3),
            'memoria_picco_mib' => round(memory_get_peak_usage(true) / 1048576, 1)]).'\n');
        $this->assertLessThan(120, $seconds, 'Attivazione oltre il limite di collaudo di 120 secondi.');
    }
}
