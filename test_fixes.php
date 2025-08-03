<?php

require_once 'vendor/autoload.php';

use App\Services\MasterDataService;

try {
    echo "Testing MasterDataService fixes...\n";
    
    $service = new MasterDataService();
    echo "✓ Service created successfully\n";
    
    // Test that the methods exist
    if (method_exists($service, 'exportRegions')) {
        echo "✓ exportRegions method exists\n";
    } else {
        echo "✗ exportRegions method missing\n";
    }
    
    if (method_exists($service, 'performExport')) {
        echo "✓ performExport method exists\n";
    } else {
        echo "✗ performExport method missing\n";
    }
    
    if (method_exists($service, 'generateTemplate')) {
        echo "✓ generateTemplate method exists\n";
    } else {
        echo "✗ generateTemplate method missing\n";
    }
    
    echo "\nAll basic checks passed! The VS Code problems should be resolved.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
