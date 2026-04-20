<?php
/**
 * Quick Admin User Creator
 * Run this script to create an admin user: php create_admin.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating admin user...\n";

try {
    // Check if admin already exists
    $existingAdmin = User::where('email', 'admin@example.com')->first();
    
    if ($existingAdmin) {
        echo "Admin user already exists!\n";
        echo "Email: admin@example.com\n";
        echo "You can reset the password if needed.\n";
        exit(0);
    }
    
    // Create admin user
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    echo "✓ Admin user created successfully!\n\n";
    echo "Login credentials:\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n\n";
    echo "Please change the password after first login.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
