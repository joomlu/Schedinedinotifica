<?php

namespace Tests\Diagnostics;

use App\Models\Customers;
use Illuminate\Support\Facades\{Artisan, DB, Schema};
use Tests\TestCase;

class CustomerWizardMigrationTest extends TestCase
{
    use \Tests\Support\StrutturaFixtures;

    protected function tearDown(): void
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true, '--no-interaction' => true]);
        } finally {
            parent::tearDown();
        }
    }

    public function test_aggiornamento_additivo_conservazione_e_ripetizione(): void
    {
        $s = $this->structureFor(null);
        $c = Customers::create(['struttura_id' => $s->id, 'type_housed' => '', 'name' => 'Persona', 'surname' => 'Legacy', 'nota' => 'Personalizzazione sintetica']);
        $file = '2026_10_15_100000_add_wizard_submission_keys_to_clienti';
        $migration = require database_path('migrations/'.$file.'.php');
        $migration->down();
        DB::table('migrations')->where('migration', $file)->delete();
        $columns = Schema::getColumnListing('clienti');
        $before = DB::table('clienti')->select($columns)->orderBy('id')->get()->toJson();
        $count = DB::table('migrations')->count();
        foreach ([1, 2] as $pass) {
            $this->assertSame(0, Artisan::call('migrate', ['--path' => 'database/migrations/'.$file.'.php', '--force' => true, '--no-interaction' => true]));
            $this->assertSame($count + 1, DB::table('migrations')->count());
            $this->assertSame($before, DB::table('clienti')->select($columns)->orderBy('id')->get()->toJson());
            $this->assertNull(DB::table('clienti')->where('id', $c->id)->value('wizard_submission_key'));
        }
        $this->assertSame('', $c->fresh()->type_housed);
        $this->assertTrue(Schema::hasColumns('clienti', ['wizard_submission_key', 'wizard_submission_digest']));
        $indexes = collect(Schema::getIndexes('clienti'));
        $this->assertTrue($indexes->contains(fn ($i) => $i['unique'] && $i['columns'] === ['wizard_submission_key']));
    }
}
