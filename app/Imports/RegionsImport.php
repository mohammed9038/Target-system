<?php

namespace App\Imports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class RegionsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
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
            $regionData = [
                'name' => trim($row['name'] ?? ''),
                'is_active' => $this->parseBoolean($row['active'] ?? true)
            ];

            if (empty($regionData['name'])) {
                throw new \InvalidArgumentException('Name is required');
            }

            if ($this->updateExisting) {
                $region = Region::updateOrCreate(
                    ['name' => $regionData['name']],
                    $regionData
                );
                
                if ($region->wasRecentlyCreated) {
                    $this->importedCount++;
                } else {
                    $this->updatedCount++;
                }
            } else {
                $region = Region::create($regionData);
                $this->importedCount++;
            }

            return $region;
        } catch (\Exception $e) {
            $this->failedCount++;
            $this->errors[] = [
                'row' => $this->rowCount,
                'data' => $row,
                'error' => $e->getMessage()
            ];
            
            Log::error('Region import failed for row', [
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
            'active' => 'nullable'
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

    /**
     * Parse boolean values from various formats
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'active', 'on']);
    }

    // Getters for statistics
    public function getRowCount(): int { return $this->rowCount; }
    public function getImportedCount(): int { return $this->importedCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getFailedCount(): int { return $this->failedCount; }
    public function getErrors(): array { return $this->errors; }
}
