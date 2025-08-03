<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Get cache system status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $statistics = $this->cacheService->getStatistics();
            
            return response()->json([
                'status' => 'success',
                'data' => $statistics,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache status retrieval failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve cache status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear cache with selective options
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'tags' => 'sometimes|array',
            'tags.*' => 'string|in:targets,matrix,statistics,master_data,exports,periods,performance',
            'clear_all' => 'sometimes|boolean'
        ]);
        
        try {
            if ($request->boolean('clear_all')) {
                $result = $this->cacheService->clearAll();
                $message = 'All caches cleared successfully';
            } elseif ($request->has('tags')) {
                $result = $this->cacheService->invalidateByTags($request->tags);
                $message = 'Cache tags cleared: ' . implode(', ', $request->tags);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Either tags or clear_all parameter is required'
                ], 400);
            }
            
            if ($result) {
                Log::info('Cache cleared via API', [
                    'tags' => $request->tags ?? 'all',
                    'clear_all' => $request->boolean('clear_all'),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'timestamp' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cache clear operation failed'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Cache clear operation failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Warm up cache with specific strategies
     */
    public function warmupCache(Request $request): JsonResponse
    {
        $request->validate([
            'strategies' => 'sometimes|array',
            'strategies.*' => 'string|in:master_data,current_period,user_preferences,matrix_data',
            'filters' => 'sometimes|array'
        ]);
        
        try {
            $strategies = $request->get('strategies', []);
            $result = $this->cacheService->warmup($strategies);
            
            Log::info('Cache warmup completed via API', [
                'strategies' => $strategies,
                'result' => $result['status'],
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cache warmup completed',
                'data' => $result,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache warmup failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Cache warmup failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get comprehensive cache statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getStatistics();
            
            // Add runtime statistics
            $runtimeStats = [
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'cache_statistics' => $stats,
                    'runtime_statistics' => $runtimeStats,
                    'recommendations' => $this->getCacheRecommendations($stats)
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache statistics retrieval failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve cache statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test cache performance
     */
    public function testPerformance(Request $request): JsonResponse
    {
        $request->validate([
            'iterations' => 'sometimes|integer|min:1|max:1000',
            'data_size' => 'sometimes|string|in:small,medium,large'
        ]);
        
        try {
            $iterations = $request->get('iterations', 100);
            $dataSize = $request->get('data_size', 'medium');
            
            // Generate test data based on size
            $testData = $this->generateTestData($dataSize);
            
            $results = [
                'write_performance' => $this->testCacheWrites($testData, $iterations),
                'read_performance' => $this->testCacheReads($testData, $iterations),
                'config' => [
                    'iterations' => $iterations,
                    'data_size' => $dataSize,
                    'cache_driver' => config('cache.default')
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $results,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache performance test failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Cache performance test failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get cache recommendations based on statistics
     */
    private function getCacheRecommendations(array $stats): array
    {
        $recommendations = [];
        
        if (isset($stats['driver']) && $stats['driver'] === 'file') {
            $recommendations[] = [
                'type' => 'driver_upgrade',
                'priority' => 'medium',
                'message' => 'Consider upgrading to Redis for better performance and features',
                'action' => 'Install and configure Redis cache driver'
            ];
        }
        
        if (isset($stats['cache_health']) && $stats['cache_health'] !== 'healthy') {
            $recommendations[] = [
                'type' => 'health_issue',
                'priority' => 'high',
                'message' => 'Cache health check failed - investigate cache configuration',
                'action' => 'Check cache driver configuration and connectivity'
            ];
        }
        
        return $recommendations;
    }

    /**
     * Generate test data for performance testing
     */
    private function generateTestData(string $size): array
    {
        return match($size) {
            'small' => ['test' => 'data', 'number' => 123],
            'medium' => array_fill(0, 100, ['field' => 'value', 'timestamp' => now()]),
            'large' => array_fill(0, 1000, [
                'id' => rand(1, 10000),
                'name' => 'Test Item ' . rand(1, 1000),
                'data' => str_repeat('x', 100),
                'metadata' => ['created' => now(), 'updated' => now()]
            ])
        };
    }

    /**
     * Test cache write performance
     */
    private function testCacheWrites(array $data, int $iterations): array
    {
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $key = "performance_test_write_{$i}";
            Cache::put($key, $data, 60);
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000; // ms
        
        // Cleanup
        for ($i = 0; $i < $iterations; $i++) {
            Cache::forget("performance_test_write_{$i}");
        }
        
        return [
            'total_time_ms' => round($totalTime, 2),
            'average_time_ms' => round($totalTime / $iterations, 2),
            'operations_per_second' => round($iterations / ($totalTime / 1000), 2)
        ];
    }

    /**
     * Test cache read performance
     */
    private function testCacheReads(array $data, int $iterations): array
    {
        // First, write test data
        $keys = [];
        for ($i = 0; $i < $iterations; $i++) {
            $key = "performance_test_read_{$i}";
            $keys[] = $key;
            Cache::put($key, $data, 60);
        }
        
        // Now test reads
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            Cache::get($keys[$i]);
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000; // ms
        
        // Cleanup
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        return [
            'total_time_ms' => round($totalTime, 2),
            'average_time_ms' => round($totalTime / $iterations, 2),
            'operations_per_second' => round($iterations / ($totalTime / 1000), 2)
        ];
    }
}
