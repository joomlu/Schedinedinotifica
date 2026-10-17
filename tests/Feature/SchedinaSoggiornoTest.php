<?php
namespace Tests\Feature;
use App\Services\SchedinaSoggiornoValidation;
use App\Models\Struttura;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SchedinaSoggiornoTest extends TestCase
{
    public static function soggiorni(): array
    {
        return [
            ['Annuale',null,null,'2026-01-01','2026-01-02',null],
            ['Annuale',null,null,'2026-01-01','2026-01-01','departure'],
            ['Annuale',null,null,'2026-01-02','2026-01-01','departure'],
            ['Stagionale','2026-12-20','2027-01-06','2026-12-20','2027-01-07',null],
            ['Stagionale','2026-12-20','2027-01-06','2027-01-06','2027-01-07',null],
            ['Stagionale','2026-12-20','2027-01-06','2026-12-19','2026-12-21','arrive'],
            ['Stagionale','2026-12-20','2027-01-06','2027-01-06','2027-01-08','departure'],
            ['Stagionale',null,null,'2026-12-20','2026-12-21','arrive'],
            ['Stagionale','2027-01-06','2026-12-20','2026-12-20','2026-12-21','arrive'],
        ];
    }
    #[DataProvider('soggiorni')]
    public function test_notti_aperte_e_check_out_senza_pernottamento($tipo,$dal,$al,$arrivo,$partenza,$error): void
    {
        $s=new Struttura(['tipo_apertura'=>$tipo,'data_apertura'=>$dal,'data_chiusura'=>$al]);
        try {
            app(SchedinaSoggiornoValidation::class)->validate($s,$arrivo,$partenza);
            $this->assertNull($error);
        } catch(ValidationException $e) {
            $this->assertNotNull($error);
            $this->assertArrayHasKey($error,$e->errors());
        }
    }
}
