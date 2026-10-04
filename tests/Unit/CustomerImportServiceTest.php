<?php

namespace Tests\Unit;

use App\Models\Struttura;
use App\Services\CustomerImportService;
use PHPUnit\Framework\TestCase;

class CustomerImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Lookup vuoti in memoria: nessuna connessione al database di sviluppo.
        $connection = new class extends \Illuminate\Database\Connection {
            public function __construct() { parent::__construct(null); }
            public function select($query, $bindings = [], $useReadPdo = true) { return []; }
        };
        $resolver = new \Illuminate\Database\ConnectionResolver(['fixture' => $connection]);
        $resolver->setDefaultConnection('fixture');
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
    }

    protected function tearDown(): void
    {
        \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
        \Carbon\Carbon::setTestNow();
    }

    public function test_rows_with_only_name_and_surname_are_importable(): void
    {
        $service = new CustomerImportService();
        $payload = [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'tipo_cliente' => 'Componente',
            'sesso' => '',
            'email' => '',
            'telefono' => '',
            'cellulare' => '',
            'cap_residenza' => '',
            'comune_residenza' => '',
            'provincia_residenza' => '',
            'indirizzo_residenza' => '',
            'numero_civico_residenza' => '',
            'nazione_residenza' => '',
            'data_nascita' => '',
            'cittadinanza' => '',
            'nazione_nascita' => '',
            'comune_nascita' => '',
            'provincia_nascita' => '',
            'tipo_documento' => '',
            'numero_documento' => '',
            'data_rilascio' => '',
            'data_scadenza' => '',
            'rilasciato_da' => '',
            'nome_gruppo' => '',
            'gruppo' => '',
            'subgroup' => '',
            'subgroup1' => '',
            'note' => '',
        ];

        $status = $this->callEvaluatePayload($service, $payload);

        $this->assertSame(CustomerImportService::STATUS_VALID, $status);
    }

    public function test_rows_without_name_or_surname_are_marked_non_importabile(): void
    {
        $service = new CustomerImportService();

        $withoutName = $this->callEvaluatePayload($service, [
            'nome' => '',
            'cognome' => 'Rossi',
            'tipo_cliente' => 'Componente',
        ]);
        $withoutSurname = $this->callEvaluatePayload($service, [
            'nome' => 'Mario',
            'cognome' => '',
            'tipo_cliente' => 'Componente',
        ]);

        $this->assertSame(CustomerImportService::STATUS_NON_IMPORTABLE, $withoutName);
        $this->assertSame(CustomerImportService::STATUS_NON_IMPORTABLE, $withoutSurname);
    }

    public function test_dates_are_normalized_with_coherent_century_and_future_values_are_rejected(): void
    {
        $service = new CustomerImportService();

        $this->assertSame('1986-09-12', $this->callNormalizeDate($service, '12/09/86'));
        $this->assertSame('2005-06-15', $this->callNormalizeDate($service, '15/06/05'));
        $this->assertSame('', $this->callNormalizeDate($service, '00/00/0000'));
        $this->assertSame('', $this->callNormalizeDate($service, '31/12/2099'));
    }

    public function test_gioiella_legacy_headers_are_mapped_to_canonical_customer_fields(): void
    {
        $service = new CustomerImportService();
        $headers = [
            'Nro Clienti',
            'Tipo Alloggiato',
            'Nome',
            'Cognome',
            'Nazione-residenza',
            'Provincia-residenza',
            'Citta-residenza',
            'Cap',
            'Email',
            'Telefono',
            'Cellular',
            'Fax',
            'Tipo via Strada',
            'Nazione-Anagrafica',
            'Provincia-Anagrafica',
            'Citta-Anagrafica',
            'Data di nascita',
            'Doc. Tipo',
            'Doc. Num',
            'Rilasciato il ',
            'Scade il',
            'Rilasciato',
            'Gruppo',
        ];

        $normalized = $this->callNormalizeHeaders($service, $headers);

        $this->assertSame('numero_cliente', $normalized[0]);
        $this->assertSame('tipo_alloggiato', $normalized[1]);
        $this->assertSame('nome', $normalized[2]);
        $this->assertSame('cognome', $normalized[3]);
        $this->assertSame('nazione_residenza', $normalized[4]);
        $this->assertSame('provincia_residenza', $normalized[5]);
        $this->assertSame('comune_residenza', $normalized[6]);
        $this->assertSame('cap_residenza', $normalized[7]);
        $this->assertSame('email', $normalized[8]);
        $this->assertSame('telefono', $normalized[9]);
        $this->assertSame('cellulare', $normalized[10]);
        $this->assertSame('fax', $normalized[11]);
        $this->assertSame('tipo_via_strada', $normalized[12]);
        $this->assertSame('nazione_nascita', $normalized[13]);
        $this->assertSame('provincia_nascita', $normalized[14]);
        $this->assertSame('comune_nascita', $normalized[15]);
        $this->assertSame('data_nascita', $normalized[16]);
        $this->assertSame('tipo_documento', $normalized[17]);
        $this->assertSame('numero_documento', $normalized[18]);
        $this->assertSame('data_rilascio', $normalized[19]);
        $this->assertSame('data_scadenza', $normalized[20]);
        $this->assertSame('rilasciato_da', $normalized[21]);
        $this->assertSame('gruppo', $normalized[22]);
    }

    public function test_template_headers_include_real_customer_fields_from_gioiella(): void
    {
        $service = new CustomerImportService();
        $headers = $service->templateHeaders();

        $this->assertContains('numero_cliente', $headers);
        $this->assertContains('tipo_cliente', $headers);
        $this->assertContains('tipo_alloggiato', $headers);
        $this->assertContains('fax', $headers);
        $this->assertContains('tipo_via_strada', $headers);
        $this->assertContains('gruppo', $headers);
        $this->assertContains('note', $headers);
    }

    public function test_date_complete_secolo_scadenza_e_calendario(): void
    {
        \Carbon\Carbon::setTestNow('2026-10-04 12:00:00');
        $service = new CustomerImportService();
        $method = new \ReflectionMethod($service, 'normalizeDate');
        $this->assertSame('1926-12-31', $method->invoke($service, '31/12/26'));
        $this->assertSame('1986-09-12', $method->invoke($service, '1986-09-12'));
        $this->assertSame('', $method->invoke($service, '31/02/2020'));
        $this->assertSame('', $method->invoke($service, '0000-00-00'));
        $this->assertSame('', $method->invoke($service, '0086-09-12'));
        $this->assertSame('2031-01-01', $method->invoke($service, '2031-01-01', true));
        $this->assertSame('2031-01-01', $method->invoke($service, '01/01/31', true));
        $this->assertSame('', $method->invoke($service, '2031-01-01'));
    }

    public function test_header_bom_e_tipologie_distinte(): void
    {
        $service = new CustomerImportService();
        $this->assertSame(['numero_cliente', 'tipo_cliente', 'tipo_alloggiato'], $this->callNormalizeHeaders($service, ["\xEF\xBB\xBFNro Clienti", 'Tipo Cliente', 'Tipo Alloggiato']));
        $this->assertCount(count($service->templateHeaders()), $service->templateExampleRow());
    }

    public function test_schedina_trasferisce_correzioni_e_tipi_distinti(): void
    {
        $service = new CustomerImportService();
        $method = new \ReflectionMethod($service, 'importPayloadFromSchedina');
        foreach ([['Ospite', 'Capogruppo'], ['Componente', 'Ospite singolo'], ['Richiesta', 'Ospite singolo']] as [$crm, $alloggiato]) {
            $schedina = new \App\Models\Schedina([
                'customer_type_housed' => $crm, 'relationship' => $alloggiato, 'type' => 'Sig.',
                'name' => 'Nome corretto', 'surname' => 'Cognome corretto',
                'customer_email' => 'corretto@example.invalid',
                'or_address' => 'Indirizzo corretto', 'or_doc' => 'TEST123',
                'oa_date_nac' => '1986-09-12', 'or_expire' => '2031-01-01',
            ]);
            $payload = $method->invoke($service, $schedina);
            $this->assertSame($crm, $payload['tipo_cliente']);
            $this->assertSame($alloggiato, $payload['tipo_alloggiato']);
            $this->assertSame('Nome corretto', $payload['nome']);
            $this->assertSame('Cognome corretto', $payload['cognome']);
            $this->assertSame('corretto@example.invalid', $payload['email']);
            $this->assertSame('Indirizzo corretto', $payload['indirizzo_residenza']);
            $this->assertSame('TEST123', $payload['numero_documento']);
            $this->assertSame('1986-09-12', $payload['data_nascita']);
            $this->assertSame('2031-01-01', $payload['data_scadenza']);
            $this->assertNotContains('Sig.', $payload);
        }
    }

    private function callEvaluatePayload(CustomerImportService $service, array $payload): string
    {
        $method = new \ReflectionMethod($service, 'evaluatePayload');
        $structure = new Struttura();
        $structure->id = 1;
        $structure->proprietario_id = 1;

        $result = $method->invoke($service, $payload, $structure, []);

        return $result[0];
    }

    private function callNormalizeDate(CustomerImportService $service, string $value): string
    {
        $method = new \ReflectionMethod($service, 'normalizeDate');

        return $method->invoke($service, $value);
    }

    private function callNormalizeHeaders(CustomerImportService $service, array $headers): array
    {
        $method = new \ReflectionMethod($service, 'normalizeHeaders');

        return $method->invoke($service, $headers);
    }
}
