<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Region;
use App\Exports\RegionsExport;
use Maatwebsite\Excel\Facades\Excel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing RegionsExport directly...\n";
    
    $filename = 'debug_regions_export_' . date('Y-m-d-H-i-s') . '.xlsx';
    $relativePath = 'private/temp/' . $filename;
    
    echo "Creating export with filters: []\n";
    $export = new RegionsExport([]);
    
    echo "Calling Excel::store with path: {$relativePath}\n";
    Excel::store($export, $relativePath, 'local');
    
    $absolutePath = realpath(storage_path('app')) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename;
    echo "Checking file at: {$absolutePath}\n";
    echo "File exists: " . (file_exists($absolutePath) ? 'Yes' : 'No') . "\n";
    
    if (file_exists($absolutePath)) {
        echo "File size: " . filesize($absolutePath) . " bytes\n";
    }
    
    // Also check with alternative path construction
    $altPath = storage_path('app/private/temp/' . $filename);
    echo "Alternative path: {$altPath}\n";
    echo "Alt path exists: " . (file_exists($altPath) ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "Export failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
