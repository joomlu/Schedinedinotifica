<?php

namespace Tests\Feature;

use App\Models\{CalendarioEvento, Componenti, Customers, Schedina};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CalendarioAutomaticiTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    public function test_compleanni_annuali_clienti_e_componenti_si_aggiornano_senza_eventi_copiati(): void
    {
        $s = $this->structureFor(null);
        $altra = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $cliente = Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Cliente compleanno', 'surname' => 'Sintetico', 'nac_reg' => '1980-11-03']);
        $componente = Componenti::forceCreate(['struttura_id' => $s->id, 'name' => 'Componente compleanno', 'surname' => 'Sintetico', 'date_nac' => '2000-11-03']);
        Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Senza nascita', 'surname' => 'Sintetico']);
        Componenti::forceCreate(['struttura_id' => $altra->id, 'name' => 'Estraneo compleanno', 'date_nac' => '2000-11-03']);
        $prima = CalendarioEvento::withoutGlobalScopes()->count();
        foreach ([2026, 2027] as $anno) {
            $this->get('/calendario?vista=day&giorno='.$anno.'-11-03')->assertOk()
                ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'compleanno')->count() === 2)
                ->assertSee('Data di nascita: 03/11/1980')->assertSee('Data di nascita: 03/11/2000')
                ->assertDontSee('Estraneo compleanno')->assertDontSee('Compleanno: Senza nascita');
        }
        $cliente->forceFill(['nac_reg' => '1980-11-04'])->save();
        $componente->forceFill(['date_nac' => '2000-11-04'])->save();
        $this->get('/calendario?vista=day&giorno=2026-11-03')->assertOk()
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'compleanno')->isEmpty());
        $this->get('/calendario?vista=month&giorno=2026-11-04')->assertOk()
            ->assertSee('data-calendar-automatic="compleanno"', false)
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'compleanno')->count() === 2 && $eventi->where('tipo', 'compleanno')->every(fn ($e) => $e['data_evento'] === '2026-11-04'));
        $this->get('/calendario?contesto=personale&vista=day&giorno=2026-11-04')->assertOk()
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'compleanno')->isEmpty());
        $this->assertSame($prima, CalendarioEvento::withoutGlobalScopes()->count());
    }

    public function test_portfolio_mostra_automatici_di_tutti_gli_hotel_autorizzati_anche_con_un_hotel_selezionato(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $estranea = $this->structureFor($this->ownerFor($this->actor('admin')));
        foreach ([$a, $b, $estranea] as $hotel) {
            Customers::forceCreate(['struttura_id' => $hotel->id, 'name' => 'Compleanno cliente '.$hotel->id, 'nac_reg' => '1980-11-03']);
            $schedina = Schedina::forceCreate(['struttura_id' => $hotel->id, 'name' => 'Arrivo portfolio '.$hotel->id, 'arrive' => '2026-11-03', 'departure' => '2026-11-04', 'circuito' => 'schedina', 'is_arrive' => false]);
            Componenti::forceCreate(['struttura_id' => $hotel->id, 'schedina_id' => $schedina->id, 'name' => 'Componente portfolio '.$hotel->id, 'date_nac' => '2000-11-03']);
        }
        foreach ([$admin, $this->actor('proprietario', $owner->id)] as $user) {
            $this->actingAs($user)->withSession(['struttura_corrente_id' => $a->id]);
            $this->get('/calendario?contesto=portfolio&vista=day&giorno=2026-11-03')->assertOk()
                ->assertViewHas('periodEvents', fn ($e) => $e->where('tipo', 'compleanno')->count() === 4 && $e->where('tipo', 'checkin')->count() === 2)
                ->assertSee('Componente portfolio '.$b->id)->assertDontSee('Arrivo portfolio '.$estranea->id);
            $this->get('/calendario?contesto=portfolio&vista=day&giorno=2026-11-03&struttura_id='.$b->id)->assertOk()
                ->assertViewHas('periodEvents', fn ($e) => $e->where('tipo', 'compleanno')->count() === 2 && $e->where('tipo', 'checkin')->count() === 1);
            $this->get('/calendario?contesto=portfolio&struttura_id='.$estranea->id)->assertForbidden();
        }
    }

    public function test_arrivo_e_partenza_seguono_la_schedina_senza_promemoria_manuali(): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $schedina = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Ospite automatico', 'arrive' => '2026-11-03', 'departure' => '2026-11-05', 'cant_people' => 2, 'circuito' => 'schedina', 'is_arrive' => false]);
        $prima = CalendarioEvento::withoutGlobalScopes()->count();
        foreach (['month', 'week', 'day'] as $vista) {
            $this->get('/calendario?vista='.$vista.'&giorno=2026-11-03')->assertOk()
                ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'checkin')->count() === 1)
                ->assertSee('Automatico · Registrato in schedina');
        }
        $schedina->forceFill(['arrive' => '2026-11-04', 'departure' => '2026-11-06'])->save();
        $this->get('/calendario?vista=day&giorno=2026-11-03')->assertOk()
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'checkin')->isEmpty());
        $this->get('/calendario?vista=day&giorno=2026-11-04')->assertOk()
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'checkin')->count() === 1);
        $this->get('/calendario?vista=day&giorno=2026-11-06')->assertOk()
            ->assertViewHas('periodEvents', fn ($eventi) => $eventi->where('tipo', 'checkout')->count() === 1);
        $this->assertSame($prima, CalendarioEvento::withoutGlobalScopes()->count());
    }
}
