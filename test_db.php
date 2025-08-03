<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Region;

try {
    echo "Checking database connection and regions...\n";
    
    // Check if we can connect to database
    $regions = Region::all();
    echo "Found " . $regions->count() . " regions in database.\n";
    
    if ($regions->count() > 0) {
        echo "Sample regions:\n";
        foreach ($regions->take(3) as $region) {
            echo "- ID: {$region->id}, Name: {$region->name}, Active: " . ($region->is_active ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "No regions found. Let's create a sample region...\n";
        $region = Region::create([
            'name' => 'Test Region',
            'is_active' => true
        ]);
        echo "Created region: {$region->name} (ID: {$region->id})\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
