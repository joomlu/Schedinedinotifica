<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

class GeoSelectTest extends DuskTestCase
{
    #[Group('geo')]
    public function test_city_autocompletes_province_and_region(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/Nac_Reg_Prov_Citt')
                ->waitFor('#nation')
                ->waitUntil('window.__GEO_READY__ === true', 10)
                // Attendi che jQuery+Select2 siano effettivamente caricati (fallback locale)
                ->waitUntil('!!(window.jQuery && jQuery.fn && jQuery.fn.select2)', 5)
                // Se le librerie CDN (jQuery/Select2) non sono caricate, salta il test (ambiente offline)
                ->script(['window.__HAS_JQ__ = !!window.jQuery; window.__HAS_S2__ = !!(window.jQuery && jQuery.fn && jQuery.fn.select2);']);

            $has = $browser->script('return [window.__HAS_JQ__, window.__HAS_S2__];');
            if (empty($has) || count($has) < 2 || ! $has[0] || ! $has[1]) {
                $this->markTestSkipped('jQuery/Select2 non disponibili (ambiente offline/CDN bloccato).');

                return;
            }
            // Verifica preselect Italia
            $browser->assertScript("document.querySelector('#nation').value === 'IT'");
            // Apri Select2 città e cerca 'Roma'
            $browser->script(["$('#city').select2('open')"]);

            $browser->whenAvailable('.select2-container--open .select2-search__field', function (Browser $input) {
                $input->type('', 'Roma');
            })
                ->waitFor('.select2-results__option', 10)
                ->keys('.select2-container--open .select2-search__field', '{enter}')
                // Attendi che provincia e regione si valorizzino (senza reload)
                ->waitUntil("$('#province option:selected').text().length > 0", 10)
                ->waitUntil("$('#region option:selected').text().length > 0", 10)
                // Controlli base: summary contiene Roma, provincia/regione non vuote
                ->assertSeeIn('#geo-summary', 'Città:')
                ->assertSeeIn('#geo-summary', 'Roma')
                // La label della città deve contenere solo il nome (senza provincia o CAP)
                ->assertScript("!/\\(|—/.test($('#city option:selected').text())")
                ->assertScript("$('#province').val() !== null && $('#province option:selected').text().length > 0")
                ->assertScript("$('#region').val() !== null && $('#region option:selected').text().length > 0");
        });
    }

    #[Group('geo')]
    public function test_cap_sets_city_and_province(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/Nac_Reg_Prov_Citt')
                ->waitFor('#cap')
                ->waitUntil('window.__GEO_READY__ === true', 10)
                ->waitUntil('!!(window.jQuery && jQuery.fn && jQuery.fn.select2)', 5)
                ->script(['window.__HAS_JQ__ = !!window.jQuery; window.__HAS_S2__ = !!(window.jQuery && jQuery.fn && jQuery.fn.select2);']);

            $has = $browser->script('return [window.__HAS_JQ__, window.__HAS_S2__];');
            if (empty($has) || count($has) < 2 || ! $has[0] || ! $has[1]) {
                $this->markTestSkipped('jQuery/Select2 non disponibili (ambiente offline/CDN bloccato).');

                return;
            }
            // Apri Select2 CAP e cerca per Comune (es. 'Roma')
            $browser->script(["$('#cap').select2('open')"]);

            $browser->whenAvailable('.select2-container--open .select2-search__field', function (Browser $input) {
                $input->type('', 'Roma');
            })
                ->waitFor('.select2-results__option', 10)
                ->keys('.select2-container--open .select2-search__field', '{enter}')
                // Attendi propagazione su città e provincia
                ->waitUntil("$('#city option:selected').text().length > 0", 10)
                ->waitUntil("$('#province option:selected').text().length > 0", 10)
                ->assertScript("$('#city').val() !== null && $('#city option:selected').text().length > 0")
                // La label della città deve contenere solo il nome (senza provincia o CAP)
                ->assertScript("!/\\(|—/.test($('#city option:selected').text())")
                ->assertScript("$('#province').val() !== null && $('#province option:selected').text().length > 0");
        });
    }

    #[Group('geo')]
    public function test_manual_mode_for_non_italy(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/Nac_Reg_Prov_Citt')
                ->waitFor('#nation')
                ->waitUntil('window.__GEO_READY__ === true', 10)
                ->waitUntil('!!(window.jQuery && jQuery.fn && jQuery.fn.select2)', 5)
                ->script(['window.__HAS_JQ__ = !!window.jQuery; window.__HAS_S2__ = !!(window.jQuery && jQuery.fn && jQuery.fn.select2);']);

            $has = $browser->script('return [window.__HAS_JQ__, window.__HAS_S2__];');
            if (empty($has) || count($has) < 2 || ! $has[0] || ! $has[1]) {
                $this->markTestSkipped('jQuery/Select2 non disponibili (ambiente offline/CDN bloccato).');

                return;
            }
            $browser->script(["$('#nation').select2('open')"]);

            $browser->whenAvailable('.select2-container--open .select2-search__field', function (Browser $input) {
                $input->type('', 'Fran'); // Francia
            })
                ->waitFor('.select2-results__option', 10)
                ->keys('.select2-container--open .select2-search__field', '{enter}')

            // Manual group visibili, select group nascosti
                ->waitUntil("document.querySelector('#region_manual') && document.querySelector('#region_manual').offsetParent !== null", 10)
                ->assertVisible('#region_manual')
                ->assertVisible('#province_manual')
                ->assertVisible('#city_manual')
                ->assertVisible('#cap_manual')
                ->assertScript("document.querySelectorAll('.select-group.d-none').length >= 4");

            // Summary indica modalità manuale
            $browser->assertSeeIn('#geo-summary', 'Modalità')
                ->assertSeeIn('#geo-summary', 'Inserimento manuale');
        });
    }
}
