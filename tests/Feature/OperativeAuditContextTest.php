<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireAuthorizedStruttura;
use App\Models\StrutturaAuditLog;
use App\Http\Middleware\LogOperativeAudit;
use Illuminate\Http\Request;
use App\Support\StrutturaCorrente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class OperativeAuditContextTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['web', 'auth', RequireAuthorizedStruttura::class])
            ->match(['GET', 'POST'], '/audit-fixture', function () {
                return response('Operazione sintetica', request()->boolean('denied') ? 403 : 200);
            })->name('customer.audit_fixture');
    }

    public static function ruoli(): array
    {
        return [['super_admin'], ['admin'], ['proprietario'], ['struttura_user']];
    }

    #[DataProvider('ruoli')]
    public function test_registra_il_tenant_autorizzato_per_ogni_ruolo(string $role): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $other = $this->structureFor($owner);
        $u = $role === 'admin' ? $admin : $this->actor($role,
            in_array($role, ['proprietario', 'struttura_user'], true) ? $owner->id : null,
            $role === 'struttura_user' ? $s->id : null);

        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id])
            ->post('/audit-fixture', ['struttura_id' => $other->id])->assertOk();

        $log = StrutturaAuditLog::withoutGlobalScopes()->sole();
        $this->assertSame($s->id, (int) $log->struttura_id);
        $this->assertSame($u->id, (int) $log->user_id);
        $this->assertSame('customer.audit_fixture', $log->route_name);
        $this->assertSame('POST', $log->metodo);
    }

    public function test_id_estraneo_non_autorizza_scritture_ne_crea_log(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $other = $this->structureFor($this->ownerFor($this->actor('admin')));
        $u = $this->actor('proprietario', $owner->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id])
            ->post('/audit-fixture?struttura_id='.$other->id)->assertForbidden();
        $this->assertSame(0, StrutturaAuditLog::withoutGlobalScopes()->count());
    }

    public function test_letture_e_rifiuti_non_diventano_operazioni_riuscite(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id]);
        // Invocazione diretta per il solo metodo GET: il catch-all applicativo
        // precede le route GET aggiunte dinamicamente dalla fixture.
        $request = Request::create('/audit-fixture', 'GET');
        $request->setUserResolver(fn () => $u);
        $response = app(LogOperativeAudit::class)->handle($request, fn () => response('Lettura sintetica'));
        $this->assertSame(200, $response->getStatusCode());
        $this->post('/audit-fixture', ['denied' => true])->assertForbidden();
        $this->assertSame(0, StrutturaAuditLog::withoutGlobalScopes()->count());
    }

    public function test_il_contesto_autorizzato_prevale_sulla_struttura_fissa(): void
    {
        $fixed = $this->structureFor(null);
        $selected = $this->structureFor(null);
        $u = $this->actor('super_admin', null, $fixed->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $selected->id])
            ->post('/audit-fixture')->assertOk();
        $this->assertSame($selected->id, (int) StrutturaAuditLog::withoutGlobalScopes()->sole()->struttura_id);
    }

    public function test_senza_attributo_usa_solo_il_contesto_appartenente_al_proprietario(): void
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $foreign = $this->structureFor(null);
        $u = $this->actor('proprietario', $owner->id);
        $request = Request::create('/audit-fixture', 'POST');
        $request->setUserResolver(fn () => $u);
        $route = new \Illuminate\Routing\Route('POST', 'audit-fixture', fn () => null);
        $route->name('customer.audit_fixture');
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);
        StrutturaCorrente::setId($s->id);
        app(LogOperativeAudit::class)->handle($request, fn () => response('Operazione sintetica'));
        $this->assertSame($s->id, (int) StrutturaAuditLog::withoutGlobalScopes()->sole()->struttura_id);
        StrutturaCorrente::setId($foreign->id);
        app(LogOperativeAudit::class)->handle($request, fn () => response('Operazione sintetica'));
        $this->assertSame(1, StrutturaAuditLog::withoutGlobalScopes()->count());
    }
}
