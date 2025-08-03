<?php

require_once 'vendor/autoload.php';
use App\Models\Region;
use App\Models\Supplier;

// Set up Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Active Status Filtering ===\n\n";

// Make one region inactive
$region = Region::first();
if ($region) {
    $region->is_active = false;
    $region->save();
    echo "Made region '{$region->name}' inactive\n";
}

// Make one supplier inactive  
$supplier = Supplier::first();
if ($supplier) {
    $supplier->is_active = false;
    $supplier->save();
    echo "Made supplier '{$supplier->name}' inactive\n\n";
}

// Test the matrix data query again
echo "=== Matrix Data After Making Some Inactive ===\n";
$targetRepository = new App\Repositories\TargetRepository();

$matrixData = $targetRepository->getMatrixData([
    'year' => 2024,
    'month' => 12
]);

echo "Matrix Data Results:\n";
echo "Salesmen count: " . $matrixData['salesmen']->count() . "\n";
echo "Suppliers count: " . $matrixData['suppliers']->count() . "\n";

echo "\nActive regions in results:\n";
$regions = collect($matrixData['salesmen'])->pluck('region_name')->unique();
foreach($regions as $regionName) {
    echo "- {$regionName}\n";
}

echo "\nActive suppliers in results:\n";  
$suppliers = collect($matrixData['suppliers'])->pluck('supplier_name')->unique();
foreach($suppliers as $supplierName) {
    echo "- {$supplierName}\n";
}

// Reactivate for cleanup
$region->is_active = true;
$region->save();
$supplier->is_active = true;
$supplier->save();

echo "\n=== Cleanup Complete - All entities reactivated ===\n";
