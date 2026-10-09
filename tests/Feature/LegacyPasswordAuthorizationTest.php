<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LegacyPasswordAuthorizationTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public static function ruoli(): array
    {
        return [['struttura_user', 'reception', false], ['struttura_user', null, false], ['struttura_user', 'proprietario', true], ['proprietario', null, true], ['admin', null, true], ['super_admin', null, true]];
    }

    #[DataProvider('ruoli')]
    public function test_reset_legacy_applica_privilegi_e_preserva_operazioni_legittime(string $role, ?string $operativo, bool $allowed): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $u = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $role === 'struttura_user' ? $s->id : null);
        $u->update(['ruolo_operativo' => $operativo]);
        $target = $this->actor('struttura_user', null, $s->id);
        $target->update(['ruolo_operativo' => 'proprietario']);
        $before = $target->password;
        $response = $this->actingAs($u)->withSession(['struttura_corrente_id' => $s->id])->post('/strutture/utenti/'.$target->id.'/reset', ['password' => 'Password-audit-123!']);
        if ($allowed) {
            $response->assertSessionHasNoErrors()->assertRedirect();
            $this->assertTrue(Hash::check('Password-audit-123!', $target->fresh()->password));
        } else {
            $response->assertForbidden();
            $this->assertSame($before, $target->fresh()->password);
        }
    }
}
