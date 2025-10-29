<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComuneLogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('comune_logos') || ! Schema::hasTable('comuni')) {
            return;
        }

        $comuneId = 797; // BELLARIA-IGEA MARINA
        $logo = '1761475595_Logo_Bellaria_Igea_Marina.png';

        if (DB::table('comuni')->where('id', $comuneId)->exists()) {
            $exists = DB::table('comune_logos')->where('comune_id', $comuneId)->exists();
            if (! $exists) {
                DB::table('comune_logos')->insert([
                    'comune_id' => $comuneId,
                    'logo_filename' => $logo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
