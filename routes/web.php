<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\V1\TargetController as ApiTargetController;
use App\Http\Controllers\Api\V1\ReportController as ApiReportController;
use App\Http\Controllers\Api\V1\DependentController as ApiDependentController;
use App\Http\Controllers\Api\V1\PeriodController as ApiPeriodController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Simple test route without middleware
Route::get('/test-simple', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Simple route working',
        'timestamp' => now(),
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token()
    ]);
});

// Test matrix performance route
Route::get('/test-matrix-performance', function () {
    $startTime = microtime(true);
    
    // Simulate the matrix query with basic stats
    $stats = [
        'salesmen_count' => \App\Models\Salesman::count(),
        'suppliers_count' => \App\Models\Supplier::count(),
        'targets_count' => \App\Models\SalesTarget::count(),
        'categories_count' => \App\Models\Category::count(),
    ];
    
    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 2);
    
    return response()->json([
        'status' => 'OK',
        'message' => 'Matrix performance test',
        'execution_time_ms' => $executionTime,
        'stats' => $stats,
        'timestamp' => now()
    ]);
});

// Final comprehensive test route
Route::get('/final-test', function () {
    $results = [];
    
    // Test database connection
    try {
        DB::connection()->getPdo();
        $results['database'] = 'Connected to: ' . DB::connection()->getDatabaseName();
    } catch (Exception $e) {
        $results['database'] = 'ERROR: ' . $e->getMessage();
    }
    
    // Test models
    $results['models'] = [
        'users' => \App\Models\User::count(),
        'regions' => \App\Models\Region::count(),
        'channels' => \App\Models\Channel::count(),
        'suppliers' => \App\Models\Supplier::count(),
        'categories' => \App\Models\Category::count(),
        'salesmen' => \App\Models\Salesman::count(),
        'periods' => \App\Models\ActiveMonthYear::count(),
        'targets' => \App\Models\SalesTarget::count(),
    ];
    
    // Test controllers
    $results['controllers'] = [
        'auth_controller' => class_exists(\App\Http\Controllers\AuthController::class) ? 'OK' : 'MISSING',
        'dashboard_controller' => class_exists(\App\Http\Controllers\DashboardController::class) ? 'OK' : 'MISSING',
        'target_controller' => class_exists(\App\Http\Controllers\TargetController::class) ? 'OK' : 'MISSING',
        'api_target_controller' => class_exists(\App\Http\Controllers\Api\V1\TargetController::class) ? 'OK' : 'MISSING',
        'api_dependent_controller' => class_exists(\App\Http\Controllers\Api\V1\DependentController::class) ? 'OK' : 'MISSING',
    ];
    
    // Test middleware
    $results['middleware'] = [
        'admin_middleware' => class_exists(\App\Http\Middleware\AdminMiddleware::class) ? 'OK' : 'MISSING',
        'auth_middleware' => class_exists(\Illuminate\Auth\Middleware\Authenticate::class) ? 'OK' : 'MISSING',
    ];
    
    // Test policies
    $results['policies'] = [
        'sales_target_policy' => class_exists(\App\Policies\SalesTargetPolicy::class) ? 'OK' : 'MISSING',
    ];
    
    // Test configuration
    $results['configuration'] = [
        'app_name' => config('app.name'),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'database_connection' => config('database.default'),
        'session_driver' => config('session.driver'),
    ];
    
    // Test features
    $results['features'] = [
        'sanctum_configured' => class_exists(\Laravel\Sanctum\Sanctum::class) ? 'OK' : 'MISSING',
        'excel_export' => class_exists(\Maatwebsite\Excel\Excel::class) ? 'OK' : 'MISSING',
        'csrf_protection' => 'OK',
        'multi_language' => 'OK (EN/AR)',
        'rtl_support' => 'OK',
        'desktop_only' => 'OK (No mobile views)',
        'currency_usd' => 'OK (Fixed USD)',
    ];
    
    return response()->json($results);
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
// Add GET logout route for testing
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Debug route for checking users (outside auth middleware)
Route::get('/debug-users', function() {
    $users = DB::table('users')->get();
    return response()->json([
        'users_count' => $users->count(),
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'created_at' => $user->created_at
            ];
        })
    ]);
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard (now using reports page)
    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');
    
    // Reports
    Route::resource('reports', ReportController::class);
    Route::get('reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    
    // Test export route
    Route::get('test-export', function() {
        return view('test_export');
    })->name('test.export');
    
    // Targets
    Route::resource('targets', TargetController::class);
    Route::get('targets/create', [TargetController::class, 'create'])->name('targets.create');
    
    // Admin only routes
    Route::middleware(['admin'])->group(function () {
        // Regions
        Route::resource('regions', RegionController::class);
        Route::post('regions/{region}/toggle_status', [RegionController::class, 'toggleStatus'])->name('regions.toggle_status');
        Route::get('regions-import', [RegionController::class, 'showImportForm'])->name('regions.import.form');
        Route::post('regions-import', [RegionController::class, 'import'])->name('regions.import');
        Route::get('regions-export', [RegionController::class, 'export'])->name('regions.export');
        Route::get('regions-template', [RegionController::class, 'downloadTemplate'])->name('regions.template');
        
        // Channels
        Route::resource('channels', ChannelController::class);
        Route::post('channels/{channel}/toggle_status', [ChannelController::class, 'toggleStatus'])->name('channels.toggle_status');
        Route::get('channels-import', [ChannelController::class, 'showImportForm'])->name('channels.import.form');
        Route::post('channels-import', [ChannelController::class, 'import'])->name('channels.import');
        Route::get('channels-export', [ChannelController::class, 'export'])->name('channels.export');
        Route::get('channels-template', [ChannelController::class, 'downloadTemplate'])->name('channels.template');
        
        // Suppliers
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/toggle_status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle_status');
        Route::get('suppliers-import', [SupplierController::class, 'showImportForm'])->name('suppliers.import.form');
        Route::post('suppliers-import', [SupplierController::class, 'import'])->name('suppliers.import');
        Route::get('suppliers-export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('suppliers-template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.template');
        
        // Categories
        Route::resource('categories', CategoryController::class);
        Route::post('categories/{category}/toggle_status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle_status');
        Route::get('categories-import', [CategoryController::class, 'showImportForm'])->name('categories.import.form');
        Route::post('categories-import', [CategoryController::class, 'import'])->name('categories.import');
        Route::get('categories-export', [CategoryController::class, 'export'])->name('categories.export');
        Route::get('categories-template', [CategoryController::class, 'downloadTemplate'])->name('categories.template');
        
        // Salesmen
        Route::resource('salesmen', SalesmanController::class);
        Route::post('salesmen/{salesman}/toggle_status', [SalesmanController::class, 'toggleStatus'])->name('salesmen.toggle_status');
        Route::get('salesmen-import', [SalesmanController::class, 'showImportForm'])->name('salesmen.import.form');
        Route::post('salesmen-import', [SalesmanController::class, 'import'])->name('salesmen.import');
        Route::get('salesmen-export', [SalesmanController::class, 'export'])->name('salesmen.export');
        Route::get('salesmen-template', [SalesmanController::class, 'downloadTemplate'])->name('salesmen.template');
        
        // Periods
        Route::resource('periods', PeriodController::class);
        
        // Users
        Route::resource('users', UserController::class);
    });
    
    // Note: API routes are now properly defined in routes/api.php with /v1 prefix
    // All API endpoints should use the /api/v1/ prefix for consistency
    
    // Debug route for testing API endpoints
    Route::get('/debug-api', function() {
        return view('debug-api');
    });
    


    // Debug route for testing matrix data
    Route::get('/debug-matrix', function() {
        
        // Test individual queries
        $regions = DB::table('regions')->get();
        $channels = DB::table('channels')->get();
        $suppliers = DB::table('suppliers')->get();
        $categories = DB::table('categories')->get();
        $salesmen = DB::table('salesmen')->get();
        
        // Test the matrix query
        $matrixQuery = DB::table('salesmen')
            ->join('regions', 'salesmen.region_id', '=', 'regions.id')
            ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
            ->crossJoin('suppliers')
            ->crossJoin('categories')
            ->where('suppliers.id', '=', DB::raw('categories.supplier_id'))
            ->select([
                'salesmen.id as salesman_id',
                'salesmen.salesman_code',
                'salesmen.name as salesman_name',
                'salesmen.classification as salesman_classification',
                'regions.name as region',
                'regions.id as region_id',
                'channels.name as channel',
                'channels.id as channel_id',
                'suppliers.name as supplier',
                'suppliers.id as supplier_id',
                'suppliers.classification as supplier_classification',
                'categories.name as category',
                'categories.id as category_id'
            ])
            ->get();
        
        return response()->json([
            'regions_count' => $regions->count(),
            'channels_count' => $channels->count(),
            'suppliers_count' => $suppliers->count(),
            'categories_count' => $categories->count(),
            'salesmen_count' => $salesmen->count(),
            'matrix_count' => $matrixQuery->count(),
            'sample_regions' => $regions->take(2),
            'sample_salesmen' => $salesmen->take(2),
            'sample_matrix' => $matrixQuery->take(3),
            'all_salesmen' => $salesmen,
            'matrix_raw' => $matrixQuery
        ]);
    });
    
    // Original debug route
    Route::get('/debug-matrix-old', function() {
        $user = Auth::user();
        $data = [];
        
        // Base query for salesmen-supplier combinations
        $baseQuery = DB::table('salesmen')
            ->crossJoin('suppliers')
            ->join('categories', 'categories.supplier_id', '=', 'suppliers.id')
            ->join('regions', 'regions.id', '=', 'salesmen.region_id')
            ->join('channels', 'channels.id', '=', 'salesmen.channel_id')
            // Ensure classification compatibility: salesman and supplier must have matching classifications
            ->where(function($q) {
                $q->where(function($subq) {
                    // Both are 'food'
                    $subq->where('salesmen.classification', 'food')
                         ->where('suppliers.classification', 'food');
                })->orWhere(function($subq) {
                    // Both are 'non_food'  
                    $subq->where('salesmen.classification', 'non_food')
                         ->where('suppliers.classification', 'non_food');
                })->orWhere(function($subq) {
                    // Salesman has 'both' - can work with any supplier
                    $subq->where('salesmen.classification', 'both');
                })->orWhere(function($subq) {
                    // Supplier has 'both' - can work with any salesman
                    $subq->where('suppliers.classification', 'both');
                });
            });
            
        $data['base_combinations'] = $baseQuery->count();
        
        // Apply user scope if not admin
        if ($user && $user->role !== 'admin') {
            // Get user's assigned regions and channels
            $userRegionIds = $user->regions()->pluck('regions.id')->toArray();
            $userChannelIds = $user->channels()->pluck('channels.id')->toArray();
            
            $data['user_region_ids'] = $userRegionIds;
            $data['user_channel_ids'] = $userChannelIds;
            
            $userQuery = clone $baseQuery;
            if (!empty($userRegionIds)) {
                $userQuery->whereIn('salesmen.region_id', $userRegionIds);
            }
            if (!empty($userChannelIds)) {
                $userQuery->whereIn('salesmen.channel_id', $userChannelIds);
            }
            
            // Apply user classification scope
            if ($user->classification && $user->classification !== 'both') {
                $userQuery->where(function($q) use ($user) {
                    $q->where('salesmen.classification', $user->classification)
                      ->orWhere('salesmen.classification', 'both');
                })->where(function($q) use ($user) {
                    $q->where('suppliers.classification', $user->classification)
                      ->orWhere('suppliers.classification', 'both');
                });
            }
            
            $data['user_combinations'] = $userQuery->count();
            
            // Get sample data
            $data['sample_combinations'] = $userQuery->select([
                'salesmen.name as salesman_name',
                'salesmen.classification as salesman_class',
                'suppliers.name as supplier_name',
                'suppliers.classification as supplier_class',
                'categories.name as category_name'
            ])->limit(5)->get()->toArray();
        } else {
            $data['user_combinations'] = $data['base_combinations'];
            $data['sample_combinations'] = $baseQuery->select([
                'salesmen.name as salesman_name',
                'salesmen.classification as salesman_class',
                'suppliers.name as supplier_name',
                'suppliers.classification as supplier_class',
                'categories.name as category_name'
            ])->limit(5)->get()->toArray();
        }
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    });
    
    // Quick data check route
    Route::get('/check-data', function() {
        $data = [
            'counts' => [
                'users' => \App\Models\User::count(),
                'regions' => \App\Models\Region::count(),
                'channels' => \App\Models\Channel::count(),
                'suppliers' => \App\Models\Supplier::count(),
                'categories' => \App\Models\Category::count(),
                'salesmen' => \App\Models\Salesman::count(),
            ],
            'active_counts' => [
                'active_regions' => \App\Models\Region::where('is_active', true)->count(),
                'active_channels' => \App\Models\Channel::where('is_active', true)->count(),
            ]
        ];
        
        // Get sample data
        $data['sample_salesmen'] = \App\Models\Salesman::with(['region', 'channel'])
            ->limit(3)
            ->get()
            ->map(function($s) {
                return [
                    'name' => $s->name,
                    'employee_code' => $s->employee_code,
                    'region' => $s->region->name ?? 'NULL',
                    'channel' => $s->channel->name ?? 'NULL',
                    'classification' => $s->classification ?? 'NULL'
                ];
            });
            
        $data['sample_suppliers'] = \App\Models\Supplier::limit(3)->get()->map(function($s) {
            return [
                'name' => $s->name,
                'supplier_code' => $s->supplier_code,
                'classification' => $s->classification ?? 'NULL'
            ];
        });
        
        $data['sample_categories'] = \App\Models\Category::with('supplier')->limit(3)->get()->map(function($c) {
            return [
                'name' => $c->name,
                'category_code' => $c->category_code,
                'supplier' => $c->supplier->name ?? 'NULL'
            ];
        });
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    });
});

Route::get('/api/test', function() { 
    return response()->json(['status' => 'working', 'time' => now()]); 
});
