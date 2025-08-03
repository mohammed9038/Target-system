<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache TTL configurations in seconds
     */
    private const CACHE_TTLS = [
        'matrix_data' => 300,      // 5 minutes
        'statistics' => 1800,     // 30 minutes
        'master_data' => 7200,    // 2 hours
        'exports' => 1800,        // 30 minutes
        'periods' => 3600,        // 1 hour
        'performance' => 3600,    // 1 hour
        'user_sessions' => 86400, // 24 hours
    ];

    /**
     * Cache tags for organized invalidation
     */
    private const CACHE_TAGS = [
        'targets' => ['matrix', 'statistics', 'exports'],
        'master_data' => ['salesmen', 'suppliers', 'categories', 'regions', 'channels'],
        'user_data' => ['sessions', 'preferences'],
        'system' => ['performance', 'health']
    ];

    /**
     * Get cache TTL for a specific type
     */
    public function getTtl(string $type): int
    {
        return self::CACHE_TTLS[$type] ?? config('cache.default_ttl', 3600);
    }

    /**
     * Store data with appropriate tags and TTL
     */
    public function put(string $key, $value, string $type = 'default', array $additionalTags = []): bool
    {
        try {
            $tags = $this->getTagsForType($type);
            $tags = array_merge($tags, $additionalTags);
            $ttl = $this->getTtl($type);

            if (!empty($tags)) {
                Cache::tags($tags)->put($key, $value, $ttl);
            } else {
                Cache::put($key, $value, $ttl);
            }

            Log::debug('Cache stored', [
                'key' => $key,
                'type' => $type,
                'tags' => $tags,
                'ttl' => $ttl
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Cache storage failed', [
                'key' => $key,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get data from cache with fallback
     */
    public function get(string $key, callable $callback = null, string $type = 'default', array $additionalTags = []): mixed
    {
        try {
            $tags = $this->getTagsForType($type);
            $tags = array_merge($tags, $additionalTags);

            if (!empty($tags)) {
                if ($callback) {
                    return Cache::tags($tags)->remember($key, $this->getTtl($type), $callback);
                }
                return Cache::tags($tags)->get($key);
            } else {
                if ($callback) {
                    return Cache::remember($key, $this->getTtl($type), $callback);
                }
                return Cache::get($key);
            }
        } catch (\Exception $e) {
            Log::error('Cache retrieval failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            // Return callback result if cache fails
            return $callback ? $callback() : null;
        }
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): bool
    {
        try {
            Cache::tags($tags)->flush();
            
            Log::info('Cache invalidated by tags', [
                'tags' => $tags
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Cache invalidation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Invalidate cache by pattern
     */
    public function invalidateByPattern(string $pattern): bool
    {
        try {
            // This would need Redis for pattern matching
            // For now, we'll invalidate common related caches
            $relatedTags = $this->getRelatedTags($pattern);
            return $this->invalidateByTags($relatedTags);
        } catch (\Exception $e) {
            Log::error('Cache pattern invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Warm up critical caches
     */
    public function warmup(array $strategies = []): array
    {
        $startTime = microtime(true);
        $results = [];

        try {
            // Warm up master data
            $results['master_data'] = $this->warmupMasterData();
            
            // Warm up current period data
            $results['current_period'] = $this->warmupCurrentPeriod();
            
            // Warm up user preferences
            $results['user_preferences'] = $this->warmupUserPreferences();
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Cache warmup completed', [
                'execution_time_ms' => $executionTime,
                'results' => $results
            ]);
            
            return [
                'status' => 'completed',
                'execution_time_ms' => $executionTime,
                'results' => $results
            ];
        } catch (\Exception $e) {
            Log::error('Cache warmup failed', [
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [
                'driver' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
                'cache_health' => $this->checkCacheHealth(),
                'memory_usage' => $this->getCacheMemoryUsage(),
                'hit_ratio' => $this->getCacheHitRatio()
            ];

            return $stats;
        } catch (\Exception $e) {
            Log::error('Cache statistics retrieval failed', [
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear all caches
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            
            Log::warning('All caches cleared', [
                'timestamp' => now()->toISOString(),
                'user' => 'system'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cache tags for a specific type
     */
    private function getTagsForType(string $type): array
    {
        foreach (self::CACHE_TAGS as $category => $tags) {
            if (in_array($type, $tags)) {
                return [$category];
            }
        }
        
        return [];
    }

    /**
     * Get related tags for pattern matching
     */
    private function getRelatedTags(string $pattern): array
    {
        $tags = [];
        
        if (str_contains($pattern, 'matrix')) {
            $tags[] = 'targets';
        }
        if (str_contains($pattern, 'user')) {
            $tags[] = 'user_data';
        }
        if (str_contains($pattern, 'master')) {
            $tags[] = 'master_data';
        }
        
        return $tags ?: ['targets']; // Default fallback
    }

    /**
     * Warm up master data caches
     */
    private function warmupMasterData(): array
    {
        $results = [];
        
        try {
            // Salesmen data
            $results['salesmen'] = $this->get('all_active_salesmen', function () {
                return DB::table('salesmen')
                    ->join('regions', 'salesmen.region_id', '=', 'regions.id')
                    ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
                    ->where('salesmen.is_active', true)
                    ->select([
                        'salesmen.id',
                        'salesmen.name',
                        'salesmen.employee_code',
                        'regions.name as region_name',
                        'channels.name as channel_name'
                    ])
                    ->get();
            }, 'master_data');

            // Suppliers and categories
            $results['suppliers'] = $this->get('all_suppliers_categories', function () {
                return DB::table('suppliers')
                    ->join('categories', 'suppliers.id', '=', 'categories.supplier_id')
                    ->select([
                        'suppliers.id as supplier_id',
                        'suppliers.name as supplier_name',
                        'suppliers.classification',
                        'categories.id as category_id',
                        'categories.name as category_name'
                    ])
                    ->get();
            }, 'master_data');

            return $results;
        } catch (\Exception $e) {
            Log::error('Master data warmup failed', [
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Warm up current period data
     */
    private function warmupCurrentPeriod(): array
    {
        try {
            $currentYear = date('Y');
            $currentMonth = date('n');
            
            return $this->get("period_data_{$currentYear}_{$currentMonth}", function () use ($currentYear, $currentMonth) {
                return DB::table('sales_targets')
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->count();
            }, 'targets', ["year_{$currentYear}", "month_{$currentMonth}"]);
        } catch (\Exception $e) {
            Log::error('Current period warmup failed', [
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Warm up user preferences
     */
    private function warmupUserPreferences(): array
    {
        try {
            // This would warm up common user preference data
            return ['status' => 'completed'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): string
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            return $retrieved === $testValue ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    /**
     * Get cache memory usage (approximate)
     */
    private function getCacheMemoryUsage(): string
    {
        // This would be driver-specific
        return 'N/A for file driver';
    }

    /**
     * Get cache hit ratio (approximate)
     */
    private function getCacheHitRatio(): string
    {
        // This would require cache driver with statistics
        return 'N/A for file driver';
    }
}
