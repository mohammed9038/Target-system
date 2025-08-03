<?php

namespace App\Repositories;

use App\Models\SalesTarget;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TargetRepository
{
    /**
     * Get optimized matrix data with advanced caching
     */
    public function getMatrixData(array $filters = []): array
    {
        $cacheKey = 'matrix_data_v2_' . md5(serialize($filters));
        
        return Cache::tags(['matrix', 'targets'])->remember($cacheKey, config('cache.ttl.matrix_data', 300), function () use ($filters) {
            $startTime = microtime(true);
            
            // **ULTRA-OPTIMIZED QUERY 1: Salesmen with preloaded relationships**
            $salesmenQuery = $this->buildOptimizedSalesmenQuery($filters);
            
            // **ULTRA-OPTIMIZED QUERY 2: Suppliers with categories - Single JOIN**
            $suppliersQuery = $this->buildOptimizedSuppliersQuery($filters);
            
            // **ULTRA-OPTIMIZED QUERY 3: Targets with strategic indexing**
            $targetsQuery = $this->buildOptimizedTargetsQuery($filters);
            
            // Execute queries in parallel where possible
            $salesmen = $salesmenQuery->get();
            $suppliers = $suppliersQuery->get();
            $targets = $targetsQuery->get();
            
            // **OPTIMIZED QUERY 4: Batch load classifications**
            $classifications = $this->getClassificationsBatch($salesmen->pluck('salesman_id')->toArray());
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Matrix data query performance', [
                'execution_time_ms' => $executionTime,
                'salesmen_count' => $salesmen->count(),
                'suppliers_count' => $suppliers->count(),
                'targets_count' => $targets->count()
            ]);
            
            return [
                'salesmen' => $salesmen,
                'suppliers' => $suppliers,
                'targets' => $targets,
                'classifications' => $classifications,
                'performance' => [
                    'execution_time_ms' => $executionTime,
                    'cached_until' => now()->addSeconds(config('cache.ttl.matrix_data', 300))->toISOString()
                ]
            ];
        });
    }

    /**
     * Build optimized salesmen query with strategic indexing
     */
    private function buildOptimizedSalesmenQuery(array $filters)
    {
        return DB::table('salesmen')
            ->select([
                'salesmen.id as salesman_id',
                'salesmen.name',
                'salesmen.employee_code',
                'regions.id as region_id',
                'regions.name as region_name',
                'channels.id as channel_id',
                'channels.name as channel_name'
            ])
            ->join('regions', 'salesmen.region_id', '=', 'regions.id')
            ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
            ->where('salesmen.is_active', true)
            ->where('regions.is_active', true)
            ->where('channels.is_active', true)
            ->when(!empty($filters['region_id']), function ($query) use ($filters) {
                return $query->where('salesmen.region_id', $filters['region_id']);
            })
            ->when(!empty($filters['region_ids']), function ($query) use ($filters) {
                return $query->whereIn('salesmen.region_id', $filters['region_ids']);
            })
            ->when(!empty($filters['channel_id']), function ($query) use ($filters) {
                return $query->where('salesmen.channel_id', $filters['channel_id']);
            })
            ->when(!empty($filters['channel_ids']), function ($query) use ($filters) {
                return $query->whereIn('salesmen.channel_id', $filters['channel_ids']);
            })
            ->orderBy('salesmen.name');
    }

    /**
     * Build optimized suppliers query
     */
    private function buildOptimizedSuppliersQuery(array $filters)
    {
        return DB::table('suppliers')
            ->select([
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.supplier_code',
                'suppliers.classification',
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.category_code'
            ])
            ->join('categories', 'suppliers.id', '=', 'categories.supplier_id')
            ->where('suppliers.is_active', true)
            ->where('categories.is_active', true)
            ->when(!empty($filters['supplier_id']), function ($query) use ($filters) {
                return $query->where('suppliers.id', $filters['supplier_id']);
            })
            ->when(!empty($filters['category_id']), function ($query) use ($filters) {
                return $query->where('categories.id', $filters['category_id']);
            })
            ->when(!empty($filters['classifications']), function ($query) use ($filters) {
                return $query->whereIn('suppliers.classification', $filters['classifications']);
            })
            ->orderBy('suppliers.name')
            ->orderBy('categories.name');
    }

    /**
     * Build optimized targets query with composite indexes
     */
    private function buildOptimizedTargetsQuery(array $filters)
    {
        $query = DB::table('sales_targets')
            ->select([
                'salesman_id',
                'supplier_id', 
                'category_id',
                'year',
                'month',
                'target_amount'
            ]);

        // Use composite indexes efficiently
        if (!empty($filters['year']) && !empty($filters['month'])) {
            $query->where([
                ['year', '=', $filters['year']],
                ['month', '=', $filters['month']]
            ]);
        } elseif (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        // Additional filters
        return $query
            ->when(!empty($filters['supplier_id']), function ($query) use ($filters) {
                return $query->where('supplier_id', $filters['supplier_id']);
            })
            ->when(!empty($filters['category_id']), function ($query) use ($filters) {
                return $query->where('category_id', $filters['category_id']);
            })
            ->when(!empty($filters['salesman_id']), function ($query) use ($filters) {
                return $query->where('salesman_id', $filters['salesman_id']);
            });
    }

    /**
     * Get classifications in batch for better performance
     */
    private function getClassificationsBatch(array $salesmenIds): array
    {
        if (empty($salesmenIds)) {
            return [];
        }

        $cacheKey = 'classifications_batch_' . md5(implode(',', $salesmenIds));
        
        return Cache::tags(['master_data'])->remember($cacheKey, config('cache.ttl.master_data', 7200), function () use ($salesmenIds) {
            $classificationData = DB::table('salesman_classifications')
                ->select(['salesman_id', 'classification'])
                ->whereIn('salesman_id', $salesmenIds)
                ->get()
                ->groupBy('salesman_id');
                
            $classifications = [];
            foreach ($classificationData as $salesmanId => $classItems) {
                $classifications[$salesmanId] = $classItems->pluck('classification')->toArray();
            }
            
            return $classifications;
        });
    }

    /**
     * Create or update target with optimized upsert
     */
    public function createOrUpdate(array $data): SalesTarget
    {
        $startTime = microtime(true);
        
        try {
            // Use upsert for better performance
            $target = DB::transaction(function () use ($data) {
                $existingTarget = SalesTarget::where([
                    'salesman_id' => $data['salesman_id'],
                    'supplier_id' => $data['supplier_id'],
                    'category_id' => $data['category_id'],
                    'year' => $data['year'],
                    'month' => $data['month']
                ])->lockForUpdate()->first();

                if ($existingTarget) {
                    $existingTarget->update(['target_amount' => $data['target_amount']]);
                    return $existingTarget;
                } else {
                    return SalesTarget::create($data);
                }
            });

            // Clear related caches
            $this->clearTargetRelatedCaches($data);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Target upsert performance', [
                'execution_time_ms' => $executionTime,
                'operation' => $target->wasRecentlyCreated ? 'create' : 'update'
            ]);

            return $target;
        } catch (\Exception $e) {
            Log::error('Target upsert failed', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Batch create/update targets for better performance
     */
    public function batchCreateOrUpdate(array $targetsData): array
    {
        $startTime = microtime(true);
        $results = [];
        
        try {
            DB::transaction(function () use ($targetsData, &$results) {
                foreach (array_chunk($targetsData, 100) as $chunk) {
                    foreach ($chunk as $data) {
                        $results[] = $this->createOrUpdate($data);
                    }
                }
            });

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Batch target operation performance', [
                'execution_time_ms' => $executionTime,
                'targets_processed' => count($targetsData)
            ]);

            // Clear matrix cache after batch operation
            Cache::tags(['matrix', 'targets'])->flush();

            return $results;
        } catch (\Exception $e) {
            Log::error('Batch target operation failed', [
                'targets_count' => count($targetsData),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get optimized export data with streaming
     */
    public function getExportData(array $filters = []): array
    {
        $cacheKey = 'export_query_' . md5(serialize($filters));
        
        return Cache::tags(['exports'])->remember($cacheKey, config('cache.ttl.exports', 1800), function () use ($filters) {
            $query = DB::table('sales_targets')
                ->select([
                    'salesmen.name as salesman_name',
                    'salesmen.employee_code',
                    'regions.name as region_name',
                    'channels.name as channel_name',
                    'suppliers.name as supplier_name',
                    'suppliers.supplier_code',
                    'suppliers.classification',
                    'categories.name as category_name',
                    'categories.category_code',
                    'sales_targets.year',
                    'sales_targets.month',
                    'sales_targets.target_amount'
                ])
                ->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
                ->join('regions', 'salesmen.region_id', '=', 'regions.id')
                ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
                ->join('suppliers', 'sales_targets.supplier_id', '=', 'suppliers.id')
                ->join('categories', 'sales_targets.category_id', '=', 'categories.id');

            // Apply filters with index optimization
            if (!empty($filters['year']) && !empty($filters['month'])) {
                $query->where([
                    ['sales_targets.year', '=', $filters['year']],
                    ['sales_targets.month', '=', $filters['month']]
                ]);
            } elseif (!empty($filters['year'])) {
                $query->where('sales_targets.year', $filters['year']);
            }

            return $query->orderBy('salesmen.name')
                         ->orderBy('suppliers.name')
                         ->orderBy('categories.name')
                         ->get()
                         ->map(function ($target) {
                            return [
                                'Year' => $target->year,
                                'Month' => $target->month,
                                'Salesman' => $target->salesman_name,
                                'Employee Code' => $target->employee_code,
                                'Region' => $target->region_name,
                                'Channel' => $target->channel_name,
                                'Supplier' => $target->supplier_name,
                                'Category' => $target->category_name,
                                'Target Amount' => $target->target_amount,
                            ];
                         })->toArray();
        });
    }

    /**
     * Get statistics with optimized aggregation
     */
    public function getStatistics(array $filters = []): array
    {
        $cacheKey = 'statistics_' . md5(serialize($filters));
        
        return Cache::tags(['statistics', 'targets'])->remember($cacheKey, config('cache.ttl.statistics', 1800), function () use ($filters) {
            $startTime = microtime(true);
            
            $query = DB::table('sales_targets');

            // Apply filters efficiently
            if (!empty($filters['year']) && !empty($filters['month'])) {
                $query->where([
                    ['year', '=', $filters['year']],
                    ['month', '=', $filters['month']]
                ]);
            } elseif (!empty($filters['year'])) {
                $query->where('year', $filters['year']);
            }

            // Use single optimized query for all statistics
            $stats = $query->selectRaw('
                COUNT(*) as total_targets,
                COUNT(DISTINCT salesman_id) as unique_salesmen,
                COUNT(DISTINCT supplier_id) as unique_suppliers,
                COUNT(DISTINCT category_id) as unique_categories,
                SUM(target_amount) as total_amount,
                AVG(target_amount) as average_amount,
                MIN(target_amount) as min_amount,
                MAX(target_amount) as max_amount,
                STDDEV(target_amount) as stddev_amount
            ')->first();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'total_targets' => (int) $stats->total_targets,
                'unique_salesmen' => (int) $stats->unique_salesmen,
                'unique_suppliers' => (int) $stats->unique_suppliers,
                'unique_categories' => (int) $stats->unique_categories,
                'total_amount' => (float) $stats->total_amount,
                'average_amount' => round((float) $stats->average_amount, 2),
                'min_amount' => (float) $stats->min_amount,
                'max_amount' => (float) $stats->max_amount,
                'stddev_amount' => round((float) $stats->stddev_amount, 2),
                'performance' => [
                    'execution_time_ms' => $executionTime
                ]
            ];
        });
    }

    /**
     * Clear target-related caches efficiently
     */
    private function clearTargetRelatedCaches(array $data): void
    {
        $tags = ['targets', 'matrix', 'statistics'];
        
        // Add specific cache tags based on data
        if (isset($data['year'])) {
            $tags[] = "year_{$data['year']}";
        }
        if (isset($data['month'])) {
            $tags[] = "month_{$data['month']}";
        }
        
        Cache::tags($tags)->flush();
    }

    /**
     * Get total count
     */
    public function count(): int
    {
        return SalesTarget::count();
    }

    /**
     * Get active periods
     */
    public function getActivePeriods(): Collection
    {
        return DB::table('active_month_years')
            ->where('is_open', true)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get top performers
     */
    public function getTopPerformers(int $limit = 10): Collection
    {
        return DB::table('sales_targets')
            ->select([
                'salesman_id',
                DB::raw('SUM(target_amount) as total_target'),
                DB::raw('COUNT(*) as target_count')
            ])
            ->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
            ->groupBy('salesman_id')
            ->orderBy('total_target', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get category breakdown
     */
    public function getCategoryBreakdown(): Collection
    {
        return DB::table('sales_targets')
            ->select([
                'category_id',
                'categories.name as category_name',
                DB::raw('SUM(target_amount) as total_target'),
                DB::raw('COUNT(*) as target_count')
            ])
            ->join('categories', 'sales_targets.category_id', '=', 'categories.id')
            ->groupBy('category_id', 'categories.name')
            ->orderBy('total_target', 'desc')
            ->get();
    }

    /**
     * Delete target
     */
    public function delete(int $id): bool
    {
        return SalesTarget::destroy($id) > 0;
    }

    /**
     * Find target by ID
     */
    public function findById(int $id): ?SalesTarget
    {
        return SalesTarget::with(['salesman', 'supplier', 'category'])->find($id);
    }

    /**
     * Get targets by filters
     */
    public function getByFilters(array $filters): Collection
    {
        $query = SalesTarget::with(['salesman', 'supplier', 'category']);

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $query->where($key, $value);
            }
        }

        return $query->get();
    }
}
