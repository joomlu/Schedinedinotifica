<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
// Nome storico mantenuto: unica base e stessa inizializzazione idempotente.
class GruppoSeeder extends Seeder
{
    public function run(): void { $this->call(GruppiSeeder::class); }
}
