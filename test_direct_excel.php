<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    echo "Testing direct PhpSpreadsheet creation...\n";
    
    // Create new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set data
    $templateData = [
        ['Name', 'Active'],
        ['North Region', 'Yes'],
        ['South Region', 'Yes'],
        ['East Region', 'No']
    ];
    
    // Add data to sheet
    $row = 1;
    foreach ($templateData as $rowData) {
        $col = 1;
        foreach ($rowData as $cellData) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $cellData);
            $col++;
        }
        $row++;
    }
    
    // Style the header row
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);
    
    // Save file
    $filename = 'regions_template.xlsx';
    $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename);
    
    echo "Saving to: $fullPath\n";
    
    $writer = new Xlsx($spreadsheet);
    $writer->save($fullPath);
    
    // Check if file exists
    if (file_exists($fullPath)) {
        echo "✓ File created successfully!\n";
        echo "File size: " . filesize($fullPath) . " bytes\n";
    } else {
        echo "✗ File still does not exist!\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
