<?php

namespace Tests\Feature;

use App\Models\CalendarioEvento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CalendarioAvvisiTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_priorita_colorate_e_scadenze_in_tutte_le_viste_senza_nuovo_schema(): void
    {
        Carbon::setTestNow('2026-11-03 12:00:00');
        try {
            $structure = $this->structureFor($this->ownerFor($this->actor('admin')));
            $this->actingAs($this->actor('struttura_user', $structure->proprietario_id, $structure->id))
                ->withSession(['struttura_corrente_id' => $structure->id]);
            foreach (['urgente' => 'danger', 'alta' => 'warning', 'normale' => 'success'] as $priority => $color) {
                $this->post('/calendario', ['tipo' => 'nota', 'titolo' => 'Avviso '.$priority, 'contesto' => 'struttura', 'data_evento' => '2026-11-02', 'priorita' => $priority, 'stato' => 'da_fare'])->assertSessionHasNoErrors()->assertRedirect();
            }
            $this->post('/calendario', ['tipo' => 'scadenza', 'titolo' => 'Scadenza sintetica', 'contesto' => 'struttura', 'data_evento' => '2026-11-02', 'priorita' => 'normale', 'stato' => 'da_fare'])->assertSessionHasNoErrors()->assertRedirect();
            $deadline = CalendarioEvento::withoutGlobalScopes()->where('tipo', 'scadenza')->sole();
            foreach (['month', 'week', 'agenda'] as $view) {
                $this->get('/calendario?mese=2026-11&giorno=2026-11-02&vista='.$view)->assertOk()
                    ->assertViewHas('periodEvents', function ($events) {
                        return $events->firstWhere('titolo', 'Avviso urgente')['alert_color'] === 'danger'
                            && $events->firstWhere('titolo', 'Avviso alta')['alert_color'] === 'warning'
                            && $events->firstWhere('titolo', 'Avviso normale')['alert_color'] === 'success'
                            && $events->firstWhere('titolo', 'Scadenza sintetica')['alert_label'] === 'Scaduta';
                    })->assertSee('data-calendar-alert="danger"', false)->assertSee('data-calendar-alert="warning"', false)->assertSee('data-calendar-alert="success"', false);
            }
            $this->get('/calendario?mese=2026-11&giorno=2026-11-02&categoria=attivita')
                ->assertOk()->assertViewHas('periodEvents', fn ($events) => $events->contains('titolo', 'Scadenza sintetica'));
            $this->post('/calendario/'.$deadline->id.'/stato', ['stato' => 'completata'])->assertSessionHasNoErrors();
            $this->get('/calendario?mese=2026-11&giorno=2026-11-02&vista=day')->assertOk()
                ->assertViewHas('periodEvents', fn ($events) => $events->firstWhere('titolo', 'Scadenza sintetica')['alert_color'] === 'success');
            $this->get('/calendario?mese=2026-11&giorno=2026-11-02&vista=day')->assertOk()->assertSee('Rosso · Urgente')->assertSee('Scadenza · promemoria');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_scadenza_del_servizio_automatica_visibile_e_scaduta_in_rosso(): void
    {
        Carbon::setTestNow('2026-11-02 12:00:00');
        try {
            $admin = $this->actor('admin');
            $structure = $this->structureFor($this->ownerFor($admin));
            $structure->update(['scadenza_servizio' => '2026-11-01']);
            $this->actingAs($admin)->withSession(['struttura_corrente_id' => $structure->id])
                ->get('/calendario?mese=2026-11&giorno=2026-11-01&vista=day')->assertOk()
                ->assertViewHas('periodEvents', fn ($events) => $events->contains(fn ($event) => $event['tipo'] === 'scadenza_servizio' && $event['alert_color'] === 'danger' && $event['alert_label'] === 'Scaduta'));
        } finally {
            Carbon::setTestNow();
        }
    }
}
