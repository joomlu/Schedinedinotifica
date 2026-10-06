<?php

require __DIR__.'/questura-checks.php';
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fixtures = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        foreach (['vuote', 'errore'] as $kind) {
            $s = $this->structureFor(null);
            $u = $this->actor('struttura_user', null, $s->id);
            $u->update(['username' => 'ux-'.$kind, 'password' => \Illuminate\Support\Facades\Hash::make('Password-ux-fixture-123!')]);
            if ($kind === 'errore') {
                \Illuminate\Support\Facades\DB::table('struttura')->where('id', $s->id)->update(['questura_password' => 'CIPHERTEXT-NON-VALIDO-UX']);
            }
        }
    }
};
$fixtures->seed();
echo "Fixture UX sintetiche create nel solo database effimero.\n";
