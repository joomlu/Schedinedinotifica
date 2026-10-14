<?php

namespace Database\Seeders;

use App\Services\TipoClienteSistemaService;
use Illuminate\Database\Seeder;

class TipoClienteSeeder extends Seeder
{
    public function run(): void
    {
        app(TipoClienteSistemaService::class)->initialize();
    }
}
