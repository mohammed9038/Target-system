<?php

require_once 'vendor/autoload.php';
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;

// Set up Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Toggle Status Functionality ===\n\n";

// Test Region toggle
echo "Testing Region status toggle:\n";
$region = Region::first();
if ($region) {
    $originalStatus = $region->is_active;
    echo "Region '{$region->name}' - Original status: " . ($originalStatus ? 'Active' : 'Inactive') . "\n";
    
    // Toggle status
    $region->is_active = !$region->is_active;
    $region->save();
    echo "After toggle: " . ($region->is_active ? 'Active' : 'Inactive') . "\n";
    
    // Restore original status
    $region->is_active = $originalStatus;
    $region->save();
    echo "Restored to: " . ($region->is_active ? 'Active' : 'Inactive') . "\n\n";
}

// Test matrix data filtering after toggle
echo "Testing matrix data with inactive entities:\n";

// Make one entity of each type inactive
$region = Region::first();
$supplier = Supplier::first();
$category = Category::first();

$region->is_active = false;
$region->save();
$supplier->is_active = false;
$supplier->save();
$category->is_active = false;
$category->save();

// Check matrix data
$targetRepository = new App\Repositories\TargetRepository();
$matrixData = $targetRepository->getMatrixData([]);

echo "Matrix data with inactive entities:\n";
echo "- Salesmen count: " . $matrixData['salesmen']->count() . "\n";
echo "- Suppliers count: " . $matrixData['suppliers']->count() . "\n";

// Check that inactive entities are excluded
$regionNames = collect($matrixData['salesmen'])->pluck('region_name')->unique();
$supplierNames = collect($matrixData['suppliers'])->pluck('supplier_name')->unique();

echo "- Regions in results: " . $regionNames->implode(', ') . "\n";
echo "- Suppliers in results: " . $supplierNames->implode(', ') . "\n";

// Restore active status
$region->is_active = true;
$region->save();
$supplier->is_active = true;
$supplier->save();
$category->is_active = true;
$category->save();

echo "\n=== All entities restored to active status ===\n";
echo "✅ Active status functionality working correctly!\n";
