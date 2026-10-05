<?php

namespace Tests\Unit;

use App\Exceptions\ComponentiImportException;
use App\Services\ComponentiImportService;
use App\Support\Componenti\DatiComponenteNormalizzati;
use PHPUnit\Framework\TestCase;

class ComponentiImportP1Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(fn (string $value): ?array =>
            strtoupper(trim($value)) === 'FRANCIA'
                ? ['nome' => 'Francia', 'cittadinanza' => 'Francese', 'is_italia' => false]
                : null
        );
    }

    protected function tearDown(): void
    {
        DatiComponenteNormalizzati::impostaGeoNazioneResolverPerTest(null);
        parent::tearDown();
    }

    private function riga(string $data = '29/02/2024'): array
    {
        return ['NomeFixture', 'CognomeFixture', 'F', 'Francia', $data, '', '', 'Francese', 'Francia', '', 'CittaFixture', 'Via', 'IndirizzoFixture', '1', '00000'];
    }

    private function batch(array $righe): array
    {
        return [
            'token' => 'fixture-p1', 'user_id' => 501, 'struttura_id' => 10,
            'schedina_id' => 77, 'formato' => 'csv',
            'raw_headers' => (new ComponentiImportService())->headersTemplate(),
            'raw_rows' => $righe, 'status' => 'pending',
            'expires_at' => time() + 3600, 'confirmed_at' => null,
        ];
    }

    private function catalogo(): array
    {
        return [['codice' => '20', 'descrizione' => 'MEMBRO GRUPPO']];
    }

    private function conferma(array $righe): array
    {
        return (new ComponentiImportService())->preparaConfermaBatch($this->batch($righe), 77, 10, 501, fn () => $this->catalogo());
    }

    private function preview(array $righe): array
    {
        $service = new ComponentiImportService();
        return $service->analizzaImportazione($service->headersTemplate(), $righe, 'csv', fn () => $this->catalogo());
    }

    public function test_date_impossibili_non_generano_payload(): void
    {
        foreach (['31/02/2026', '29/02/2025', '12-31-1980'] as $data) {
            $preview = $this->preview([$this->riga($data)]);
            $conferma = $this->conferma([$this->riga($data)]);
            fwrite(STDOUT, '\nP1-A ' . json_encode(['input' => $data, 'status' => $preview['rows'][0]['status'], 'date_preview' => $preview['rows'][0]['date_nac'], 'payload_date' => $conferma['payloads'][0]['date_nac'] ?? null, 'valid_count' => $conferma['valid_count']]) . "\n");
            $this->assertSame(0, $conferma['valid_count']);
            $this->assertSame(1, $conferma['invalid_count']);
            $this->assertSame([], $conferma['payloads']);
            $this->assertSame('NON_IMPORTABILE', $preview['rows'][0]['status']);
            $this->assertSame($data, $preview['rows'][0]['data']['date_nac']);
            $this->assertSame(1, $preview['righe_in_errore']);
        }
    }

    public function test_riga_strutturale_errore_non_genera_payload(): void
    {
        $riga = array_merge($this->riga(), ['extra']);
        $preview = $this->preview([$riga]);
        $conferma = $this->conferma([$riga]);
        fwrite(STDOUT, '\nP1-B ' . json_encode(['status' => $preview['rows'][0]['status'], 'valid_count' => $conferma['valid_count'], 'payloads' => $conferma['payloads']]) . "\n");
        $this->assertSame('ERRORE', $preview['rows'][0]['status']);
        $this->assertSame(0, $conferma['valid_count']);
        $this->assertSame(1, $conferma['invalid_count']);
        $this->assertSame([], $conferma['payloads']);
        $this->assertSame(1, $preview['righe_in_errore']);
    }

    public function test_batch_misto_conferma_solo_righe_ammissibili(): void
    {
        $result = $this->conferma([$this->riga(), $this->riga('31/02/2026'), array_merge($this->riga(), ['extra'])]);
        $this->assertSame(1, $result['valid_count']);
        $this->assertSame(2, $result['invalid_count']);
        $this->assertCount(1, $result['payloads']);
        $this->assertSame('2024-02-29', $result['payloads'][0]['date_nac']);
        $this->assertSame(10, $result['payloads'][0]['struttura_id']);
        $this->assertSame(77, $result['payloads'][0]['schedina_id']);
    }

    public function test_workflow_review_e_completamento_restano_ammissibili(): void
    {
        $review = $this->riga();
        $review[7] = '';
        $result = $this->conferma([$this->riga(), $review, $this->riga('')]);
        $this->assertSame(['COMPLETO', 'DA_VERIFICARE', 'DA_COMPLETARE'], array_column($result['payloads'], '_review_status'));
        $this->assertSame(3, $result['valid_count']);
        $this->assertNull($result['payloads'][2]['date_nac']);
        $this->assertFalse($result['payloads'][2]['_review_confirmed']);
    }

    public function test_conteggio_preview_coincide_con_conferma_e_payload(): void
    {
        $review = $this->riga();
        $review[7] = '';
        $completare = $this->riga('');
        foreach ([[$completare], [$this->riga(), $review, $completare, $this->riga('31/02/2026'), array_merge($this->riga(), ['extra'])]] as $righe) {
            $preview = $this->preview($righe);
            $conferma = $this->conferma($righe);
            // Il controller espone righe_valide come confirmable_rows.
            $confirmableRows = (int) $preview['righe_valide'];
            fwrite(STDOUT, "\nConteggi preview/conferma " . json_encode([
                'stati' => array_column($preview['rows'], 'status'),
                'confirmable_rows' => $confirmableRows,
                'valid_count' => $conferma['valid_count'],
                'payload_count' => count($conferma['payloads']),
            ]) . "\n");
            $this->assertSame($conferma['valid_count'], $confirmableRows);
            $this->assertCount($confirmableRows, $conferma['payloads']);
        }
    }


    public function test_contesto_batch_estraneo_viene_rifiutato(): void
    {
        foreach (['user_id', 'struttura_id', 'schedina_id'] as $campo) {
            $batch = $this->batch([$this->riga()]);
            $batch[$campo] = 999;
            try {
                (new ComponentiImportService())->preparaConfermaBatch($batch, 77, 10, 501, fn () => $this->catalogo());
                $this->fail('Contesto estraneo accettato: ' . $campo);
            } catch (ComponentiImportException $exception) {
                $this->assertStringContainsString('Batch', $exception->getMessage());
            }
        }
    }
}
