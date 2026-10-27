<?php

// Caricato esclusivamente dal router di collaudo attestato, mai dal front controller pubblico.
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
$app->booted(function () use ($app) {
    config(['questura.enabled' => true]);
    $app->bind(\App\Services\QuesturaWebService::class, function () {
        require_once __DIR__.'/../Feature/QuesturaWsContractTest.php';

        $double = new class extends \Tests\Feature\QuesturaSoapDouble
        {
            public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null): mixed
            {
                if (! in_array($name, ['Test', 'Send'], true)) {
                    return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
                }
                $this->calls[] = [$name, $args[0]];
                $count = count($args[0]['ElencoSchedine']['string']);

                return (object) [$name.'Result' => (object) ['esito' => true], 'result' => (object) ['SchedineValide' => $count, 'Dettaglio' => (object) ['EsitoOperazioneServizio' => array_fill(0, $count, (object) ['esito' => true])]]];
            }
        };

        return new class($double) extends \App\Services\QuesturaWebService
        {
            public function __construct(private \SoapClient $double) {}

            protected function isSimulation(\App\Models\Struttura $s): bool
            {
                return false;
            }

            protected function makeClient(): \SoapClient
            {
                return $this->double;
            }
        };
    });
});
