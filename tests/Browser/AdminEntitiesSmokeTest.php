<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminEntitiesSmokeTest extends DuskTestCase
{
    /**
     * Basic smoke test for admin entities pages: ensure page loads, "Nuovo" button is visible,
     * opening the modal shows a localized Close button ("Chiudi").
     */
    public function test_admin_pages_have_new_button_and_modal_close()
    {
        $routes = [
            '/groups',
            '/subgroups',
            '/subgroups1',
            '/typedoc',
            '/typestreet',
            '/titles',
            '/released',
        ];

        $this->browse(function (Browser $browser) use ($routes) {
            foreach ($routes as $route) {
                $browser->visit($route)
                    ->assertSee('Nuovo')
                    // open modal via the "Nuovo" button which targets #myModal on these pages
                    ->click('a[data-bs-target="#myModal"]')
                    ->whenAvailable('#myModal', function (Browser $modal) {
                        $modal->assertSee('Chiudi')
                            ->click('#myModal .modal-footer .btn-light');
                    });
            }
        });
    }
}
