<?php

namespace Tests\Unit;

use App\Models\Schedina;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

/** Rendering dei componenti reali, senza bootstrap applicativo, .env, kernel o database. */
class ComponentiImportFormTest extends TestCase
{
    private Application $container;
    private string $compiled;
    private Store $session;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);
        $this->compiled = sys_get_temp_dir() . '/import-blade-' . bin2hex(random_bytes(6));
        mkdir($this->compiled);
        $this->container = new Application($root);
        $this->container->instance('config', new Repository([
            'app' => ['url' => 'https://schedinedinotifica.test', 'key' => 'test'],
            'view' => ['paths' => [$root . '/resources/views'], 'compiled' => $this->compiled],
        ]));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->container);
        $this->container->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->container->register(\Illuminate\View\ViewServiceProvider::class);
        $this->container['view']->share('errors', new \Illuminate\Support\ViewErrorBag());
        $provider = $this->container->register(\Laravel\Ui\UiServiceProvider::class);
        $provider->boot();
        $request = Request::create('https://schedinedinotifica.test/schedine/nuova');
        $this->session = new Store('unit', new ArraySessionHandler(120));
        $this->session->start();
        $request->setLaravelSession($this->session);
        $this->container->instance('request', $request);
        $this->container->instance('session', $this->session);
        $this->container->instance('session.store', $this->session);
        // Carica le route vere, senza eseguire controller o middleware.
        ob_start();
        require $root . '/routes/web.php';
        $emitted = ob_get_clean();
        $this->assertSame('', $emitted, 'Le route non devono emettere byte nei download.');
        $this->container['router']->getRoutes()->refreshNameLookups();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->compiled . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->compiled);
        \App\Support\Componenti\DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(null);
        \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
        \App\Support\StrutturaCorrente::resetMemory();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        \Illuminate\Container\Container::setInstance(null);
        parent::tearDown();
    }

    private function render(Schedina $schedina, array $extra = []): \DOMXPath
    {
        $data = ['schedina' => $schedina, 'componenti' => collect(), 'strutturaInfo' => null,
            'tassaConfig' => null, 'esenzioni' => collect(), 'tassaDettaglio' => [], 'geoEndpoints' => []];
        foreach (['titoli', 'tipiVia', 'tipiDocumento', 'nations', 'regions', 'provinces', 'ciudades'] as $key) {
            $data[$key] = collect();
        }
        $html = $this->container['view']->make('schedina.partials.form', array_merge($data, $extra))->render();
        $dom = new \DOMDocument();
        $before = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($before);
        return new \DOMXPath($dom);
    }

    public function test_nuova_con_old_input_33_genera_solo_url_nuova(): void
    {
        $this->session->flashInput(['id' => 33, 'schedina_id' => 33, 'schedina' => ['id' => 33]]);
        $schedina = new Schedina();
        $schedina->id = 33;
        $dom = $this->render($schedina, ['schedinaContext' => 'new', 'usePutMethod' => false]);
        $button = $dom->query('//button[@formaction]')->item(0);
        $this->assertSame('https://schedinedinotifica.test/schedine/nuova/componenti/import/prepara', $button->getAttribute('formaction'));
        $this->assertSame('POST', $button->getAttribute('formmethod'));
        $this->assertSame('schedina-form', $button->getAttribute('form'));
        $this->assertSame('https://schedinedinotifica.test/schedine', $dom->query('//form')->item(0)->getAttribute('action'));
        $this->assertSame(0, $dom->query('//*[@action or @formaction or @href][contains(@action,"/schedine/33") or contains(@formaction,"/schedine/33") or contains(@href,"/schedine/33")]')->length);
        $this->assertSame(0, $dom->query('//input[@name="_method"]')->length);
        foreach (['csv', 'txt', 'xlsx'] as $format) {
            $this->assertSame(1, $dom->query('//a[@download][@href="https://schedinedinotifica.test/schedine/nuova/componenti/import/modello/' . $format . '"]')->length);
        }
        $this->assertFalse($schedina->exists);
    }

    public function test_contesto_nuova_prevale_anche_su_model_persistito(): void
    {
        $schedina = new Schedina();
        $schedina->id = 33;
        $schedina->exists = true;
        $schedina->setRelation('camere', collect());
        $dom = $this->render($schedina, ['schedinaContext' => 'new', 'usePutMethod' => false]);
        $this->assertStringContainsString('/schedine/nuova/', $dom->query('//button[@formaction]')->item(0)->getAttribute('formaction'));
    }

    public function test_import_esistente_e_post_ma_salvataggio_resta_put(): void
    {
        $schedina = new Schedina();
        $schedina->id = 33;
        $schedina->exists = true;
        $schedina->setRelation('camere', collect());
        $dom = $this->render($schedina);
        $button = $dom->query('//button[@formaction]')->item(0);
        $this->assertSame('https://schedinedinotifica.test/schedine/33/componenti/import/prepara', $button->getAttribute('formaction'));
        $hidden = $dom->query('//input[@name="_method"]')->item(0);
        $this->assertSame('PUT', $hidden->getAttribute('value'));
        $this->assertTrue($button->hasAttribute('formnovalidate'));
        $this->assertTrue($button->hasAttribute('data-confirm-ignore'));
        // Serializzazione dei controlli riusciti nell'ordine DOM: il submitter segue l'hidden.
        parse_str(http_build_query(['_method' => $hidden->getAttribute('value')]) . '&' .
            http_build_query([$button->getAttribute('name') => $button->getAttribute('value')]), $payload);
        Request::enableHttpMethodParameterOverride();
        $request = Request::create($button->getAttribute('formaction'), 'POST', $payload);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('schedina.componenti.import.prepare', $this->container['router']->getRoutes()->match($request)->getName());
        $save = Request::create('/schedine/33', 'POST', ['_method' => 'PUT']);
        $this->assertSame('PUT', $save->getMethod());
        $this->assertSame('schedina.update', $this->container['router']->getRoutes()->match($save)->getName());
    }

    public function test_payload_precedente_riproduce_405(): void
    {
        Request::enableHttpMethodParameterOverride();
        $request = Request::create('/schedine/33/componenti/import/prepara', 'POST', ['_method' => 'PUT']);
        $this->assertSame('PUT', $request->getMethod());
        $this->expectException(\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class);
        $this->container['router']->getRoutes()->match($request);
    }

    public function test_salva_componente_e_presente_in_ogni_pannello_dettagli(): void
    {
        $schedina = new Schedina();
        $schedina->id = 33;
        $schedina->exists = true;
        $schedina->setRelation('camere', collect());

        $dom = $this->render($schedina, [
            'componenti' => collect([
                ['id' => 10, 'name' => 'Maria', 'surname' => 'Rossi', 'sex' => 'F', 'relationship' => 'MEMBRO GRUPPO', 'exent' => 'NO'],
            ]),
        ]);

        $buttons = $dom->query('//button[@type="submit" and contains(., "Salva componente") and @class="btn btn-success btn-sm save-componente-row"]');
        $this->assertSame(1, $buttons->length);
        $this->assertSame('1', $buttons->item(0)->getAttribute('data-save-component'));
        $this->assertSame('10', $buttons->item(0)->getAttribute('data-component-id'));
        $this->assertSame('0', $buttons->item(0)->getAttribute('data-component-index'));
    }
    private function prepareController(?\App\Services\ComponentiImportService $service = null): \App\Http\Controllers\ComponentiImportController
    {
        // Nessun PDO: sono ammesse esclusivamente letture della struttura sintetica.
        $connection = new class extends \Illuminate\Database\Connection {
            public function __construct() { parent::__construct(null, 'fixture'); }
            public function select($query, $bindings = [], $useReadPdo = true)
            {
                if (str_contains($query, 'from "struttura"')) {
                    return [(object) ['id' => 7, 'nome_struttura' => 'Struttura test']];
                }
                throw new \LogicException('Lettura DB inattesa: ' . $query);
            }
            protected function run($query, $bindings, \Closure $callback)
            {
                throw new \LogicException('Accesso DB vietato: ' . $query);
            }
        };
        $resolver = new \Illuminate\Database\ConnectionResolver(['fixture' => $connection]);
        $resolver->setDefaultConnection('fixture');
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
        $this->container->instance('auth', new class {
            public function id() { return 11; }
            public function user() { return (object) ['id' => 11, 'struttura_id' => 7]; }
        });
        $this->container['config']->set('cache', ['default' => 'array', 'stores' => ['array' => ['driver' => 'array']]]);
        $this->container->register(\Illuminate\Cache\CacheServiceProvider::class);
        $this->container->register(\Illuminate\Translation\TranslationServiceProvider::class);
        $this->container->register(\Illuminate\Validation\ValidationServiceProvider::class);
        return new \App\Http\Controllers\ComponentiImportController($service ?? new \App\Services\ComponentiImportService());
    }

    public function test_prepare_nuova_scartando_id_stale_non_redirige_a_33(): void
    {
        $controller = $this->prepareController();
        $request = Request::create('/schedine/nuova/componenti/import/prepara', 'POST', [
            'id' => 33, 'schedina_id' => 33, 'schedina' => ['id' => 33], 'name' => 'Prova', '_method' => 'POST',
        ]);
        $request->setLaravelSession($this->session);
        $response = $controller->newPrepare($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('https://schedinedinotifica.test/schedine/nuova/componenti/import', $response->getTargetUrl());
        $this->assertSame(['name' => 'Prova'], $this->session->get('componenti_import_new_schedina.11.7'));
    }

    /** @dataProvider formati */
    public function test_download_nuova_restituisce_file_e_header(string $format, string $type): void
    {
        $controller = $this->prepareController();
        $response = $controller->newTemplate($format);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($type, $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=modello_componenti.' . $format, $response->headers->get('Content-Disposition'));
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith($format === 'xlsx' ? "PK" : ($format === 'csv' ? "\xEF\xBB\xBFNome" : 'Nome'), $content);
    }

    public static function formati(): array
    {
        return [
            ['csv', 'text/csv; charset=UTF-8'],
            ['txt', 'text/plain; charset=UTF-8'],
            ['xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];
    }

    public function test_preview_conferma_e_ritorno_al_form_senza_persistenza(): void
    {
        $service = new class extends \App\Services\ComponentiImportService {
            private function catalogo(): array
            {
                return [['codice' => '20', 'descrizione' => 'MEMBRO GRUPPO']];
            }
            public function previewDaContenuto(string $contenuto, string $formato, ?callable $tipoAlloggiatoResolver = null): array
            {
                return parent::previewDaContenuto($contenuto, $formato, fn () => $this->catalogo());
            }
            public function preparaConfermaBatch(array $batch, int $schedinaId, int $strutturaId, int $userId, ?callable $tipoAlloggiatoResolver = null): array
            {
                return parent::preparaConfermaBatch($batch, $schedinaId, $strutturaId, $userId, fn () => $this->catalogo());
            }
        };
        \App\Support\Componenti\DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(
            fn ($value) => $value ? ['id' => 2, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false] : null
        );
        $controller = $this->prepareController($service);
        $this->session->put('componenti_import_new_schedina.11.7', ['name' => 'Capo', 'componenti' => []]);
        $file = tempnam(sys_get_temp_dir(), 'import-test-');
        $stream = fopen($file, 'w');
        fputcsv($stream, $service->headersTemplate(), ';');
        fputcsv($stream, ['Prova', 'Importazione', 'F', 'Francese', 'Francia', '01/01/1990', '', '', '', '', '', '', '', '', '', '', '', ''], ';');
        fclose($stream);
        try {
            $request = ValidatedImportRequest::create('/schedine/nuova/componenti/import', 'POST', [], [], [
                'file_import' => new \Illuminate\Http\UploadedFile($file, 'componenti.csv', 'text/csv', null, true),
            ]);
            $request->setLaravelSession($this->session);
            $preview = $controller->newPreview($request)->getData();
            $this->assertNull($preview['schedina']);
            $this->assertSame(1, $preview['preview']['righe_valide']);
            $this->assertSame([], $this->session->get('componenti_import_new_schedina.11.7.componenti'));
            $token = $preview['preview']['batch_token'];
            $this->assertNull($this->session->get('componenti_import_batches.' . $token . '.schedina_id'));
            $confirm = ValidatedImportRequest::create('/schedine/nuova/componenti/import/conferma', 'POST', ['import_batch_token' => $token]);
            $confirm->setLaravelSession($this->session);
            $response = $controller->newConfirm($confirm);
            $this->assertSame('https://schedinedinotifica.test/schedine/nuova?active_tab=schedina-step-comp', $response->getTargetUrl());
            $rows = $this->session->get('componenti_import_new_schedina.11.7.componenti');
            $this->assertCount(1, $rows);
            $this->assertSame('Prova', $rows[0]['name']);
            $this->assertNull($rows[0]['schedina_id']);
            $this->assertSame('Capo', $this->session->get('componenti_import_new_schedina.11.7.name'));
            $this->assertSame($rows, $this->session->getOldInput('componenti'));
            $dom = $this->render(new Schedina(), ['schedinaContext' => 'new', 'usePutMethod' => false]);
            $this->assertSame(1, $dom->query('//input[@name="componenti[0][name]"][@value="Prova"]')->length);
            $controller->newConfirm($confirm);
            $this->assertCount(1, $this->session->get('componenti_import_new_schedina.11.7.componenti'));
        } finally {
            unlink($file);
        }
    }

    public function test_salvataggio_componenti_nuova_schedina_senza_persistenza(): void
    {
        \App\Support\StrutturaCorrente::setId(7);
        $this->container->instance('auth', new class {
            public function id(): int { return 11; }
            public function user(): object { return (object) ['id' => 11, 'struttura_id' => 7]; }
        });

        $controller = new \App\Http\Controllers\SchedinaController(new \App\Services\TassaDiSoggiornoService());
        $request = Request::create('/schedine', 'POST', [
            'save_mode' => 'componenti',
            'name' => 'Capo',
            'surname' => 'Schedina',
            'cant_people' => 2,
            'componenti' => [[
                'name' => 'Maria',
                'surname' => 'Rossi',
                'sex' => 'F',
                'relationship' => 'MEMBRO GRUPPO',
                'exent' => 'NO',
                'city_nac' => 'Francese',
                'country_nac' => 'Francia',
                'date_nac' => '1990-01-01',
            ]],
        ]);
        $request->setLaravelSession($this->session);

        $response = $controller->store($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('https://schedinedinotifica.test/schedine/nuova?active_tab=schedina-step-comp', $response->getTargetUrl());
        $draft = $this->session->get('componenti_import_new_schedina.11.7');
        $this->assertSame('Capo', $draft['name']);
        $this->assertSame('Maria', $draft['componenti'][0]['name']);
    }

}


class ValidatedImportRequest extends Request
{
    public function validate(array $rules, array $messages = []): array
    {
        return app('validator')->make($this->all(), $rules, $messages)->validate();
    }
}
