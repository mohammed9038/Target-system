<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;

try {
    echo "Creating sample data for testing...\n";
    
    // Create channels
    $channelCount = Channel::count();
    if ($channelCount < 2) {
        Channel::create(['name' => 'Retail', 'is_active' => true]);
        Channel::create(['name' => 'Wholesale', 'is_active' => true]);
        echo "Created sample channels.\n";
    } else {
        echo "Channels already exist ($channelCount).\n";
    }
    
    // Create suppliers
    $supplierCount = Supplier::count();
    if ($supplierCount < 2) {
        Supplier::create(['name' => 'ABC Supplier Ltd', 'is_active' => true]);
        Supplier::create(['name' => 'XYZ Trading Co', 'is_active' => true]);
        echo "Created sample suppliers.\n";
    } else {
        echo "Suppliers already exist ($supplierCount).\n";
    }
    
    // Create categories
    $categoryCount = Category::count();
    if ($categoryCount < 2) {
        $supplier1 = Supplier::first();
        if ($supplier1) {
            Category::create(['name' => 'Electronics', 'supplier_id' => $supplier1->id]);
            Category::create(['name' => 'Clothing', 'supplier_id' => $supplier1->id]);
            echo "Created sample categories.\n";
        }
    } else {
        echo "Categories already exist ($categoryCount).\n";
    }
    
    // Create salesmen
    $salesmanCount = Salesman::count();
    if ($salesmanCount < 2) {
        $region = \App\Models\Region::first();
        $channel = Channel::first();
        if ($region && $channel) {
            Salesman::create([
                'name' => 'John Doe', 
                'salesman_code' => 'JD001',
                'is_active' => true,
                'region_id' => $region->id,
                'channel_id' => $channel->id,
                'classification' => 'both'
            ]);
            Salesman::create([
                'name' => 'Jane Smith',
                'salesman_code' => 'JS002', 
                'is_active' => true,
                'region_id' => $region->id,
                'channel_id' => $channel->id,
                'classification' => 'both'
            ]);
            echo "Created sample salesmen.\n";
        }
    } else {
        echo "Salesmen already exist ($salesmanCount).\n";
    }
    
    echo "Sample data ready!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
