<?php
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application so Eloquent and config are available
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

if (!User::where('email', 'test@example.com')->exists()) {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('secret'),
        'avatar' => ''
    ]);
    echo "user created\n";
} else {
    echo "user exists\n";
}
