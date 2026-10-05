<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

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
            if (!in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'], true)
                && !isset($parameters['_token']) && !isset($server['HTTP_X_CSRF_TOKEN'])
                && !isset($this->serverVariables['HTTP_X_CSRF_TOKEN'])) {
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
        parent::tearDown();
    }
}
