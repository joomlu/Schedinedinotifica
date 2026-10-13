<?php

namespace Database\Seeders;

use App\Services\GruppiStrutturaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GruppiSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DB::table('struttura')->orderBy('id')->pluck('id') as $id) {
            app(GruppiStrutturaService::class)->initialize((int) $id);
        }
    }
}
