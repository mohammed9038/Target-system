<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;

// Set up Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test active status filtering
echo "=== Testing Active Status Functionality ===\n\n";

// Check current active/inactive counts
echo "Current Status Counts:\n";
echo "Regions - Active: " . Region::where('is_active', true)->count() . ", Inactive: " . Region::where('is_active', false)->count() . "\n";
echo "Channels - Active: " . Channel::where('is_active', true)->count() . ", Inactive: " . Channel::where('is_active', false)->count() . "\n";
echo "Suppliers - Active: " . Supplier::where('is_active', true)->count() . ", Inactive: " . Supplier::where('is_active', false)->count() . "\n";
echo "Categories - Active: " . Category::where('is_active', true)->count() . ", Inactive: " . Category::where('is_active', false)->count() . "\n";
echo "Salesmen - Active: " . Salesman::where('is_active', true)->count() . ", Inactive: " . Salesman::where('is_active', false)->count() . "\n\n";

// Test the matrix data query
echo "=== Testing Matrix Data Query ===\n";
$targetRepository = new App\Repositories\TargetRepository();

try {
    $matrixData = $targetRepository->getMatrixData([
        'year' => 2024,
        'month' => 12
    ]);
    
    echo "Matrix Data Results:\n";
    echo "Salesmen count: " . $matrixData['salesmen']->count() . "\n";
    echo "Suppliers count: " . $matrixData['suppliers']->count() . "\n";
    echo "Targets count: " . $matrixData['targets']->count() . "\n\n";
    
    // Show first few active salesmen
    echo "First 3 active salesmen:\n";
    foreach($matrixData['salesmen']->take(3) as $salesman) {
        echo "- ID: {$salesman->salesman_id}, Name: {$salesman->name}, Region: {$salesman->region_name}, Channel: {$salesman->channel_name}\n";
    }
    
    echo "\nFirst 3 active suppliers:\n";
    foreach($matrixData['suppliers']->take(3) as $supplier) {
        echo "- ID: {$supplier->supplier_id}, Name: {$supplier->supplier_name}, Category: {$supplier->category_name}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
