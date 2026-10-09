<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebCheckinLifecycleMigrationAudit extends TestCase
{
    public function test_roundtrip_migration_locale_preserva_tabelle_e_ricrea_metadati(): void
    {
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $migration = require database_path('migrations/2026_10_09_230000_add_web_checkin_link_lifecycle.php');
        $this->assertTrue(Schema::hasColumn('web_checkin_richieste', 'short_token'));
        $migration->down();
        $this->assertFalse(Schema::hasColumn('web_checkin_richieste', 'short_token'));
        $this->assertTrue(Schema::hasColumn('web_checkin_richieste', 'token'));
        $migration->up();
        foreach (['short_token', 'link_expires_at', 'link_revoked_at', 'link_issued_at'] as $field) {
            $this->assertTrue(Schema::hasColumn('web_checkin_richieste', $field));
        }
    }
}
