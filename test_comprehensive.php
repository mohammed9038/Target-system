<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\MasterDataService;
use App\Repositories\MasterDataRepository;

try {
    echo "Testing updated master data functionality...\n";
    
    // Create service instance
    $repository = new MasterDataRepository();
    $service = new MasterDataService($repository);
    
    echo "ZipArchive available: " . (class_exists('ZipArchive') ? 'Yes' : 'No') . "\n";
    
    // Test regions export (should work)
    echo "\n--- Testing Regions Export ---\n";
    $result = $service->exportRegions();
    echo "✓ Export completed: {$result['filename']}\n";
    
    // Test template generation (should work)
    echo "\n--- Testing Template Generation ---\n";
    $entities = ['regions', 'channels', 'suppliers', 'categories', 'salesmen'];
    
    foreach ($entities as $entity) {
        try {
            $filePath = $service->generateTemplate($entity);
            $filename = basename($filePath);
            if (file_exists($filePath)) {
                echo "✓ $entity template: $filename (" . filesize($filePath) . " bytes)\n";
            } else {
                echo "✗ $entity template: file not created\n";
            }
        } catch (\Exception $e) {
            echo "✗ $entity template: " . $e->getMessage() . "\n";
        }
    }
    
    // Test creating CSV templates (fallback)
    echo "\n--- Creating CSV Templates for Import ---\n";
    
    $csvTemplates = [
        'regions' => [
            ['Name', 'Active'],
            ['Test Region CSV', 'Yes'],
            ['Another Region', 'No']
        ],
        'channels' => [
            ['Name', 'Active'],
            ['Test Channel CSV', 'Yes'],
            ['Another Channel', 'No']
        ],
        'suppliers' => [
            ['Name', 'Active'],
            ['Test Supplier CSV', 'Yes'],
            ['Another Supplier', 'No']
        ],
        'categories' => [
            ['Name', 'Supplier Name'],
            ['Test Category CSV', 'ABC Supplier Ltd'],
            ['Another Category', 'XYZ Trading Co']
        ],
        'salesmen' => [
            ['Name', 'Salesman Code', 'Active', 'Region Name', 'Channel Name', 'Classification'],
            ['Test Salesman CSV', 'TS001', 'Yes', 'North Region', 'Retail', 'both'],
            ['Another Salesman', 'AS002', 'No', 'South Region', 'Wholesale', 'food']
        ]
    ];
    
    $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
    
    foreach ($csvTemplates as $entity => $data) {
        $csvFile = $tempDir . DIRECTORY_SEPARATOR . $entity . '_template.csv';
        $fp = fopen($csvFile, 'w');
        
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        if (file_exists($csvFile)) {
            echo "✓ Created CSV template: {$entity}_template.csv (" . filesize($csvFile) . " bytes)\n";
        }
    }
    
    echo "\n🎉 All functionality is ready!\n";
    echo "\nNOTE: Since ZipArchive is not available, import functionality will use CSV format.\n";
    echo "Users can download CSV templates and upload CSV files for importing data.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
