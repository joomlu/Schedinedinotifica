<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RilasciatoDaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DB::table('struttura')->orderBy('id')->pluck('id') as $sid) {
            app(\App\Services\CataloghiIndirizziStrutturaService::class)->initialize((int) $sid);
        }
    }
}
