<?php
echo "Starting export test...\n";

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Laravel bootstrapped.\n";

use App\Services\MasterDataService;
use App\Repositories\MasterDataRepository;

try {
    echo "Creating service...\n";
    
    // Create service instance
    $repository = new MasterDataRepository();
    $service = new MasterDataService($repository);
    
    echo "Service created successfully.\n";
    
    // Test regions export
    echo "Testing regions export...\n";
    $result = $service->exportRegions();
    echo "Export completed: {$result['filename']}\n";
    echo "File path: {$result['file_path']}\n";
    
    // Check if file exists
    if (file_exists($result['file_path'])) {
        echo "✓ Export file exists!\n";
        echo "File size: " . filesize($result['file_path']) . " bytes\n";
    } else {
        echo "✗ Export file does not exist!\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Throwable $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
