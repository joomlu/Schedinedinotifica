<?php

namespace Database\Seeders;

use App\Services\TitoliStrutturaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TitoloSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DB::table('struttura')->pluck('id') as $sid) {
            app(TitoliStrutturaService::class)->initialize((int) $sid);
        }
    }
}
