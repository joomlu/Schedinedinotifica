<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private ?string $testStorageRoot = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testStorageRoot = \Tests\Support\IsolatedTestStorage::create($this->app);
    }

    public function be(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        parent::be($user, $guard);
        // L'identità impostata dal test equivale a un nuovo login esplicito.
        // Le richieste successive attraversano tutti i controlli di sessione.
        $this->withSession([
            'password_hash_'.($guard ?? $this->app['auth']->getDefaultDriver()) => $user->getAuthPassword(),
        ]);

        return $this;
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        // Il kernel HTTP in-process deve avere lo stesso contesto web del server.
        // I comandi Artisan restano in contesto console fuori dalla richiesta.
        $context = new \ReflectionProperty(\Illuminate\Foundation\Application::class, 'isRunningInConsole');
        $previous = $context->getValue($this->app);
        $context->setValue($this->app, false);
        try {
            $this->startSession();
            $token = $this->app['session']->token();
            $cookies = array_replace([$this->app['config']['session.cookie'] => $this->app['session']->getId()], $cookies);
            // Aggiunge un token valido alle richieste sintetiche, senza escludere
            // il middleware CSRF. I token espliciti (anche errati) sono preservati.
            if (! in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'], true)
                && ! isset($parameters['_token']) && ! isset($server['HTTP_X_CSRF_TOKEN'])
                && ! isset($this->serverVariables['HTTP_X_CSRF_TOKEN'])) {
                $server['HTTP_X_CSRF_TOKEN'] = $token;
            }

            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } finally {
            $context->setValue($this->app, $previous);
        }
    }

    protected function tearDown(): void
    {
        \App\Support\StrutturaCorrente::resetMemory();
        try {
            parent::tearDown();
        } finally {
            if ($this->testStorageRoot !== null) {
                \Tests\Support\IsolatedTestStorage::remove($this->testStorageRoot);
                $this->testStorageRoot = null;
            }
        }
    }
}
