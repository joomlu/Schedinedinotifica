<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LegacyUserCreationSecurityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function payload(): array
    {
        return ['name' => 'Utente legacy sintetico', 'email' => 'legacy-new@example.invalid', 'password' => 'Password-audit-123!'];
    }

    public static function ruoli(): array
    {
        return [['struttura_user', 'reception', false], ['struttura_user', null, false], ['struttura_user', 'proprietario', true], ['proprietario', null, true], ['admin', null, true], ['super_admin', null, true]];
    }

    #[DataProvider('ruoli')]
    public function test_creazione_e_form_applicano_privilegi_senza_escalation(string $role, ?string $operativo, bool $allowed): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $foreign = $this->structureFor(null);
        $u = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $role === 'struttura_user' ? $s->id : null);
        $u->update(['ruolo_operativo' => $operativo]);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id]);
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        $attempts = 0;
        $event = 'eloquent.creating: '.User::class;
        Event::listen($event, function () use (&$attempts) {
            $attempts++;
        });
        try {
            $form = $this->get('/strutture/utenti/create');
            $r = $this->post('/strutture/utenti', $this->payload() + ['avatar' => 'NON-USARE', 'ruolo' => 'super_admin', 'ruolo_operativo' => 'proprietario', 'struttura_id' => $foreign->id, 'attivo' => false]);
            if (! $allowed) {
                $form->assertForbidden();
                $r->assertForbidden();
                $this->assertSame(0, $attempts, 'Il rifiuto deve precedere il tentativo di inserimento');
                $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());

                return;
            }
            $form->assertOk();
            $r->assertSessionHasNoErrors()->assertRedirect(route('strutture.utenti.index'));
            $new = User::where('email', $this->payload()['email'])->sole();
            $this->assertSame('struttura_user', $new->ruolo);
            $this->assertNull($new->ruolo_operativo);
            $this->assertSame((int) $s->id, (int) $new->struttura_id);
            $this->assertSame('', $new->avatar);
            $this->assertTrue((bool) $new->attivo);
            $this->assertTrue(Hash::check($this->payload()['password'], $new->password));
            $this->assertSame(1, $attempts);
            $this->get('/logout');
            $this->post('/login', ['login' => $new->email, 'password' => $this->payload()['password']])->assertSessionHasNoErrors()->assertRedirect();
            $this->get('/gestione-operativa')->assertOk();
        } finally {
            Event::forget($event);
        }
    }

    public static function dati_invalidi(): array
    {
        return [['name', ''], ['email', 'email-invalida'], ['password', 'breve'], ['email', 'duplicata']];
    }

    #[DataProvider('dati_invalidi')]
    public function test_dati_invalidi_non_creano_utenti(string $field, string $value): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($u);
        $data = $this->payload();
        $data[$field] = $value === 'duplicata' ? $u->email : $value;
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        $this->postJson('/strutture/utenti', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    public function test_struttura_estranea_e_csrf_errato_non_creano_utenti(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $otherOwner = $this->ownerFor($this->actor('admin'));
        $foreign = $this->structureFor($otherOwner);
        $u = $this->actor('proprietario', $owner->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id' => $foreign->id]);
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        $this->get('/strutture/utenti/create')->assertForbidden();
        $this->post('/strutture/utenti', $this->payload())->assertForbidden();
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
        $s = $this->structureFor($owner);
        $this->withSession(['struttura_corrente_id' => $s->id]);
        $this->post('/strutture/utenti', $this->payload() + ['_token' => 'CSRF-INVALIDO'])->assertStatus(419);
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }

    public function test_viste_legacy_rendono_con_layout_disponibile(): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($u);
        $this->get('/strutture/utenti/create')->assertOk()->assertSee('Nuovo utente per');
        $this->get('/strutture/utenti')->assertOk()->assertSee('Utenti struttura:');
    }
}
