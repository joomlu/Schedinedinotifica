<?php

namespace Tests\Feature;

use App\Models\{CalendarioEvento, Componenti, Customers, Schedina};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CalendarioOperativoTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    private function contesto(): array
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id]);
        return [$s, $u];
    }

    public function test_hotel_singolo_non_espone_portfolio_neanche_con_url_precedente(): void
    {
        [$s] = $this->contesto();
        foreach (['', '?contesto=portfolio'] as $query) {
            $this->get('/calendario'.$query)->assertOk()
                ->assertViewHas('contesto', 'struttura')
                ->assertViewHas('canSelectStructure', false)
                ->assertViewHas('selectedStructureId', $s->id)
                ->assertDontSee('Tutte le strutture')
                ->assertSee('Note personali riservate');
        }
        $this->get('/calendario?contesto=personale')->assertOk()->assertViewHas('contesto', 'personale');
        $this->get('/calendario?contesto=portfolio&struttura_id='.$this->structureFor(null)->id)->assertForbidden();
    }

    public function test_amministratore_e_proprietario_con_un_solo_hotel_non_hanno_selettori_inutili(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $proprietario = $this->actor('proprietario', $owner->id);
        foreach ([$admin, $proprietario] as $user) {
            $this->actingAs($user)->get('/calendario?contesto=portfolio')->assertOk()
                ->assertViewHas('contesto', 'struttura')->assertViewHas('selectedStructureId', $s->id)
                ->assertViewHas('canSelectStructure', false)->assertDontSee('Tutte le strutture');
        }
    }

    public function test_storico_filtra_stato_e_mantiene_il_filtro_nei_collegamenti(): void
    {
        $this->contesto();
        foreach (['completata', 'chiusa', 'da_fare'] as $stato) {
            $this->post('/calendario', $this->payload(['tipo' => 'nota', 'titolo' => 'Archivio '.$stato, 'stato' => $stato, 'data_evento' => '2026-10-01']))->assertSessionHasNoErrors();
        }
        $this->get('/calendario?giorno=2026-10-30&sezione=storico&stato_storico=chiusa')->assertOk()
            ->assertViewHas('historyEvents', fn ($entries) => $entries->count() === 1 && $entries->first()['stato'] === 'chiusa')
            ->assertSee('Archivio chiusa')
            ->assertSee('stato_storico=chiusa')->assertSee('calendar-history-state');
    }

    private function payload(array $extra = []): array
    {
        return array_merge(['contesto' => 'struttura', 'titolo' => 'Attività sintetica', 'data_evento' => '2026-10-30', 'priorita' => 'normale', 'stato' => 'da_fare'], $extra);
    }

    public function test_salva_tre_categorie_senza_schema_nuovo_e_preserva_le_note_legacy(): void
    {
        [$s, $u] = $this->contesto();
        foreach (['manuale', 'nota', 'evento'] as $tipo) {
            $this->post('/calendario', $this->payload(['tipo' => $tipo, 'titolo' => 'Voce '.$tipo]))->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->post('/calendario', $this->payload())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('calendario_eventi', ['titolo' => 'Attività sintetica', 'tipo' => 'manuale', 'struttura_id' => $s->id]);
        foreach (['informazioni' => 'nota', 'attivita' => 'manuale', 'eventi' => 'evento'] as $categoria => $tipo) {
            $this->get('/calendario?giorno=2026-10-30&vista=day&categoria='.$categoria)->assertOk()
                ->assertViewHas('periodEvents', fn ($events) => $events->isNotEmpty() && $events->every(fn ($e) => $e['categoria'] === $categoria));
        }
        $evento = CalendarioEvento::where('tipo', 'evento')->firstOrFail();
        $this->put('/calendario/'.$evento->id, $this->payload(['tipo' => 'nota']))->assertSessionHasNoErrors();
        $this->assertSame('nota', $evento->fresh()->tipo);
        $this->post('/calendario/'.$evento->id.'/stato', ['stato' => 'completata'])->assertSessionHasNoErrors();
        $this->assertNotNull($evento->fresh()->completato_at);
        $this->post('/calendario/'.$evento->id.'/stato', ['stato' => 'da_fare'])->assertSessionHasNoErrors();
        $this->assertNull($evento->fresh()->completato_at);
    }

    public function test_settimana_a_cavallo_del_mese_e_giorno_del_passato_non_diventa_chiuso(): void
    {
        [$s] = $this->contesto();
        foreach (['2026-10-31', '2026-11-01', '2026-11-02'] as $data) {
            $this->post('/calendario', $this->payload(['data_evento' => $data]))->assertSessionHasNoErrors();
        }
        $this->get('/calendario?giorno=2026-10-30&vista=week')->assertOk()
            ->assertViewHas('periodEvents', fn ($e) => $e->count() === 2)
            ->assertViewHas('periodEnd', fn ($d) => $d->toDateString() === '2026-11-01');
        $this->get('/calendario?mese=2026-11&vista=agenda')->assertOk()->assertViewHas('periodEvents', fn ($e) => $e->count() === 2);
        Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Capo sintetico', 'surname' => 'Calendario', 'arrive' => '2020-01-02', 'departure' => '2020-01-03', 'cant_people' => 3, 'is_arrive' => false, 'circuito' => 'schedina']);
        $this->get('/calendario?giorno=2020-01-02&vista=day')->assertOk()->assertViewHas('periodEvents', fn ($e) => $e->first()['stato_label'] === 'Registrato in schedina');
    }

    public function test_dettaglio_componenti_tenant_e_soggiorni_effettivi(): void
    {
        [$s] = $this->contesto();
        $altro = $this->structureFor(null);
        $h = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Cliente sintetico', 'surname' => 'Calendario', 'arrive' => '2026-10-30', 'departure' => '2026-11-01', 'cant_people' => 2, 'is_arrive' => false, 'circuito' => 'schedina']);
        Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $h->id, 'name' => 'Componente ammesso', 'surname' => 'Sintetico']);
        Componenti::forceCreate(['struttura_id' => $altro->id, 'schedina_id' => $h->id, 'name' => 'Componente estraneo', 'surname' => 'Sintetico']);
        foreach (['arrivi', 'web_checkin', 'bozza'] as $tipo) Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Escluso '.$tipo, 'arrive' => '2026-10-30', 'departure' => '2026-11-01', 'is_arrive' => $tipo === 'arrivi', 'circuito' => $tipo]);
        $this->get('/calendario?giorno=2026-10-30&vista=day')->assertOk()->assertSee('Componente ammesso')->assertDontSee('Componente estraneo')->assertDontSee('Escluso arrivi')->assertDontSee('Escluso web_checkin')
            ->assertViewHas('periodEvents', fn ($e) => $e->count() === 1 && $e->first()['persone'] === 2);
        $this->get('/calendario?struttura_id='.$altro->id)->assertForbidden();
    }

    public function test_note_private_e_negativi_di_modifica_su_due_tenant(): void
    {
        [$s, $u] = $this->contesto();
        $altro = $this->structureFor(null);
        $v = $this->actor('struttura_user', null, $altro->id);
        $this->post('/calendario', $this->payload(['contesto' => 'personale', 'tipo' => 'nota', 'titolo' => 'Nota privata sintetica']))->assertSessionHasNoErrors();
        $privata = CalendarioEvento::withoutGlobalScopes()->where('user_scope_id', $u->id)->firstOrFail();
        $this->post('/calendario', $this->payload(['tipo' => 'evento', 'titolo' => 'Evento struttura A']))->assertSessionHasNoErrors();
        $condivisa = CalendarioEvento::withoutGlobalScopes()->where('struttura_id', $s->id)->firstOrFail();
        $this->get('/calendario?contesto=struttura&giorno=2026-10-30')->assertOk()->assertDontSee('Nota privata sintetica');
        $this->actingAs($v)->withSession(['struttura_corrente_id' => $altro->id]);
        foreach ([$privata, $condivisa] as $e) {
            $prima = $e->getAttributes();
            $this->put('/calendario/'.$e->id, $this->payload(['tipo' => 'nota']))->assertForbidden();
            $this->post('/calendario/'.$e->id.'/stato', ['stato' => 'completata'])->assertForbidden();
            $this->assertSame($prima, $e->fresh()->getAttributes());
        }
        $this->get('/calendario?contesto=portfolio&giorno=2026-10-30')->assertOk()->assertDontSee('Nota privata sintetica')->assertDontSee('Evento struttura A');
    }

    public function test_storico_mostra_la_categoria_richiesta_senza_caricarlo_nella_vista_normale(): void
    {
        $this->contesto();
        foreach (['nota', 'manuale', 'evento'] as $tipo) {
            $this->post('/calendario', $this->payload(['tipo' => $tipo, 'titolo' => 'Storico '.$tipo, 'data_evento' => '2026-10-01', 'stato' => 'completata']))->assertSessionHasNoErrors();
        }
        $this->get('/calendario?giorno=2026-10-30&vista=day')->assertOk()->assertViewHas('history', fn ($e) => $e->isEmpty());
        $this->get('/calendario?giorno=2026-10-30&sezione=storico&categoria=eventi')->assertOk()
            ->assertSee('Storico evento')->assertDontSee('Storico manuale')->assertDontSee('Storico nota')
            ->assertViewHas('historyEvents', fn ($e) => $e->count() === 1 && $e->first()['categoria'] === 'eventi');
    }

    public function test_mese_scelto_non_eredita_il_giorno_30_o_31_della_data_corrente(): void
    {
        $this->contesto();
        Carbon::setTestNow(Carbon::parse('2026-10-31 12:00:00'));
        try {
            foreach (['2026-02', '2024-02', '2026-04'] as $mese) {
                $this->get('/calendario?mese='.$mese.'&vista=month')->assertOk()
                    ->assertViewHas('month', fn ($day) => $day->format('Y-m-d') === $mese.'-01');
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_date_e_categorie_non_valide_rifiutate(): void
    {
        $this->contesto();
        foreach (['vista=foo', 'categoria=foo', 'giorno=2026-02-30', 'mese=2026-13', 'giorno[]=2026-10-30', 'contesto[]=foo', 'struttura_id[]=1'] as $q) $this->getJson('/calendario?'.$q)->assertUnprocessable();
        $this->postJson('/calendario', $this->payload(['tipo' => 'checkin']))->assertUnprocessable();
        $this->postJson('/calendario', $this->payload(['data_evento' => '2026-02-30']))->assertUnprocessable();
    }

    public function test_compleanno_bisestile_e_bordo_griglia_presenti_in_tutte_le_viste(): void
    {
        [$s] = $this->contesto();
        Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Cliente bisestile', 'surname' => 'Sintetico', 'nac_reg' => '2000-02-29']);
        foreach (['month', 'week', 'day', 'agenda'] as $vista) {
            $this->get('/calendario?giorno=2027-02-28&vista='.$vista.'&categoria=eventi')->assertOk()
                ->assertViewHas('periodEvents', fn ($e) => $e->contains(fn ($v) => str_contains($v['titolo'], 'Cliente bisestile') && $v['data_evento'] === '2027-02-28' && str_contains($v['descrizione'], '27 anni')));
        }
    }
    public function test_portfolio_rispetta_la_struttura_scelta_anche_nel_giorno_e_nella_ricerca(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $a = $this->structureFor($owner);
        $b = $this->structureFor($owner);
        $estranea = $this->structureFor($this->ownerFor($this->actor('admin')));
        foreach ([$a, $b, $estranea] as $s) {
            Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Calendario tenant '.$s->id, 'arrive' => '2026-10-30', 'departure' => '2026-11-01', 'cant_people' => 1, 'is_arrive' => false, 'circuito' => 'schedina']);
        }
        $this->actingAs($admin);
        $base = '/calendario?contesto=portfolio&giorno=2026-10-30&vista=day';
        $this->get($base)->assertOk()->assertViewHas('periodEvents', fn ($e) => $e->count() === 2);
        $this->get($base.'&struttura_id='.$a->id)->assertOk()
            ->assertViewHas('periodEvents', fn ($e) => $e->count() === 1 && str_contains($e->first()['titolo'], 'tenant '.$a->id))
            ->assertDontSee('Calendario tenant '.$b->id);
        $this->get($base.'&struttura_id='.$a->id.'&q=inesistente')->assertOk()->assertViewHas('periodEvents', fn ($e) => $e->isEmpty());
        $this->get($base.'&struttura_id='.$estranea->id)->assertForbidden();
    }

    public function test_testo_utente_escapato_e_csrf_errato_non_scrive(): void
    {
        $this->contesto();
        $testo = '<img src=x onerror="alert(1)">';
        $this->post('/calendario', $this->payload(['titolo' => $testo]))->assertSessionHasNoErrors();
        $this->get('/calendario?giorno=2026-10-30&vista=day')->assertOk()->assertSee($testo)->assertDontSee($testo, false);
        $prima = CalendarioEvento::withoutGlobalScopes()->count();
        $this->post('/calendario', $this->payload(['_token' => 'csrf-errato-sintetico']))->assertStatus(419);
        $this->assertSame($prima, CalendarioEvento::withoutGlobalScopes()->count());
    }

}
