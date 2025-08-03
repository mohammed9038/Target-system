<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\ChannelController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SalesmanController;
use App\Http\Controllers\Api\V1\PeriodController;
use App\Http\Controllers\Api\V1\TargetController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\DependentController;
use App\Http\Controllers\API\MasterDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    
    // Sanctum CSRF cookie
    Route::get('/sanctum/csrf-cookie', function () {
        return response()->json(['message' => 'CSRF cookie set']);
    });

    // Auth routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // Test endpoint
    Route::get('/test-auth', function() {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user() ? Auth::user()->username : null,
            'guard' => config('auth.defaults.guard')
        ]);
    });
    
    // Debug matrix endpoint
    Route::get('/debug-matrix', function() {
        try {
            $userCount = App\Models\User::count();
            $salesmenCount = App\Models\Salesman::count();
            $suppliersCount = App\Models\Supplier::count();
            
            return response()->json([
                'status' => 'success',
                'auth_check' => Auth::check(),
                'user' => Auth::user() ? Auth::user()->username : null,
                'counts' => [
                    'users' => $userCount,
                    'salesmen' => $salesmenCount,
                    'suppliers' => $suppliersCount
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    });

    // Protected routes
    Route::middleware(['web', 'auth'])->group(function () {
        
        // Master Data CRUD (Admin only)
        Route::middleware('admin')->group(function () {
            Route::apiResource('regions', RegionController::class)->names('api.regions');
            Route::apiResource('channels', ChannelController::class)->names('api.channels');
            Route::apiResource('suppliers', SupplierController::class)->names('api.suppliers');
            Route::apiResource('categories', CategoryController::class)->names('api.categories');
            Route::apiResource('salesmen', SalesmanController::class)->names('api.salesmen');
            
            // Periods management (Admin only)
            Route::get('/periods', [PeriodController::class, 'index']);
            Route::post('/periods', [PeriodController::class, 'store']);
            Route::patch('/periods/{year}/{month}', [PeriodController::class, 'update']);
            Route::get('/periods/check', [PeriodController::class, 'checkStatus']);
        });

        // User info
        Route::get('/user/info', [DependentController::class, 'userInfo']);

        // Dependent dropdowns
        Route::get('/deps/regions', [DependentController::class, 'regions']);
        Route::get('/deps/channels', [DependentController::class, 'channels']);
        Route::get('/deps/salesmen', [DependentController::class, 'salesmen']);
        Route::get('/deps/suppliers', [DependentController::class, 'suppliers']);
        Route::get('/deps/categories', [DependentController::class, 'categories']);
        
        // Filtered dependencies for cascading dropdowns
        Route::get('/deps/filtered/suppliers', [DependentController::class, 'filteredSuppliers']);
        Route::get('/deps/filtered/categories', [DependentController::class, 'filteredCategories']);
        Route::get('/deps/filtered/salesmen', [DependentController::class, 'filteredSalesmen']);


        // Export/Import routes (separate from targets resource)
        Route::get('/export/targets', [TargetController::class, 'exportCsv']);
        Route::get('/export/debug', function(Request $request) {
            return response()->json([
                'auth_check' => Auth::check(),
                'user' => Auth::user() ? [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'role' => Auth::user()->role
                ] : null,
                'request_params' => $request->all(),
                'targets_count' => \App\Models\SalesTarget::count(),
                'salesmen_count' => \App\Models\Salesman::count(),
                'suppliers_count' => \App\Models\Supplier::count(),
                'categories_count' => \App\Models\Category::count()
            ]);
        });
        Route::get('/export/template', [TargetController::class, 'downloadTemplate']);
        
        // Targets (Admin and Manager)
        Route::get('/targets/matrix', [TargetController::class, 'getMatrix']);
        Route::post('/targets/bulk', [TargetController::class, 'bulkUpsert']);
        Route::post('/targets/upload', [TargetController::class, 'upload']);
        Route::post('/targets/bulk-save', [TargetController::class, 'bulkSave']);
        Route::apiResource('targets', TargetController::class)->names('api.targets');

        // Reports (Admin and Manager with scope)
        Route::get('/reports/summary', [ReportController::class, 'summary']);
    });
    
    // Master Data Import/Export routes (rate limited)
    Route::middleware(['throttle:20,1'])->prefix('master-data')->group(function () {
        // Salesmen routes
        Route::get('/salesmen', [MasterDataController::class, 'getSalesmen']);
        Route::get('/salesmen/export', [MasterDataController::class, 'exportSalesmen']);
        Route::post('/salesmen/import', [MasterDataController::class, 'importSalesmen']);
        Route::get('/salesmen/template', [MasterDataController::class, 'getSalesmenTemplate']);
        
        // Suppliers routes  
        Route::get('/suppliers', [MasterDataController::class, 'getSuppliers']);
        Route::get('/suppliers/export', [MasterDataController::class, 'exportSuppliers']);
        Route::post('/suppliers/import', [MasterDataController::class, 'importSuppliers']);
        Route::get('/suppliers/template', [MasterDataController::class, 'getSuppliersTemplate']);
        
        // Other master data routes
        Route::get('/regions', [MasterDataController::class, 'getRegions']);
        Route::get('/channels', [MasterDataController::class, 'getChannels']);
        Route::get('/categories', [MasterDataController::class, 'getCategories']);
    });
    
    // System info endpoint (for statistics)
    Route::get('/system/info', function() {
        try {
            $salesmenCount = App\Models\Salesman::count();
            $suppliersCount = App\Models\Supplier::count();
            $categoriesCount = App\Models\Category::count();
            $regionsCount = App\Models\Region::count();
            $channelsCount = App\Models\Channel::count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'salesmen_count' => $salesmenCount,
                    'suppliers_count' => $suppliersCount,
                    'categories_count' => $categoriesCount,
                    'regions_count' => $regionsCount,
                    'channels_count' => $channelsCount
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get system information'
            ], 500);
        }
    });
}); 
