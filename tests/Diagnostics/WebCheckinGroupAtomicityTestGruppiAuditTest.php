<?php
namespace Tests\Diagnostics;
require_once __DIR__.'/../Feature/WebCheckinGroupAtomicityTest.php';
// Le migration sono già applicate dal launcher attestato. La suite storica
// resta immutata; transazioni per caso evitano migrate:fresh nel suo setup.
class WebCheckinGroupAtomicityTestGruppiAuditTest extends \Tests\Feature\WebCheckinGroupAtomicityTest
{
    public function refreshDatabase(): void
    {
        $this->beginDatabaseTransaction();
    }
}
