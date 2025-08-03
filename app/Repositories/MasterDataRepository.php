<?php

namespace App\Repositories;

use App\Models\Salesman;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Region;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MasterDataRepository
{
    /**
     * Get salesmen with relationships and filters
     */
    public function getSalesmen(array $filters = []): Collection
    {
        $cacheKey = 'salesmen_' . md5(serialize($filters));
        
        return Cache::tags(['master_data', 'salesmen'])->remember($cacheKey, config('cache.ttl.master_data', 7200), function () use ($filters) {
            $query = DB::table('salesmen')
                ->select([
                    'salesmen.id',
                    'salesmen.employee_code',
                    'salesmen.salesman_code',
                    'salesmen.name',
                    'salesmen.classification',
                    'regions.id as region_id',
                    'regions.name as region_name',
                    'channels.id as channel_id',
                    'channels.name as channel_name',
                    'salesmen.created_at',
                    'salesmen.updated_at'
                ])
                ->join('regions', 'salesmen.region_id', '=', 'regions.id')
                ->join('channels', 'salesmen.channel_id', '=', 'channels.id');

            // Apply filters
            if (!empty($filters['region_id'])) {
                $query->where('salesmen.region_id', $filters['region_id']);
            }

            if (!empty($filters['channel_id'])) {
                $query->where('salesmen.channel_id', $filters['channel_id']);
            }

            if (!empty($filters['classification'])) {
                $query->where('salesmen.classification', $filters['classification']);
            }

            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('salesmen.name', 'like', $search)
                      ->orWhere('salesmen.employee_code', 'like', $search)
                      ->orWhere('salesmen.salesman_code', 'like', $search);
                });
            }

            return $query->orderBy('salesmen.name')->get();
        });
    }

    /**
     * Get suppliers with categories and filters
     */
    public function getSuppliers(array $filters = []): Collection
    {
        $cacheKey = 'suppliers_' . md5(serialize($filters));
        
        return Cache::tags(['master_data', 'suppliers'])->remember($cacheKey, config('cache.ttl.master_data', 7200), function () use ($filters) {
            $query = DB::table('suppliers')
                ->select([
                    'suppliers.id as supplier_id',
                    'suppliers.name as supplier_name',
                    'suppliers.supplier_code',
                    'suppliers.classification',
                    'categories.id as category_id',
                    'categories.name as category_name',
                    'categories.category_code',
                    'suppliers.created_at',
                    'suppliers.updated_at'
                ])
                ->join('categories', 'suppliers.id', '=', 'categories.supplier_id');

            // Apply filters
            if (!empty($filters['classification'])) {
                $query->where('suppliers.classification', 'like', '%' . $filters['classification'] . '%');
            }

            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('suppliers.name', 'like', $search)
                      ->orWhere('suppliers.supplier_code', 'like', $search)
                      ->orWhere('categories.name', 'like', $search)
                      ->orWhere('categories.category_code', 'like', $search);
                });
            }

            return $query->orderBy('suppliers.name')
                         ->orderBy('categories.name')
                         ->get();
        });
    }

    /**
     * Get regions with caching
     */
    public function getRegions(): Collection
    {
        return Cache::tags(['master_data', 'regions'])->remember('all_regions', config('cache.ttl.master_data', 7200), function () {
            return DB::table('regions')
                ->select(['id', 'name', 'region_code', 'created_at', 'updated_at'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get channels with caching
     */
    public function getChannels(): Collection
    {
        return Cache::tags(['master_data', 'channels'])->remember('all_channels', config('cache.ttl.master_data', 7200), function () {
            return DB::table('channels')
                ->select(['id', 'name', 'channel_code', 'created_at', 'updated_at'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get categories with caching
     */
    public function getCategories(): Collection
    {
        return Cache::tags(['master_data', 'categories'])->remember('all_categories', config('cache.ttl.master_data', 7200), function () {
            return DB::table('categories')
                ->select(['id', 'name', 'category_code', 'created_at', 'updated_at'])
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Create or update salesman
     */
    public function createOrUpdateSalesman(array $data): Salesman
    {
        try {
            // Find region and channel by name if not provided by ID
            if (isset($data['region_name']) && !isset($data['region_id'])) {
                $region = DB::table('regions')->where('name', $data['region_name'])->first();
                if ($region) {
                    $data['region_id'] = $region->id;
                } else {
                    throw new \Exception("Region '{$data['region_name']}' not found");
                }
            }

            if (isset($data['channel_name']) && !isset($data['channel_id'])) {
                $channel = DB::table('channels')->where('name', $data['channel_name'])->first();
                if ($channel) {
                    $data['channel_id'] = $channel->id;
                } else {
                    throw new \Exception("Channel '{$data['channel_name']}' not found");
                }
            }

            $salesman = Salesman::updateOrCreate(
                ['employee_code' => $data['employee_code']],
                [
                    'salesman_code' => $data['salesman_code'] ?? null,
                    'name' => $data['name'],
                    'region_id' => $data['region_id'],
                    'channel_id' => $data['channel_id'],
                    'classification' => $data['classification'] ?? 'both'
                ]
            );

            // Clear related caches
            Cache::tags(['master_data', 'salesmen'])->flush();

            return $salesman;
        } catch (\Exception $e) {
            Log::error('Failed to create/update salesman', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create or update supplier with categories
     */
    public function createOrUpdateSupplier(array $data): array
    {
        try {
            DB::beginTransaction();

            // Create or update supplier
            $supplier = Supplier::updateOrCreate(
                ['supplier_code' => $data['supplier_code']],
                [
                    'name' => $data['name'],
                    'classification' => $data['classification'] ?? ''
                ]
            );

            // Handle categories if provided
            $categories = [];
            if (isset($data['categories']) && is_array($data['categories'])) {
                foreach ($data['categories'] as $categoryData) {
                    $category = Category::updateOrCreate(
                        [
                            'supplier_id' => $supplier->id,
                            'category_code' => $categoryData['category_code']
                        ],
                        [
                            'name' => $categoryData['name']
                        ]
                    );
                    $categories[] = $category;
                }
            } elseif (isset($data['category_code']) && isset($data['category_name'])) {
                // Single category case
                $category = Category::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'category_code' => $data['category_code']
                    ],
                    [
                        'name' => $data['category_name']
                    ]
                );
                $categories[] = $category;
            }

            DB::commit();

            // Clear related caches
            Cache::tags(['master_data', 'suppliers'])->flush();

            return [
                'supplier' => $supplier,
                'categories' => $categories
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create/update supplier', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get export data for salesmen
     */
    public function getSalesmenExportData(array $filters = []): Collection
    {
        $query = DB::table('salesmen')
            ->select([
                'salesmen.employee_code',
                'salesmen.salesman_code',
                'salesmen.name',
                'regions.name as region_name',
                'channels.name as channel_name',
                'salesmen.classification',
                'salesmen.created_at',
                'salesmen.updated_at'
            ])
            ->join('regions', 'salesmen.region_id', '=', 'regions.id')
            ->join('channels', 'salesmen.channel_id', '=', 'channels.id');

        // Apply filters
        if (!empty($filters['region_id'])) {
            $query->where('salesmen.region_id', $filters['region_id']);
        }

        if (!empty($filters['channel_id'])) {
            $query->where('salesmen.channel_id', $filters['channel_id']);
        }

        if (!empty($filters['classification'])) {
            $query->where('salesmen.classification', $filters['classification']);
        }

        return $query->orderBy('salesmen.name')->get();
    }

    /**
     * Get export data for suppliers
     */
    public function getSuppliersExportData(array $filters = []): Collection
    {
        $query = DB::table('suppliers')
            ->select([
                'suppliers.supplier_code',
                'suppliers.name as supplier_name',
                'suppliers.classification',
                'categories.category_code',
                'categories.name as category_name',
                'suppliers.created_at',
                'suppliers.updated_at'
            ])
            ->join('categories', 'suppliers.id', '=', 'categories.supplier_id');

        // Apply filters
        if (!empty($filters['classification'])) {
            $query->where('suppliers.classification', 'like', '%' . $filters['classification'] . '%');
        }

        return $query->orderBy('suppliers.name')
                     ->orderBy('categories.name')
                     ->get();
    }

    /**
     * Get statistics for master data
     */
    public function getStatistics(): array
    {
        $cacheKey = 'master_data_statistics';
        
        return Cache::tags(['master_data', 'statistics'])->remember($cacheKey, config('cache.ttl.statistics', 1800), function () {
            return [
                'salesmen_count' => DB::table('salesmen')->count(),
                'suppliers_count' => DB::table('suppliers')->count(),
                'categories_count' => DB::table('categories')->count(),
                'regions_count' => DB::table('regions')->count(),
                'channels_count' => DB::table('channels')->count()
            ];
        });
    }

    /**
     * Clear all master data caches
     */
    public function clearCache(): void
    {
        Cache::tags(['master_data'])->flush();
        Log::info('Master data cache cleared');
    }
}
