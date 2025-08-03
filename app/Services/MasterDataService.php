<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;
use App\Exports\RegionsExport;
use App\Exports\ChannelsExport;
use App\Exports\SuppliersExport;
use App\Exports\CategoriesExport;
use App\Exports\SalesmenExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Collection;

class MasterDataService
{
    protected $repository;

    public function __construct($repository = null)
    {
        $this->repository = $repository;
    }

    /**
     * Export regions data
     */
    public function exportRegions(array $filters = [], string $format = 'xlsx'): array
    {
        $fileName = 'regions_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Excel::store(new RegionsExport($filters), 'temp/' . $fileName);
        
        return [
            'filename' => $fileName,
            'file_path' => $filePath
        ];
    }

    /**
     * Export channels data  
     */
    public function exportChannels(array $filters = [], string $format = 'xlsx'): array
    {
        $fileName = 'channels_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Excel::store(new ChannelsExport($filters), 'temp/' . $fileName);
        
        return [
            'filename' => $fileName,
            'file_path' => $filePath
        ];
    }

    /**
     * Export suppliers data
     */
    public function exportSuppliers(array $filters = [], string $format = 'xlsx'): array
    {
        $fileName = 'suppliers_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Excel::store(new SuppliersExport($filters), 'temp/' . $fileName);
        
        return [
            'filename' => $fileName,
            'file_path' => $filePath
        ];
    }

    /**
     * Export categories data
     */
    public function exportCategories(array $filters = [], string $format = 'xlsx'): array
    {
        $fileName = 'categories_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Excel::store(new CategoriesExport($filters), 'temp/' . $fileName);
        
        return [
            'filename' => $fileName,
            'file_path' => $filePath
        ];
    }

    /**
     * Export salesmen data
     */
    public function exportSalesmen(array $filters = [], string $format = 'xlsx'): array
    {
        $fileName = 'salesmen_export_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Excel::store(new SalesmenExport($filters), 'temp/' . $fileName);
        
        return [
            'filename' => $fileName,
            'file_path' => $filePath
        ];
    }
    /**
     * Export data to Excel file
     */
    public function performExport(string $entity, $data): string
    {
        $fileName = $entity . '_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create Excel file using PhpSpreadsheet directly
        $this->createExcelFile($data, $filePath, ucfirst($entity) . ' Export');
        
        return $fileName;
    }

    /**
     * Generate template file
     */
    public function generateTemplate(string $entity): string
    {
        $templateData = $this->getTemplateData($entity);
        
        // Try Excel first
        $fileName = $entity . '_template_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = storage_path('app/temp/' . $fileName);
        
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $this->createExcelFile($templateData, $filePath, ucfirst($entity) . ' Template');
            return $fileName;
        } catch (\Exception $e) {
            // Fallback to CSV
            $csvFileName = $entity . '_template_' . date('Y-m-d_H-i-s') . '.csv';
            $csvFilePath = storage_path('app/temp/' . $csvFileName);
            $this->createCsvFile($templateData, $csvFilePath);
            return $csvFileName;
        }
    }

    /**
     * Create Excel file using PhpSpreadsheet
     */
    private function createExcelFile(array $data, string $filePath, string $title): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        if (empty($data)) {
            $sheet->setCellValue('A1', 'No data available');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
            return;
        }

        // Add headers
        $headers = array_keys($data[0]);
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1', ucfirst(str_replace('_', ' ', $header)));
            $col++;
        }

        // Add data
        $row = 2;
        foreach ($data as $item) {
            $col = 1;
            foreach ($item as $value) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $value);
                $col++;
            }
            $row++;
        }

        $lastCol = count($headers);
        
        // Auto-size columns
        foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    /**
     * Create CSV file for templates
     */
    private function createCsvFile(array $templateData, string $filePath): void
    {
        $fp = fopen($filePath, 'w');
        
        if (!empty($templateData)) {
            // Write headers
            fputcsv($fp, array_keys($templateData[0]));
            
            // Write data
            foreach ($templateData as $row) {
                fputcsv($fp, array_values($row));
            }
        }
        
        fclose($fp);
    }

    /**
     * Get template data for entity
     */
    private function getTemplateData(string $entity): array
    {
        switch ($entity) {
            case 'regions':
                return [
                    ['code' => 'SAMPLE001', 'name' => 'Sample Region', 'description' => 'Sample Description']
                ];
            case 'channels':
                return [
                    ['code' => 'SAMPLE001', 'name' => 'Sample Channel', 'description' => 'Sample Description']
                ];
            case 'suppliers':
                return [
                    ['code' => 'SAMPLE001', 'name' => 'Sample Supplier', 'contact_person' => 'John Doe', 'phone' => '123456789', 'email' => 'sample@example.com']
                ];
            case 'categories':
                return [
                    ['code' => 'SAMPLE001', 'name' => 'Sample Category', 'description' => 'Sample Description']
                ];
            case 'salesmen':
                return [
                    ['code' => 'SAMPLE001', 'name' => 'Sample Salesman', 'phone' => '123456789', 'email' => 'sample@example.com']
                ];
            default:
                return [];
        }
    }

    /**
     * Import CSV file
     */
    public function importCsvFile(UploadedFile $file, string $entity): array
    {
        $filePath = $file->getPathname();
        $data = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }
        
        return $this->processImportData($entity, $data);
    }

    /**
     * Process import data for specific entity
     */
    private function processImportData(string $entity, array $data): array
    {
        $results = ['success' => 0, 'errors' => []];
        
        foreach ($data as $index => $row) {
            try {
                switch ($entity) {
                    case 'regions':
                        $this->importRegion($row);
                        break;
                    case 'channels':
                        $this->importChannel($row);
                        break;
                    case 'suppliers':
                        $this->importSupplier($row);
                        break;
                    case 'categories':
                        $this->importCategory($row);
                        break;
                    case 'salesmen':
                        $this->importSalesman($row);
                        break;
                }
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        return $results;
    }

    /**
     * Import region data
     */
    private function importRegion(array $row): void
    {
        Region::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'description' => $row['description'] ?? null
            ]
        );
    }

    /**
     * Import channel data
     */
    private function importChannel(array $row): void
    {
        Channel::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'description' => $row['description'] ?? null
            ]
        );
    }

    /**
     * Import supplier data
     */
    private function importSupplier(array $row): void
    {
        Supplier::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'contact_person' => $row['contact_person'] ?? null,
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null
            ]
        );
    }

    /**
     * Import category data
     */
    private function importCategory(array $row): void
    {
        Category::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'description' => $row['description'] ?? null
            ]
        );
    }

    /**
     * Import salesman data
     */
    private function importSalesman(array $row): void
    {
        Salesman::updateOrCreate(
            ['code' => $row['code']],
            [
                'name' => $row['name'],
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null
            ]
        );
    }

    /**
     * Import categories from file
     */
    public function importCategories(UploadedFile $file, bool $updateExisting = false): array
    {
        return $this->importCsvFile($file, 'categories');
    }

    /**
     * Import channels from file
     */
    public function importChannels(UploadedFile $file, bool $updateExisting = false): array
    {
        return $this->importCsvFile($file, 'channels');
    }

    /**
     * Import regions from file
     */
    public function importRegions(UploadedFile $file, bool $updateExisting = false): array
    {
        return $this->importCsvFile($file, 'regions');
    }

    /**
     * Import suppliers from file
     */
    public function importSuppliers(UploadedFile $file, bool $updateExisting = false): array
    {
        return $this->importCsvFile($file, 'suppliers');
    }

    /**
     * Import salesmen from file
     */
    public function importSalesmen(UploadedFile $file, bool $updateExisting = false): array
    {
        return $this->importCsvFile($file, 'salesmen');
    }

    /**
     * Get all regions
     */
    public function getAllRegions(): Collection
    {
        return Region::all();
    }

    /**
     * Get all channels
     */
    public function getAllChannels(): Collection
    {
        return Channel::all();
    }

    /**
     * Get all suppliers
     */
    public function getAllSuppliers(): Collection
    {
        return Supplier::all();
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return Category::all();
    }

    /**
     * Get all salesmen
     */
    public function getAllSalesmen(): Collection
    {
        return Salesman::all();
    }
}
