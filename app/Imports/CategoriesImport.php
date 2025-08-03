<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class CategoriesImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    use Importable;

    private bool $updateExisting;
    private int $rowCount = 0;
    private int $importedCount = 0;
    private int $updatedCount = 0;
    private int $failedCount = 0;
    private array $errors = [];

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowCount++;

        try {
            $categoryData = [
                'name' => trim($row['name'] ?? ''),
                'category_code' => trim($row['category_code'] ?? ''),
                'supplier_id' => $row['supplier_id'] ?? null
            ];

            if (empty($categoryData['name'])) {
                throw new \InvalidArgumentException('Name is required');
            }

            if (empty($categoryData['supplier_id'])) {
                throw new \InvalidArgumentException('Supplier ID is required');
            }

            // Verify supplier exists
            if (!Supplier::find($categoryData['supplier_id'])) {
                throw new \InvalidArgumentException('Supplier not found with ID: ' . $categoryData['supplier_id']);
            }

            if ($this->updateExisting) {
                $category = Category::updateOrCreate(
                    ['name' => $categoryData['name'], 'supplier_id' => $categoryData['supplier_id']],
                    $categoryData
                );
                
                if ($category->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->updatedCount++;
                }
            } else {
                $category = Category::create($categoryData);
                $this->importedCount++;
            }

            return $category;
        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = [
                'row' => $this->rowCount,
                'data' => $row,
                'error' => $e->getMessage()
            ];
            
            Log::error('Category import failed for row', [
                'row' => $this->rowCount,
                'data' => $row,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_code' => 'nullable|string|max:100',
            'supplier_id' => 'required|integer|exists:suppliers,id'
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    // Getters for statistics
    public function getRowCount(): int { return $this->rowCount; }
    public function getImportedCount(): int { return $this->importedCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getFailedCount(): int { return $this->failedCount; }
    public function getErrors(): array { return $this->errors; }
}
