<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!config('customer_sharing.enabled')) throw new RuntimeException('Selezionare --customer-sharing per questa fixture.');

$fixture = new class {
    use \Tests\Support\StrutturaFixtures;

    public function seed(): array
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $user = $this->actor('proprietario', $owner->id);
        $password = 'Password-fixture-123!';
        $user->forceFill(['email' => 'sharing-owner@fixture.invalid', 'password' => \Illuminate\Support\Facades\Hash::make($password)])->save();
        $hotels = [];
        foreach (['A', 'B', 'C'] as $letter) {
            $hotel = $this->structureFor($owner);
            $hotel->nome_struttura = 'Hotel prova '.$letter; $hotel->save();
            $actor = $this->actor('struttura_user', $owner->id, $hotel->id);
            $actor->forceFill(['email' => 'sharing-'.strtolower($letter).'@fixture.invalid', 'password' => \Illuminate\Support\Facades\Hash::make($password)])->save();
            $hotels[$letter] = $hotel->id;
        }
        foreach (['A', 'B'] as $letter) {
            \App\Models\Customers::forceCreate(['struttura_id' => $hotels[$letter], 'name' => 'Persona', 'surname' => 'Condivisa', 'nac_reg' => '1980-01-02',
                'country_reg' => 'Nazione sintetica', 'city_reg' => 'Comune sintetico', 'sex' => 'F', 'type_housed' => 'Componente', 'numero_cliente' => 'LEGACY-'.$letter]);
            \App\Models\Customers::forceCreate(['struttura_id' => $hotels[$letter], 'name' => 'Omonimo', 'surname' => 'Prudente',
                'type_housed' => 'Componente', 'numero_cliente' => 'DUBBIO-'.$letter]);
        }
        \App\Models\Customers::forceCreate(['struttura_id' => $hotels['A'], 'name' => 'Riservata', 'surname' => 'Alfa', 'type_housed' => 'Componente', 'numero_cliente' => 'SOLO-A']);
        \App\Models\Customers::forceCreate(['struttura_id' => $hotels['C'], 'name' => 'Cliente', 'surname' => 'Escluso', 'type_housed' => 'Componente']);
        return ['hotels' => $hotels, 'password' => $password];
    }
};
file_put_contents(public_path('customer-sharing-fixture.json'), json_encode($fixture->seed(), JSON_THROW_ON_ERROR));
echo "Fixture condivisione sintetiche create soltanto nel runtime attestato.\n";
