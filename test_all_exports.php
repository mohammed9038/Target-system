<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\MasterDataService;
use App\Repositories\MasterDataRepository;

try {
    echo "Testing all export functionality...\n";
    
    // Create service instance
    $repository = new MasterDataRepository();
    $service = new MasterDataService($repository);
    
    $entities = [
        'regions' => 'exportRegions',
        'channels' => 'exportChannels', 
        'suppliers' => 'exportSuppliers',
        'categories' => 'exportCategories',
        'salesmen' => 'exportSalesmen'
    ];
    
    foreach ($entities as $entity => $method) {
        echo "\n--- Testing $entity export ---\n";
        
        try {
            $result = $service->$method();
            echo "✓ Export completed: {$result['filename']}\n";
            echo "  File path: {$result['file_path']}\n";
            
            if (file_exists($result['file_path'])) {
                echo "  ✓ File exists! Size: " . filesize($result['file_path']) . " bytes\n";
            } else {
                echo "  ✗ File does not exist!\n";
            }
            
        } catch (\Exception $e) {
            echo "  ✗ Export failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n--- Testing template generation ---\n";
    
    foreach (array_keys($entities) as $entity) {
        try {
            $filePath = $service->generateTemplate($entity);
            $filename = basename($filePath);
            echo "✓ Template generated: $filename\n";
            
            if (file_exists($filePath)) {
                echo "  ✓ File exists! Size: " . filesize($filePath) . " bytes\n";
            } else {
                echo "  ✗ File does not exist!\n";
            }
            
        } catch (\Exception $e) {
            echo "✗ Template generation failed for $entity: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nAll tests completed!\n";
    
} catch (\Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
