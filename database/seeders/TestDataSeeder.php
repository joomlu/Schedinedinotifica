<?php

namespace Database\Seeders;

use App\Models\Customers;
use App\Models\Estructura;
use App\Models\Group;
use App\Models\Released;
use App\Models\SubGroup;
use App\Models\SubGroup1;
use App\Models\Title;
use App\Models\TypeDoc;
use App\Models\TypeStreet;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔄 Creando datos de prueba...\n\n";

        // 1. Crear Groups básicos
        $group1 = Group::create(['name' => 'Grupo A']);
        $group2 = Group::create(['name' => 'Grupo B']);

        $subgroup1 = SubGroup::create(['name' => 'Subgrupo 1', 'id_group' => $group1->id]);
        $subgroup2 = SubGroup::create(['name' => 'Subgrupo 2', 'id_group' => $group2->id]);

        $subgroup1_1 = SubGroup1::create(['name' => 'Subgrupo 1-1', 'id_subgroup' => $subgroup1->id]);

        // 2. Crear Title, TypeDoc, Released, TypeStreet básicos
        $title1 = Title::create(['name' => 'Sr.']);
        $title2 = Title::create(['name' => 'Sra.']);

        $typedoc1 = TypeDoc::create(['name' => 'Pasaporte']);
        $typedoc2 = TypeDoc::create(['name' => 'DNI']);

        $released1 = Released::create(['name' => 'Italia']);
        $released2 = Released::create(['name' => 'España']);

        $typestreet1 = TypeStreet::create(['name' => 'Via']);
        $typestreet2 = TypeStreet::create(['name' => 'Calle']);

        // 3. Crear 2 Estructuras (Hotels)
        $structure1 = Estructura::create([
            'name' => 'Hotel Satellite Roma',
            'phone' => '+39 06 1234567',
            'city' => 'Roma',
            'address' => 'Via Roma 123',
            'email' => 'roma@hotelsatellite.com',
            'cp' => '00100',
            'web' => 'www.hotelsatellite-roma.com',
            'cf' => 'HTLRM12345',
            'piva' => '12345678901',
            'startact' => '2020-01-01',
            'typology' => 'Hotel',
            'clasification' => '4 stelle',
            'numshedine' => 50,
            'roomdisp' => 45,
            'ref' => 'REF-ROMA-001',
            'beddisp' => 90,
            'refpass' => 'PASS-ROMA-001',
        ]);

        $structure2 = Estructura::create([
            'name' => 'Hotel Satellite Milano',
            'phone' => '+39 02 7654321',
            'city' => 'Milano',
            'address' => 'Via Milano 456',
            'email' => 'milano@hotelsatellite.com',
            'cp' => '20100',
            'web' => 'www.hotelsatellite-milano.com',
            'cf' => 'HTLML67890',
            'piva' => '98765432109',
            'startact' => '2019-06-15',
            'typology' => 'Hotel',
            'clasification' => '5 stelle',
            'numshedine' => 80,
            'roomdisp' => 75,
            'ref' => 'REF-MILANO-001',
            'beddisp' => 150,
            'refpass' => 'PASS-MILANO-001',
        ]);

        // 4. Crear Customers para cada estructura
        $customers = [
            [
                'name' => 'Mario',
                'surname' => 'Rossi',
                'date_birth' => '1985-03-15',
                'place_birth' => 'Roma',
                'id_nat' => 1,
                'citizenship' => 'Italiana',
                'id_title' => $title1->id,
                'id_typedoc' => $typedoc1->id,
                'num_doc' => 'AA123456',
                'id_released' => $released1->id,
                'id_typestreet' => $typestreet1->id,
                'address' => 'Roma 100',
                'numi_address' => '10',
                'city_res' => 'Roma',
                'prov_res' => 'RM',
                'nat_res' => 'Italia',
                'cp_res' => '00100',
                'structure_id' => $structure1->id,
                'id_group' => $group1->id,
                'id_subgroup' => $subgroup1->id,
                'id_subgroup1' => $subgroup1_1->id,
            ],
            [
                'name' => 'Giulia',
                'surname' => 'Bianchi',
                'date_birth' => '1990-07-22',
                'place_birth' => 'Roma',
                'id_nat' => 1,
                'citizenship' => 'Italiana',
                'id_title' => $title2->id,
                'id_typedoc' => $typedoc2->id,
                'num_doc' => 'BB987654',
                'id_released' => $released1->id,
                'id_typestreet' => $typestreet1->id,
                'address' => 'Roma 200',
                'numi_address' => '20',
                'city_res' => 'Roma',
                'prov_res' => 'RM',
                'nat_res' => 'Italia',
                'cp_res' => '00100',
                'structure_id' => $structure1->id,
                'id_group' => $group1->id,
                'id_subgroup' => $subgroup1->id,
                'id_subgroup1' => $subgroup1_1->id,
            ],
            [
                'name' => 'Luca',
                'surname' => 'Verdi',
                'date_birth' => '1988-11-10',
                'place_birth' => 'Milano',
                'id_nat' => 1,
                'citizenship' => 'Italiana',
                'id_title' => $title1->id,
                'id_typedoc' => $typedoc1->id,
                'num_doc' => 'CC112233',
                'id_released' => $released1->id,
                'id_typestreet' => $typestreet2->id,
                'address' => 'Milano 300',
                'numi_address' => '30',
                'city_res' => 'Milano',
                'prov_res' => 'MI',
                'nat_res' => 'Italia',
                'cp_res' => '20100',
                'structure_id' => $structure2->id,
                'id_group' => $group2->id,
                'id_subgroup' => $subgroup2->id,
                'id_subgroup1' => null,
            ],
            [
                'name' => 'Sofia',
                'surname' => 'Ferrari',
                'date_birth' => '1992-05-18',
                'place_birth' => 'Milano',
                'id_nat' => 1,
                'citizenship' => 'Italiana',
                'id_title' => $title2->id,
                'id_typedoc' => $typedoc2->id,
                'num_doc' => 'DD445566',
                'id_released' => $released1->id,
                'id_typestreet' => $typestreet2->id,
                'address' => 'Milano 400',
                'numi_address' => '40',
                'city_res' => 'Milano',
                'prov_res' => 'MI',
                'nat_res' => 'Italia',
                'cp_res' => '20100',
                'structure_id' => $structure2->id,
                'id_group' => $group2->id,
                'id_subgroup' => $subgroup2->id,
                'id_subgroup1' => null,
            ],
        ];

        foreach ($customers as $customerData) {
            Customers::create($customerData);
        }

        // 5. Actualizar users con structures
        \App\Models\User::where('email', 'satellite@test.com')->update(['structure_id' => $structure1->id]);
        \App\Models\User::where('email', 'hotel@test.com')->update(['structure_id' => $structure1->id]);
        \App\Models\User::where('email', 'staff@test.com')->update(['structure_id' => $structure1->id]);

        echo "✅ Estructuras creadas:\n";
        echo "   - {$structure1->name} (ID: {$structure1->id})\n";
        echo "   - {$structure2->name} (ID: {$structure2->id})\n\n";

        echo "✅ Customers creados: 4 (2 por estructura)\n\n";

        echo "✅ Usuarios actualizados con structures:\n";
        echo "   - satellite@test.com → Hotel Satellite Roma\n";
        echo "   - hotel@test.com → Hotel Satellite Roma\n";
        echo "   - staff@test.com → Hotel Satellite Roma\n\n";

        echo "🎯 Listo para probar:\n";
        echo "   - superadmin@test.com verá TODOS los 4 customers\n";
        echo "   - satellite@test.com verá solo 2 customers (Hotel Roma)\n";
        echo "   - hotel@test.com verá solo 2 customers (Hotel Roma)\n";
        echo "   - staff@test.com verá solo 2 customers (Hotel Roma)\n\n";
    }
}
