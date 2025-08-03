<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create a test admin user
$user = User::firstOrCreate(
    ['username' => 'admin'],  // Changed from email to username
    [
        'username' => 'admin',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'is_active' => true,
    ]
);

if ($user->wasRecentlyCreated) {
    echo "Admin user created successfully!\n";
    echo "Username: admin\n";
    echo "Password: password\n";
} else {
    echo "Admin user already exists!\n";
    echo "Username: admin\n";
    echo "You can use password: password\n";
    
    // Update password in case it's different
    $user->password = Hash::make('password');
    $user->save();
    echo "Password updated to: password\n";
}

echo "\nYou can now login with:\n";
echo "Username: admin\n";
echo "Password: password\n";
