<?php

namespace Tests\Feature;

use App\Models\Proprietario;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProprietarioAccessoValidationTest extends TestCase
{
    use DatabaseTransactions;

    public static function aree(): array
    {
        return [['admin', 'admin'], ['superadmin', 'super_admin']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('aree')]
    public function test_validazione_persistenza_e_messaggi_accesso(string $area, string $ruolo): void
    {
        $admin = User::factory()->create(['ruolo' => $ruolo, 'attivo' => true]);
        $this->actingAs($admin);
        $url = '/'.$area.'/proprietari';
        $form = $url.'/create';
        $numero = Proprietario::count();
        $this->from($form)->post($url, ['nome' => ''])->assertRedirect($form)
            ->assertSessionHasErrors(['nome' => 'Il campo nome è obbligatorio.']);
        $this->assertSame($numero, Proprietario::count());
        $this->get($form)->assertOk()->assertSee('Il campo nome è obbligatorio.');

        $payload = ['nome' => 'Proprietario sintetico '.$area, 'accesso_username' => '', 'accesso_password' => '',
            'accesso_nome' => 'Nome accesso sintetico', 'accesso_email' => 'accesso-'.$area.'@fixture.invalid'];
        $this->post($url, $payload)->assertSessionHasNoErrors()->assertSessionHas('status',
            'Proprietario salvato senza accesso al portale. Per configurarlo, inserisci username e password.');
        $owner = Proprietario::where('nome', $payload['nome'])->firstOrFail();
        $this->assertSame(0, User::where('proprietario_id', $owner->id)->count());

        foreach ([['accesso_username' => 'nuovo-'.$area, 'accesso_password' => '', 'campo' => 'accesso_password',
            'messaggio' => 'Inserisci la password per creare l’accesso del proprietario.'],
            ['accesso_username' => '', 'accesso_password' => 'Password-sintetica-123!', 'campo' => 'accesso_username',
            'messaggio' => 'Inserisci lo username di accesso del proprietario.']] as $caso) {
            $prima = Proprietario::count();
            $this->from($form)->post($url, array_replace($payload, array_intersect_key($caso, $payload)))
                ->assertRedirect($form)->assertSessionHasErrors([$caso['campo'] => $caso['messaggio']]);
            $this->assertSame($prima, Proprietario::count());
            $html = $this->get($form)->assertOk()->assertSee($caso['messaggio'])->getContent();
            $this->assertMatchesRegularExpression('/name="'.$caso['campo'].'"[^>]*is-invalid[^>]*>\s*<div class="invalid-feedback d-block">/', $html);
            $this->assertStringContainsString('id="proprietario-pane-accesso"', $html);
        }

        $credenziali = array_replace($payload, ['nome' => 'Con accesso '.$area,
            'accesso_username' => 'nuovo-'.$area, 'accesso_password' => 'Password-sintetica-123!']);
        $this->post($url, $credenziali)->assertSessionHasNoErrors();
        $ownerAccesso = Proprietario::where('nome', $credenziali['nome'])->firstOrFail();
        $accesso = User::where('proprietario_id', $ownerAccesso->id)->firstOrFail();
        $this->assertTrue(Hash::check($credenziali['accesso_password'], $accesso->password));
        $this->assertSame($credenziali['accesso_username'], $accesso->username);
        $prima = Proprietario::count();
        $this->post($url, array_replace($credenziali, ['nome' => 'Duplicato sintetico']))
            ->assertSessionHasErrors(['accesso_username' => 'Lo username di accesso è già utilizzato da un altro utente.']);
        $this->assertSame($prima, Proprietario::count());

        $hash = $accesso->password;
        $this->put($url.'/'.$ownerAccesso->id, array_replace($credenziali,
            ['nome' => 'Aggiornato '.$area, 'accesso_password' => '']))->assertSessionHasNoErrors();
        $this->assertSame('Aggiornato '.$area, $ownerAccesso->fresh()->nome);
        $this->assertSame($hash, $accesso->fresh()->password);
        $this->assertSame($credenziali['accesso_username'], $accesso->fresh()->username);
        $this->assertTrue((bool) $accesso->fresh()->attivo);
        $this->put($url.'/'.$ownerAccesso->id, ['nome' => 'Credenziali conservate', 'accesso_username' => '', 'accesso_password' => ''])
            ->assertSessionHasNoErrors();
        $this->assertSame($hash, $accesso->fresh()->password);
        $this->assertSame($credenziali['accesso_username'], $accesso->fresh()->username);
        $this->assertTrue((bool) $accesso->fresh()->attivo);
        $this->put($url.'/'.$owner->id, ['nome' => 'Senza accesso aggiornato', 'accesso_username' => '', 'accesso_password' => ''])
            ->assertSessionHasNoErrors()->assertSessionHas('status',
                'Proprietario salvato senza accesso al portale. Per configurarlo, inserisci username e password.');
        $this->assertSame(0, User::where('proprietario_id', $owner->id)->count());
    }

    public function test_admin_non_modifica_proprietario_altrui(): void
    {
        $admin = User::factory()->create(['ruolo' => 'admin', 'attivo' => true]);
        $altro = User::factory()->create(['ruolo' => 'admin', 'attivo' => true]);
        $owner = Proprietario::create(['nome' => 'Altro proprietario sintetico', 'admin_id' => $altro->id, 'attivo' => true]);
        $this->actingAs($admin)->put('/admin/proprietari/'.$owner->id,
            ['nome' => 'Non autorizzato', 'accesso_username' => 'intruso', 'accesso_password' => 'Password-sintetica-123!'])->assertNotFound();
        $this->assertSame('Altro proprietario sintetico', $owner->fresh()->nome);
        $this->assertSame(0, User::where('proprietario_id', $owner->id)->count());
    }
}
