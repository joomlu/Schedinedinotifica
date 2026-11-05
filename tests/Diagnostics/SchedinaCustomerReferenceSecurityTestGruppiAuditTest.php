<?php
namespace Tests\Diagnostics;
require_once __DIR__.'/../Feature/SchedinaCustomerReferenceSecurityTest.php';
// Le migration sono già applicate dal launcher attestato. La suite storica
// resta immutata; transazioni per caso evitano migrate:fresh nel suo setup.
class SchedinaCustomerReferenceSecurityTestGruppiAuditTest extends \Tests\Feature\SchedinaCustomerReferenceSecurityTest
{
    public function refreshDatabase(): void
    {
        $this->beginDatabaseTransaction();
    }
}
