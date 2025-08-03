<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Maatwebsite\Excel\Facades\Excel;

try {
    echo "Testing Laravel Excel import functionality...\n";
    
    // Try to read the regions template we created
    $templatePath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'regions_template.xlsx');
    
    if (file_exists($templatePath)) {
        echo "Template file exists: $templatePath\n";
        
        // Try to load the file using Laravel Excel
        $collection = Excel::toCollection(new \App\Imports\RegionsImport(), $templatePath);
        echo "✓ Laravel Excel can read files!\n";
        echo "Collection count: " . $collection->count() . "\n";
        
        if ($collection->count() > 0) {
            echo "First sheet rows: " . $collection->first()->count() . "\n";
            echo "Sample data: " . json_encode($collection->first()->first()) . "\n";
        }
        
    } else {
        echo "Template file not found. Creating one first...\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
