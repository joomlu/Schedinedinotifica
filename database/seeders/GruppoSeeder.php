<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gruppo;

class GruppoSeeder extends Seeder
{
    public function run(): void
    {
        Gruppo::create([
            'nome' => 'Gruppo Alfa',
            'tipo' => 'Standard',
        ]);
        Gruppo::create([
            'nome' => 'Gruppo Beta',
            'tipo' => 'Premium',
        ]);
        Gruppo::create([
            'nome' => 'Gruppo Gamma',
            'tipo' => 'Base',
        ]);
    }
}
