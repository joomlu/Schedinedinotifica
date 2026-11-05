<?php

namespace Tests\Unit;

use App\Support\Anagrafica\EtaOperativa;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class EtaOperativaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-04'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_nato_oggi_restituisce_eta_operativa_1(): void
    {
        $this->assertSame(1, EtaOperativa::etaOperativa('2026-10-04'));
    }

    public function test_nato_3_mesi_fa_restituisce_eta_operativa_1(): void
    {
        $this->assertSame(1, EtaOperativa::etaOperativa('2026-07-04'));
    }

    public function test_nato_11_mesi_fa_restituisce_eta_operativa_1(): void
    {
        $this->assertSame(1, EtaOperativa::etaOperativa('2025-11-05'));
    }

    public function test_nato_esattamente_1_anno_fa_restituisce_eta_operativa_1(): void
    {
        $this->assertSame(1, EtaOperativa::etaOperativa('2025-10-04'));
    }

    public function test_nato_2_anni_fa_restituisce_eta_operativa_2(): void
    {
        $this->assertSame(2, EtaOperativa::etaOperativa('2024-10-04'));
    }

    public function test_nato_10_anni_fa_restituisce_eta_operativa_10(): void
    {
        $this->assertSame(10, EtaOperativa::etaOperativa('2016-10-04'));
    }

    public function test_adulto_mantiene_eta_normale(): void
    {
        $this->assertSame(57, EtaOperativa::etaOperativa('1969-10-04'));
    }

    public function test_data_futura_resta_non_valida(): void
    {
        $this->assertNull(EtaOperativa::etaOperativa('2026-10-05'));
    }

    public function test_date_impossibili_con_zero_vengono_reputate_non_valide(): void
    {
        $this->assertNull(EtaOperativa::etaOperativa('00/00/0000'));
        $this->assertNull(EtaOperativa::etaOperativa('2026-00-05'));
        $this->assertNull(EtaOperativa::etaOperativa('2026-05-00'));
    }

    public function test_regressione_valori_classici_restituisce_stesso_valore(): void
    {
        $this->assertSame(5, EtaOperativa::etaOperativa('2021-10-04'));
        $this->assertSame(17, EtaOperativa::etaOperativa('2009-10-04'));
        $this->assertSame(18, EtaOperativa::etaOperativa('2008-10-04'));
        $this->assertSame(57, EtaOperativa::etaOperativa('1969-10-04'));
    }
}
