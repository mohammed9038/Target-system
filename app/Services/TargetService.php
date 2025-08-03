<?php

namespace App\Services;

use App\Repositories\TargetRepository;
use App\Models\SalesTarget;
use App\Events\TargetUpdated;
use App\Jobs\ProcessTargetImport;
use App\Exceptions\TargetNotFoundException;
use App\Exceptions\PeriodClosedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class TargetService
{
    public function __construct(
        private TargetRepository $targetRepository
    ) {}

    /**
     * Get matrix data with performance monitoring
     */
    public function getMatrixData(array $filters = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Add performance context to filters
            $filters['_request_id'] = request()->header('X-Request-ID', uniqid());
            
            $data = $this->targetRepository->getMatrixData($filters);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log performance metrics
            Log::info('TargetService::getMatrixData performance', [
                'execution_time_ms' => $executionTime,
                'filters' => $filters,
                'data_counts' => [
                    'salesmen' => count($data['salesmen']),
                    'suppliers' => count($data['suppliers']),
                    'targets' => count($data['targets'])
                ]
            ]);
            
            // Add service-level metadata
            $data['service_metadata'] = [
                'execution_time_ms' => $executionTime,
                'cache_strategy' => 'multi_layer',
                'performance_level' => $executionTime < 100 ? 'excellent' : ($executionTime < 500 ? 'good' : 'needs_optimization')
            ];
            
            return $data;
        } catch (\Exception $e) {
            Log::error('TargetService::getMatrixData failed', [
                'filters' => $filters,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create or update target with business logic
     */
    public function createOrUpdateTarget(array $data): SalesTarget
    {
        DB::beginTransaction();
        
        try {
            $target = $this->targetRepository->createOrUpdate($data);
            
            // Clear cache
            $this->clearMatrixCache();
            
            // Fire event
            event(new TargetUpdated($target));
            
            DB::commit();
            
            return $target;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import targets from Excel/CSV
     */
    public function importTargets(array $targetsData): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        
        try {
            foreach ($targetsData as $index => $targetData) {
                try {
                    $this->targetRepository->createOrUpdate($targetData);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row {$index}: " . $e->getMessage();
                }
            }
            
            // Clear cache after import
            $this->clearMatrixCache();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Export targets to Excel format
     */
    public function exportTargets(array $filters = []): array
    {
        return $this->targetRepository->getExportData($filters);
    }

    /**
     * Get target statistics
     */
    public function getStatistics(): array
    {
        return Cache::remember('target_statistics', 600, function () {
            return [
                'total_targets' => $this->targetRepository->count(),
                'active_periods' => $this->targetRepository->getActivePeriods(),
                'top_performers' => $this->targetRepository->getTopPerformers(),
                'category_breakdown' => $this->targetRepository->getCategoryBreakdown(),
            ];
        });
    }

    /**
     * Clear matrix cache
     */
    private function clearMatrixCache(): void
    {
        Cache::tags(['matrix', 'targets'])->flush();
    }

    /**
     * Validate target data
     */
    public function validateTargetData(array $data): array
    {
        $errors = [];

        if (empty($data['salesman_id'])) {
            $errors[] = 'Salesman is required';
        }

        if (empty($data['supplier_id'])) {
            $errors[] = 'Supplier is required';
        }

        if (empty($data['category_id'])) {
            $errors[] = 'Category is required';
        }

        if (!isset($data['target_amount']) || $data['target_amount'] <= 0) {
            $errors[] = 'Target amount must be greater than 0';
        }

        return $errors;
    }
}
