<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ComponentiSmokeTest extends DuskTestCase
{
    public function test_componenti_actions_are_wired_with_confirmations()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/componenti')
                ->assertSee('Componenti');

            // If a delete button exists, trigger SweetAlert2 and then cancel
            $hasDelete = $browser->script('return !!document.querySelector("table a.btn.btn-danger[data-sa-confirm=\\"delete\\"]");');
            if (! empty($hasDelete) && $hasDelete[0]) {
                $browser->click('table a.btn.btn-danger[data-sa-confirm="delete"]')
                    ->waitFor('.swal2-popup')
                    ->assertSeeIn('.swal2-popup', 'Confermi eliminazione?')
                    ->click('.swal2-cancel');
            }

            // If an edit/view link exists, open it and try to submit; cancel on confirmation
            $hasEdit = $browser->script('return !!document.querySelector("table a.btn.btn-success[href^=\\"/editcomponenti\\"]");');
            if (! empty($hasEdit) && $hasEdit[0]) {
                $browser->click('table a.btn.btn-success[href^="/editcomponenti"]')
                    ->assertPresent('form[data-sa-confirm="update"]')
                    ->click('form[data-sa-confirm="update"] button[type="submit"]')
                    ->waitFor('.swal2-popup')
                    ->assertSeeIn('.swal2-popup', 'Confermi modifica?')
                    ->click('.swal2-cancel');
            }
        });
    }
}
