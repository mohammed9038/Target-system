<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
            'authenticated' => auth()->check(),
            'user' => auth()->user() ? auth()->user()->username : null,
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
                'auth_check' => auth()->check(),
                'user' => auth()->user() ? auth()->user()->username : null,
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
            Route::apiResource('regions', RegionController::class);
            Route::apiResource('channels', ChannelController::class);
            Route::apiResource('suppliers', SupplierController::class);
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('salesmen', SalesmanController::class);
            
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


        // Export/Import routes (separate from targets resource)
        Route::get('/export/targets', [TargetController::class, 'exportCsv']);
        Route::get('/export/template', [TargetController::class, 'downloadTemplate']);
        
        // Targets (Admin and Manager)
        Route::get('/targets/matrix', [TargetController::class, 'getMatrix']);
        Route::post('/targets/bulk', [TargetController::class, 'bulkUpsert']);
        Route::post('/targets/upload', [TargetController::class, 'upload']);
        Route::post('/targets/bulk-save', [TargetController::class, 'bulkSave']);
        Route::apiResource('targets', TargetController::class);

        // Reports (Admin and Manager with scope)
        Route::get('/reports/summary', [ReportController::class, 'summary']);
        Route::get('/reports/export.xlsx', [ReportController::class, 'export']);
    });
}); 