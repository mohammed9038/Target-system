<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TargetController;
use App\Http\Controllers\API\PerformanceController;
use App\Http\Controllers\API\CacheController;
use App\Http\Controllers\API\MasterDataController;

/*
|--------------------------------------------------------------------------
| Performance-Optimized API Routes
|--------------------------------------------------------------------------
|
| These routes are optimized for performance with caching, compression,
| and efficient query patterns.
|
*/

// Performance monitoring routes
Route::prefix('performance')->group(function () {
    Route::get('/metrics', [PerformanceController::class, 'getMetrics'])
        ->name('api.performance.metrics')
        ->middleware(['throttle:60,1']);
    
    Route::get('/health', [PerformanceController::class, 'healthCheck'])
        ->name('api.performance.health')
        ->middleware(['throttle:120,1']);
    
    Route::post('/warmup', [PerformanceController::class, 'warmupCaches'])
        ->name('api.performance.warmup')
        ->middleware(['throttle:10,1']);
});

// Cache management routes
Route::prefix('cache')->group(function () {
    Route::get('/status', [CacheController::class, 'getStatus'])
        ->name('api.cache.status')
        ->middleware(['throttle:60,1']);
    
    Route::post('/clear', [CacheController::class, 'clearCache'])
        ->name('api.cache.clear')
        ->middleware(['throttle:5,1']);
    
    Route::post('/warmup', [CacheController::class, 'warmupCache'])
        ->name('api.cache.warmup')
        ->middleware(['throttle:10,1']);
        
    Route::get('/statistics', [CacheController::class, 'getStatistics'])
        ->name('api.cache.statistics')
        ->middleware(['throttle:30,1']);
});

// Enhanced target routes with performance optimization
Route::prefix('targets')->group(function () {
    // Matrix data with advanced caching
    Route::get('/matrix', [TargetController::class, 'getMatrix'])
        ->name('api.targets.matrix')
        ->middleware(['throttle:120,1', 'cache.headers:public;max_age=300']);
    
    // Optimized statistics endpoint
    Route::get('/statistics', [TargetController::class, 'getStatistics'])
        ->name('api.targets.statistics')
        ->middleware(['throttle:60,1', 'cache.headers:public;max_age=1800']);
    
    // Batch operations with enhanced performance
    Route::post('/batch', [TargetController::class, 'batchUpdate'])
        ->name('api.targets.batch')
        ->middleware(['throttle:10,1']);
    
    Route::post('/import', [TargetController::class, 'import'])
        ->name('api.targets.import')
        ->middleware(['throttle:5,1']);
    
    // Export with streaming support
    Route::get('/export', [TargetController::class, 'export'])
        ->name('api.targets.export')
        ->middleware(['throttle:10,1']);
    
    // Individual target operations
    Route::get('/{id}', [TargetController::class, 'show'])
        ->name('api.targets.show')
        ->where('id', '[0-9]+')
        ->middleware(['throttle:120,1']);
    
    Route::post('/', [TargetController::class, 'store'])
        ->name('api.targets.store')
        ->middleware(['throttle:60,1']);
    
    Route::put('/{id}', [TargetController::class, 'update'])
        ->name('api.targets.update')
        ->where('id', '[0-9]+')
        ->middleware(['throttle:60,1']);
    
    Route::delete('/{id}', [TargetController::class, 'destroy'])
        ->name('api.targets.destroy')
        ->where('id', '[0-9]+')
        ->middleware(['throttle:30,1']);
});

// Master data routes with import/export functionality
Route::prefix('master-data')->group(function () {
    // Salesmen routes
    Route::prefix('salesmen')->group(function () {
        Route::get('/', [MasterDataController::class, 'getSalesmen'])
            ->name('api.master.salesmen.list')
            ->middleware(['throttle:120,1']);
        
        Route::get('/export', [MasterDataController::class, 'exportSalesmen'])
            ->name('api.master.salesmen.export')
            ->middleware(['throttle:10,1']);
        
        Route::post('/import', [MasterDataController::class, 'importSalesmen'])
            ->name('api.master.salesmen.import')
            ->middleware(['throttle:5,1']);
        
        Route::get('/template', [MasterDataController::class, 'getSalesmenTemplate'])
            ->name('api.master.salesmen.template')
            ->middleware(['throttle:30,1']);
    });
    
    // Suppliers routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [MasterDataController::class, 'getSuppliers'])
            ->name('api.master.suppliers.list')
            ->middleware(['throttle:120,1']);
        
        Route::get('/export', [MasterDataController::class, 'exportSuppliers'])
            ->name('api.master.suppliers.export')
            ->middleware(['throttle:10,1']);
        
        Route::post('/import', [MasterDataController::class, 'importSuppliers'])
            ->name('api.master.suppliers.import')
            ->middleware(['throttle:5,1']);
        
        Route::get('/template', [MasterDataController::class, 'getSuppliersTemplate'])
            ->name('api.master.suppliers.template')
            ->middleware(['throttle:30,1']);
    });
    
    // Reference data routes
    Route::get('/regions', [MasterDataController::class, 'getRegions'])
        ->name('api.master.regions')
        ->middleware(['throttle:120,1', 'cache.headers:public;max_age=7200']);
    
    Route::get('/channels', [MasterDataController::class, 'getChannels'])
        ->name('api.master.channels')
        ->middleware(['throttle:120,1', 'cache.headers:public;max_age=7200']);
});

// Master data routes with long-term caching
Route::prefix('master-data')->group(function () {
    Route::get('/salesmen', function (Request $request) {
        return cache()->tags(['master_data', 'salesmen'])->remember(
            'api_salesmen_' . md5($request->getQueryString() ?? ''),
            7200, // 2 hours
            function () use ($request) {
                return response()->json([
                    'data' => \Illuminate\Support\Facades\DB::table('salesmen')
                        ->join('regions', 'salesmen.region_id', '=', 'regions.id')
                        ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
                        ->select([
                            'salesmen.id',
                            'salesmen.name',
                            'salesmen.employee_code',
                            'regions.name as region_name',
                            'channels.name as channel_name'
                        ])
                        ->where('salesmen.is_active', true)
                        ->when($request->region_id, function ($query, $regionId) {
                            return $query->where('salesmen.region_id', $regionId);
                        })
                        ->orderBy('salesmen.name')
                        ->get(),
                    'cached_at' => now()->toISOString()
                ]);
            }
        );
    })->name('api.master.salesmen')
      ->middleware(['throttle:120,1', 'cache.headers:public;max_age=7200']);
    
    Route::get('/suppliers', function (Request $request) {
        return cache()->tags(['master_data', 'suppliers'])->remember(
            'api_suppliers_' . md5($request->getQueryString() ?? ''),
            7200, // 2 hours
            function () use ($request) {
                return response()->json([
                    'data' => \Illuminate\Support\Facades\DB::table('suppliers')
                        ->join('categories', 'suppliers.id', '=', 'categories.supplier_id')
                        ->select([
                            'suppliers.id as supplier_id',
                            'suppliers.name as supplier_name',
                            'suppliers.supplier_code',
                            'suppliers.classification',
                            'categories.id as category_id',
                            'categories.name as category_name',
                            'categories.category_code'
                        ])
                        ->when($request->classification, function ($query, $classification) {
                            return $query->where('suppliers.classification', $classification);
                        })
                        ->orderBy('suppliers.name')
                        ->orderBy('categories.name')
                        ->get(),
                    'cached_at' => now()->toISOString()
                ]);
            }
        );
    })->name('api.master.suppliers')
      ->middleware(['throttle:120,1', 'cache.headers:public;max_age=7200']);
    
    Route::get('/periods', function () {
        return cache()->tags(['master_data', 'periods'])->remember(
            'api_active_periods',
            3600, // 1 hour
            function () {
                return response()->json([
                    'data' => \Illuminate\Support\Facades\DB::table('active_month_years')
                        ->select(['year', 'month', 'is_open'])
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->get(),
                    'cached_at' => now()->toISOString()
                ]);
            }
        );
    })->name('api.master.periods')
      ->middleware(['throttle:120,1', 'cache.headers:public;max_age=3600']);
});

// System information routes
Route::prefix('system')->group(function () {
    Route::get('/info', function () {
        return response()->json([
            'status' => 'operational',
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'timestamp' => now()->toISOString(),
            'performance' => [
                'database_status' => 'connected',
                'cache_status' => 'active',
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]
        ]);
    })->name('api.system.info')
      ->middleware(['throttle:60,1']);
      
    Route::get('/routes', function () {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())
            ->map(function ($route) {
                return [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName()
                ];
            })
            ->filter(function ($route) {
                return str_starts_with($route['uri'], 'api/');
            })
            ->values();
            
        return response()->json([
            'routes' => $routes,
            'total_count' => $routes->count()
        ]);
    })->name('api.system.routes')
      ->middleware(['throttle:10,1']);
});

// Legacy compatibility routes (with deprecation warnings)
Route::prefix('v1')->group(function () {
    Route::get('/targets', function () {
        return response()->json([
            'warning' => 'This endpoint is deprecated. Please use /api/targets/matrix instead.',
            'redirect_to' => route('api.targets.matrix'),
            'deprecated_since' => '2024-01-01',
            'removal_date' => '2024-06-01'
        ], 200, [
            'Deprecation' => 'true',
            'Sunset' => 'Wed, 01 Jun 2024 00:00:00 GMT'
        ]);
    })->name('api.v1.targets.deprecated');
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'error' => 'API endpoint not found',
        'message' => 'The requested API endpoint does not exist.',
        'available_endpoints' => [
            'GET /api/targets/matrix' => 'Get targets matrix data',
            'GET /api/targets/statistics' => 'Get targets statistics',
            'GET /api/performance/health' => 'System health check',
            'GET /api/master-data/salesmen' => 'Get salesmen data',
            'GET /api/system/info' => 'System information'
        ],
        'documentation' => 'Please refer to the API documentation for available endpoints.'
    ], 404);
});
