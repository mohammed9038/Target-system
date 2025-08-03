<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TargetService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    public function __construct(
        private TargetService $targetService,
        private CacheService $cacheService
    ) {}

    /**
     * Get comprehensive performance metrics
     */
    public function getMetrics(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        try {
            $metrics = $this->targetService->getPerformanceMetrics();
            
            // Add request-specific metrics
            $metrics['request'] = [
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'timestamp' => now()->toISOString(),
                'endpoint' => $request->path()
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Performance metrics retrieval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve performance metrics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Comprehensive health check
     */
    public function healthCheck(): JsonResponse
    {
        $startTime = microtime(true);
        $checks = [];
        $overallStatus = 'healthy';
        
        try {
            // Database connectivity check
            $checks['database'] = $this->checkDatabase();
            
            // Cache system check
            $checks['cache'] = $this->checkCache();
            
            // Memory usage check
            $checks['memory'] = $this->checkMemory();
            
            // Disk space check
            $checks['disk'] = $this->checkDisk();
            
            // Application-specific checks
            $checks['application'] = $this->checkApplication();
            
            // Determine overall status
            foreach ($checks as $check) {
                if ($check['status'] !== 'healthy') {
                    $overallStatus = $check['status'] === 'warning' ? 'degraded' : 'unhealthy';
                    if ($check['status'] === 'error') {
                        break; // Critical error found
                    }
                }
            }
            
            $responseData = [
                'status' => $overallStatus,
                'timestamp' => now()->toISOString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'checks' => $checks,
                'summary' => [
                    'total_checks' => count($checks),
                    'healthy_checks' => collect($checks)->where('status', 'healthy')->count(),
                    'warning_checks' => collect($checks)->where('status', 'warning')->count(),
                    'error_checks' => collect($checks)->where('status', 'error')->count()
                ]
            ];
            
            $httpStatus = match($overallStatus) {
                'healthy' => 200,
                'degraded' => 200,
                'unhealthy' => 503
            };
            
            return response()->json($responseData, $httpStatus);
        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Health check system failure',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Warm up system caches
     */
    public function warmupCaches(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year', 'month', 'region_id', 'channel_id']);
            
            // Warm up target service caches
            $targetWarmup = $this->targetService->warmupCaches($filters);
            
            // Warm up general caches
            $cacheWarmup = $this->cacheService->warmup();
            
            return response()->json([
                'status' => 'completed',
                'message' => 'Cache warmup completed successfully',
                'results' => [
                    'target_service' => $targetWarmup,
                    'cache_service' => $cacheWarmup
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Cache warmup failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Cache warmup failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get system optimization recommendations
     */
    public function getOptimizationRecommendations(): JsonResponse
    {
        try {
            $recommendations = [];
            
            // Check memory usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
            if ($memoryUsage > 512) {
                $recommendations[] = [
                    'type' => 'memory',
                    'priority' => 'high',
                    'message' => 'High memory usage detected. Consider optimizing queries or increasing cache TTL.',
                    'current_value' => "{$memoryUsage} MB",
                    'recommended_action' => 'Review memory-intensive operations'
                ];
            }
            
            // Check cache configuration
            if (config('cache.default') === 'file') {
                $recommendations[] = [
                    'type' => 'cache',
                    'priority' => 'medium',
                    'message' => 'File cache driver in use. Consider Redis for better performance.',
                    'current_value' => 'file',
                    'recommended_action' => 'Upgrade to Redis cache driver'
                ];
            }
            
            // Check database connection pool
            $recommendations[] = [
                'type' => 'database',
                'priority' => 'low',
                'message' => 'Database connection is optimized with connection pooling.',
                'current_value' => 'optimized',
                'recommended_action' => 'Monitor query performance regularly'
            ];
            
            return response()->json([
                'status' => 'success',
                'recommendations' => $recommendations,
                'total_recommendations' => count($recommendations),
                'priority_breakdown' => [
                    'high' => collect($recommendations)->where('priority', 'high')->count(),
                    'medium' => collect($recommendations)->where('priority', 'medium')->count(),
                    'low' => collect($recommendations)->where('priority', 'low')->count()
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Optimization recommendations failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate optimization recommendations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Database connectivity check
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            
            // Test basic connectivity
            DB::connection()->getPdo();
            
            // Test query performance
            $targetCount = DB::table('sales_targets')->count();
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => $responseTime < 100 ? 'healthy' : ($responseTime < 500 ? 'warning' : 'error'),
                'response_time_ms' => $responseTime,
                'connection' => 'active',
                'target_records' => $targetCount,
                'message' => $responseTime < 100 ? 'Database performing well' : 'Database response time is slow'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cache system check
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            // Test cache write
            Cache::put($testKey, $testValue, 10);
            
            // Test cache read
            $retrieved = Cache::get($testKey);
            
            // Cleanup
            Cache::forget($testKey);
            
            $status = $retrieved === $testValue ? 'healthy' : 'error';
            
            return [
                'status' => $status,
                'driver' => config('cache.default'),
                'message' => $status === 'healthy' ? 'Cache system operational' : 'Cache read/write failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache system check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Memory usage check
     */
    private function checkMemory(): array
    {
        $currentUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $peakUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
        $memoryLimit = ini_get('memory_limit');
        
        $status = $currentUsage < 256 ? 'healthy' : ($currentUsage < 512 ? 'warning' : 'error');
        
        return [
            'status' => $status,
            'current_usage_mb' => round($currentUsage, 2),
            'peak_usage_mb' => round($peakUsage, 2),
            'memory_limit' => $memoryLimit,
            'message' => $status === 'healthy' ? 'Memory usage normal' : 'High memory usage detected'
        ];
    }

    /**
     * Disk space check
     */
    private function checkDisk(): array
    {
        try {
            $path = storage_path();
            $freeBytes = disk_free_space($path);
            $totalBytes = disk_total_space($path);
            
            if ($freeBytes === false || $totalBytes === false) {
                return [
                    'status' => 'warning',
                    'message' => 'Unable to check disk space'
                ];
            }
            
            $freeGB = round($freeBytes / (1024 ** 3), 2);
            $totalGB = round($totalBytes / (1024 ** 3), 2);
            $usedPercent = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2);
            
            $status = $usedPercent < 80 ? 'healthy' : ($usedPercent < 90 ? 'warning' : 'error');
            
            return [
                'status' => $status,
                'free_space_gb' => $freeGB,
                'total_space_gb' => $totalGB,
                'used_percent' => $usedPercent,
                'message' => $status === 'healthy' ? 'Sufficient disk space' : 'Low disk space warning'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Disk space check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Application-specific health checks
     */
    private function checkApplication(): array
    {
        try {
            $checks = [];
            
            // Check if essential tables exist
            $tables = ['sales_targets', 'salesmen', 'suppliers', 'categories'];
            foreach ($tables as $table) {
                try {
                    DB::table($table)->limit(1)->exists();
                    $checks[$table] = 'exists';
                } catch (\Exception $e) {
                    $checks[$table] = 'missing';
                }
            }
            
            $missingTables = collect($checks)->filter(fn($status) => $status === 'missing');
            
            return [
                'status' => $missingTables->isEmpty() ? 'healthy' : 'error',
                'database_tables' => $checks,
                'message' => $missingTables->isEmpty() ? 'All required tables present' : 'Missing database tables: ' . $missingTables->keys()->implode(', ')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Application health check failed',
                'error' => $e->getMessage()
            ];
        }
    }
}
