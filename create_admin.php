<?php

// Script para crear usuario admin
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin@test.com')->first();

if ($user) {
    $user->password = bcrypt('12345678');
    $user->save();
    echo "✅ Usuario admin@test.com actualizado con password: 12345678\n";
} else {
    User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('12345678'),
        'avatar' => '',
    ]);
    echo "✅ Usuario admin@test.com creado con password: 12345678\n";
}

echo "\n📧 Email: admin@test.com\n";
echo "🔑 Password: 12345678\n";
