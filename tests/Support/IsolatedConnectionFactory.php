<?php

namespace Tests\Support;

use Illuminate\Database\Connectors\ConnectionFactory;

final class IsolatedConnectionFactory extends ConnectionFactory
{
    public function __construct($container, private array $expected, private array $identity)
    {
        parent::__construct($container);
    }

    public function make(array $config, $name = null)
    {
        if ($name !== 'mysql' || isset($config['read']) || isset($config['write']) || isset($config['url'])) {
            TestingEnvironment::fail('connessione secondaria, URL o read/write rifiutata');
        }
        foreach ($this->expected as $key => $value) {
            if (!array_key_exists($key, $config) || $config[$key] !== $value) {
                TestingEnvironment::fail('configurazione connessione alterata: '.$key);
            }
        }
        if (array_diff(array_keys($config), [...array_keys($this->expected), 'name', 'prefix_indexes'])) {
            TestingEnvironment::fail('opzioni connessione aggiuntive rifiutate');
        }
        return parent::make($config, $name);
    }

    public function createConnector(array $config)
    {
        $identity = $this->identity;
        return new class($identity) {
            public function __construct(private array $identity) {}
            public function connect(array $config)
            {
                TestingEnvironment::requireIsolatedRuntime();
                return TestingEnvironment::verifiedPdo($this->identity);
            }
        };
    }
}
