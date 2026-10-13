<?php
namespace Tests\Diagnostics;
require_once __DIR__.'/../Feature/SchedinaStoreTest.php';
// Le migration sono già applicate dal launcher attestato. La suite storica
// resta immutata; transazioni per caso evitano migrate:fresh nel suo setup.
class SchedinaStoreTestGruppiAuditTest extends \Tests\Feature\SchedinaStoreTest
{
    public function refreshDatabase(): void
    {
        $this->beginDatabaseTransaction();
    }
}
