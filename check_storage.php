<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Checking Laravel Storage configuration...\n";
    
    $disk = Storage::disk('local');
    echo "Local disk path: " . $disk->path('') . "\n";
    
    // Test direct storage
    $testContent = 'Test file content';
    $testPath = 'private/temp/test.txt';
    
    Storage::disk('local')->put($testPath, $testContent);
    
    $fullPath = $disk->path($testPath);
    echo "Test file stored at: {$fullPath}\n";
    echo "Test file exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
    
    // Clean up
    Storage::disk('local')->delete($testPath);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
