<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use App\Models\ActiveMonthYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TargetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = SalesTarget::with([
            'region', 'channel', 'salesman', 'supplier', 'category'
        ]);

        // Apply user scope
        if (!$user->isAdmin()) {
            $userScope = $user->scope();
            
            if ($userScope) {
                // Apply region scope (direct on sales_targets table)
                if (!empty($userScope['region_ids'])) {
                    $query->whereIn('region_id', $userScope['region_ids']);
                }
                
                // Apply channel scope (direct on sales_targets table)
                if (!empty($userScope['channel_ids'])) {
                    $query->whereIn('channel_id', $userScope['channel_ids']);
                }
                
                // Apply classification scope using many-to-many
                if (!empty($userScope['classifications'])) {
                    $query->whereHas('salesman', function($q) use ($userScope) {
                        $q->whereHas('classifications', function($subQ) use ($userScope) {
                            $subQ->whereIn('classification', $userScope['classifications']);
                        });
                    });
                    
                    // Also filter by supplier classification
                    $query->whereHas('supplier', function($q) use ($userScope) {
                        $q->whereIn('classification', $userScope['classifications']);
                    });
                }
            }
        }

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->channel_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('employee_code')) {
            $query->byEmployeeCode($request->employee_code);
        }

        if ($request->filled('classification')) {
            $query->whereHas('salesman', function($q) use ($request) {
                $q->whereHas('classifications', function($subQ) use ($request) {
                    $subQ->where('classification', $request->classification);
                });
            });
        }

        if ($request->filled('salesman_id')) {
            $query->where('salesman_id', $request->salesman_id);
        }

        $targets = $query->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->orderBy('region_id')
                        ->orderBy('channel_id')
                        ->paginate(50);

        return response()->json([
            'data' => $targets
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'region_id' => 'required|exists:regions,id',
            'channel_id' => 'required|exists:channels,id',
            'salesman_id' => 'required|exists:salesmen,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'target_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if period is open
        $period = ActiveMonthYear::where('year', $request->year)
                                ->where('month', $request->month)
                                ->first();

        if (!$period || !$period->is_open) {
            return response()->json([
                'message' => 'This period is not open for target entry'
            ], 422);
        }

        // Apply user scope for non-admin users
        if (!$user->isAdmin()) {
            $scope = $user->scope();
            
            // Check region permission
            if (!empty($scope['region_ids']) && !in_array($request->region_id, $scope['region_ids'])) {
                return response()->json([
                    'message' => 'You can only create targets within your assigned regions'
                ], 403);
            }
            
            // Check channel permission
            if (!empty($scope['channel_ids']) && !in_array($request->channel_id, $scope['channel_ids'])) {
                return response()->json([
                    'message' => 'You can only create targets within your assigned channels'
                ], 403);
            }
            
            // Check classification permission
            if (isset($scope['classification'])) {
                $salesman = \App\Models\Salesman::find($request->salesman_id);
                if ($salesman && $salesman->classification !== $scope['classification'] && $scope['classification'] !== 'both') {
                    return response()->json([
                        'message' => 'You can only create targets for your assigned classification'
                    ], 403);
                }
            }
        }

        // Check uniqueness
        $existing = SalesTarget::where('year', $request->year)
                              ->where('month', $request->month)
                              ->where('salesman_id', $request->salesman_id)
                              ->where('supplier_id', $request->supplier_id)
                              ->where('category_id', $request->category_id)
                              ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Target already exists for this combination'
            ], 422);
        }

        $target = SalesTarget::create($request->all());

        return response()->json([
            'data' => $target->load(['region', 'channel', 'salesman', 'supplier', 'category']),
            'message' => 'Target created successfully'
        ], 201);
    }

    public function show(SalesTarget $target)
    {
        $user = Auth::user();
        
        // Check scope for non-admin users
        if (!$user->isAdmin()) {
            $scope = $user->scope();
            
            // Check region permission
            if (!empty($scope['region_ids']) && !in_array($target->region_id, $scope['region_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned region'
                ], 403);
            }
            
            // Check channel permission
            if (!empty($scope['channel_ids']) && !in_array($target->channel_id, $scope['channel_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned channel'
                ], 403);
            }
            
            // Check classification permission
            if (isset($scope['classification'])) {
                $salesman = $target->salesman;
                if ($salesman && $salesman->classification !== $scope['classification'] && $scope['classification'] !== 'both') {
                    return response()->json([
                        'message' => 'Access denied - not your assigned classification'
                    ], 403);
                }
            }
        }

        return response()->json([
            'data' => $target->load(['region', 'channel', 'salesman', 'supplier', 'category'])
        ]);
    }

    public function update(Request $request, SalesTarget $target)
    {
        $user = Auth::user();
        
        // Check scope for non-admin users
        if (!$user->isAdmin()) {
            $scope = $user->scope();
            
            // Check region permission
            if (!empty($scope['region_ids']) && !in_array($target->region_id, $scope['region_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned region'
                ], 403);
            }
            
            // Check channel permission
            if (!empty($scope['channel_ids']) && !in_array($target->channel_id, $scope['channel_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned channel'
                ], 403);
            }
            
            // Check classification permission
            if (isset($scope['classification'])) {
                $salesman = $target->salesman;
                if ($salesman && $salesman->classification !== $scope['classification'] && $scope['classification'] !== 'both') {
                    return response()->json([
                        'message' => 'Access denied - not your assigned classification'
                    ], 403);
                }
            }
        }

        $request->validate([
            'target_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $target->update($request->only(['target_amount', 'notes']));

        return response()->json([
            'data' => $target->load(['region', 'channel', 'salesman', 'supplier', 'category']),
            'message' => 'Target updated successfully'
        ]);
    }

    public function destroy(SalesTarget $target)
    {
        $user = Auth::user();
        
        // Check scope for non-admin users
        if (!$user->isAdmin()) {
            $scope = $user->scope();
            
            // Check region permission
            if (!empty($scope['region_ids']) && !in_array($target->region_id, $scope['region_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned region'
                ], 403);
            }
            
            // Check channel permission
            if (!empty($scope['channel_ids']) && !in_array($target->channel_id, $scope['channel_ids'])) {
                return response()->json([
                    'message' => 'Access denied - not your assigned channel'
                ], 403);
            }
            
            // Check classification permission
            if (isset($scope['classification'])) {
                $salesman = $target->salesman;
                if ($salesman && $salesman->classification !== $scope['classification'] && $scope['classification'] !== 'both') {
                    return response()->json([
                        'message' => 'Access denied - not your assigned classification'
                    ], 403);
                }
            }
        }

        $target->delete();

        return response()->json([
            'message' => 'Target deleted successfully'
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'targets' => 'required|array|min:1',
            'targets.*.year' => 'required|integer|min:2020|max:2030',
            'targets.*.month' => 'required|integer|min:1|max:12',
            'targets.*.region_id' => 'required|exists:regions,id',
            'targets.*.channel_id' => 'required|exists:channels,id',
            'targets.*.salesman_id' => 'required|exists:salesmen,id',
            'targets.*.supplier_id' => 'required|exists:suppliers,id',
            'targets.*.category_id' => 'required|exists:categories,id',
            'targets.*.target_amount' => 'required|numeric|min:0',
            'targets.*.notes' => 'nullable|string',
        ]);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($request->targets as $index => $targetData) {
            try {
                // Apply user scope for non-admin users
                if (!$user->isAdmin()) {
                    $scope = $user->scope();
                    
                    // Check region permission
                    if (!empty($scope['region_ids']) && !in_array($targetData['region_id'], $scope['region_ids'])) {
                        $errors[] = "Row " . ($index + 1) . ": Access denied - not your assigned region";
                        continue;
                    }
                    
                    // Check channel permission
                    if (!empty($scope['channel_ids']) && !in_array($targetData['channel_id'], $scope['channel_ids'])) {
                        $errors[] = "Row " . ($index + 1) . ": Access denied - not your assigned channel";
                        continue;
                    }
                    
                    // Check classification permission
                    if (isset($scope['classification'])) {
                        $salesman = \App\Models\Salesman::find($targetData['salesman_id']);
                        if ($salesman && $salesman->classification !== $scope['classification'] && $scope['classification'] !== 'both') {
                            $errors[] = "Row " . ($index + 1) . ": Access denied - not your assigned classification";
                            continue;
                        }
                    }
                }

                // Check if period is open
                $period = ActiveMonthYear::where('year', $targetData['year'])
                                        ->where('month', $targetData['month'])
                                        ->first();

                if (!$period || !$period->is_open) {
                    $errors[] = "Row " . ($index + 1) . ": Period is not open";
                    continue;
                }

                $target = SalesTarget::updateOrCreate(
                    [
                        'year' => $targetData['year'],
                        'month' => $targetData['month'],
                        'salesman_id' => $targetData['salesman_id'],
                        'supplier_id' => $targetData['supplier_id'],
                        'category_id' => $targetData['category_id'],
                    ],
                    $targetData
                );

                if ($target->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Bulk operation completed",
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors
        ]);
    }

    public function getMatrix(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'region_id' => 'nullable|exists:regions,id',
            'channel_id' => 'nullable|exists:channels,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:categories,id',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'classification' => 'nullable|in:food,non_food,both',
        ]);

        $user = Auth::user();
        
        // Create cache key based on request parameters and user
        $cacheKey = 'matrix_' . md5(serialize([
            'user_id' => $user->id,
            'year' => $request->year,
            'month' => $request->month,
            'region_id' => $request->region_id,
            'channel_id' => $request->channel_id,
            'supplier_id' => $request->supplier_id,
            'category_id' => $request->category_id,
            'salesman_id' => $request->salesman_id,
            'classification' => $request->classification,
        ]));
        
        // Try to get cached response (cache for 5 minutes)
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            $cachedResponse['meta']['from_cache'] = true;
            return response()->json($cachedResponse);
        }
        
        // Check period status first
        $period = ActiveMonthYear::where('year', $request->year)
                                ->where('month', $request->month)
                                ->first();
        $isPeriodOpen = $period ? $period->is_open : false;
        
        // Apply user scope restrictions for non-admin users
        $userScope = null;
        if (!$user->isAdmin()) {
            try {
                $userScope = $user->scope();
                
                // If user has no scope permissions, return empty data instead of error
                if (!$userScope || (
                    empty($userScope['region_ids']) && 
                    empty($userScope['channel_ids']) && 
                    empty($userScope['classifications'])
                )) {
                    $emptyResponse = [
                        'data' => [
                            'salesmen' => [],
                            'suppliers' => [],
                            'targets' => [],
                            'is_period_open' => $isPeriodOpen
                        ],
                        'meta' => [
                            'execution_time_ms' => 0,
                            'salesmen_count' => 0,
                            'suppliers_count' => 0,
                            'targets_count' => 0,
                            'from_cache' => false
                        ]
                    ];
                    
                    // Cache empty response for 1 minute
                    Cache::put($cacheKey, $emptyResponse, 60);
                    return response()->json($emptyResponse);
                }
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Error getting user scope for user ' . $user->id . ': ' . $e->getMessage());
                
                // Return error response with details
                return response()->json([
                    'message' => 'User permission error: ' . $e->getMessage()
                ], 500);
            }
        }

        // Start measuring performance
        $startTime = microtime(true);
        
        // **OPTIMIZED QUERY 1: Get Salesmen with single optimized query**
        $salesmenQuery = DB::table('salesmen')
            ->select([
                'salesmen.id as salesman_id',
                'salesmen.salesman_code',
                'salesmen.name as salesman_name',
                'salesmen.region_id',
                'salesmen.channel_id',
                'regions.name as region_name',
                'channels.name as channel_name'
            ])
            ->leftJoin('regions', 'salesmen.region_id', '=', 'regions.id')
            ->leftJoin('channels', 'salesmen.channel_id', '=', 'channels.id')
            ->where('salesmen.is_active', true)
            ->where('regions.is_active', true)
            ->where('channels.is_active', true);
        
        // Apply filters
        if ($request->filled('region_id')) {
            $salesmenQuery->where('salesmen.region_id', $request->region_id);
        }
        if ($request->filled('channel_id')) {
            $salesmenQuery->where('salesmen.channel_id', $request->channel_id);
        }
        if ($request->filled('salesman_id')) {
            $salesmenQuery->where('salesmen.id', $request->salesman_id);
        }

        // Apply user scope filters for non-admin users
        if ($userScope) {
            // Filter by user's assigned regions
            if (!empty($userScope['region_ids'])) {
                $salesmenQuery->whereIn('salesmen.region_id', $userScope['region_ids']);
            }
            
            // Filter by user's assigned channels
            if (!empty($userScope['channel_ids'])) {
                $salesmenQuery->whereIn('salesmen.channel_id', $userScope['channel_ids']);
            }
        }

        // **OPTIMIZED QUERY 2: Get Suppliers and Categories with single optimized query**
        $suppliersQuery = DB::table('suppliers')
            ->select([
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.classification as supplier_classification',
                'categories.id as category_id',
                'categories.name as category_name'
            ])
            ->join('categories', 'suppliers.id', '=', 'categories.supplier_id')
            ->where('suppliers.is_active', true)
            ->where('categories.is_active', true);

        if ($request->filled('supplier_id')) {
            $suppliersQuery->where('suppliers.id', $request->supplier_id);
        }
        if ($request->filled('category_id')) {
            $suppliersQuery->where('categories.id', $request->category_id);
        }
        if ($request->filled('classification')) {
            $suppliersQuery->where('suppliers.classification', $request->classification);
        }

        // Apply user scope filters to suppliers for non-admin users
        if ($userScope && !empty($userScope['classifications'])) {
            $suppliersQuery->whereIn('suppliers.classification', $userScope['classifications']);
        }

        // **OPTIMIZED QUERY 3: Get existing targets with single optimized query**
        $targetsQuery = DB::table('sales_targets')
            ->select(['salesman_id', 'supplier_id', 'category_id', 'target_amount'])
            ->where('year', $request->year)
            ->where('month', $request->month);
            
        if ($request->filled('salesman_id')) {
            $targetsQuery->where('salesman_id', $request->salesman_id);
        }
        if ($request->filled('supplier_id')) {
            $targetsQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('category_id')) {
            $targetsQuery->where('category_id', $request->category_id);
        }

        // Execute all queries efficiently
        $salesmen = $salesmenQuery->orderBy('salesmen.name')->get();
        $suppliers = $suppliersQuery->orderBy('suppliers.name')->orderBy('categories.name')->get();
        $targets = $targetsQuery->get();
        
        // **OPTIMIZED QUERY 4: Get classifications for salesmen in batch**
        $salesmenIds = $salesmen->pluck('salesman_id')->toArray();
        $classifications = [];
        if (!empty($salesmenIds)) {
            $classificationData = DB::table('salesman_classifications')
                ->select(['salesman_id', 'classification'])
                ->whereIn('salesman_id', $salesmenIds)
                ->get()
                ->groupBy('salesman_id');
            
            foreach ($classificationData as $salesmanId => $classRows) {
                $classifications[$salesmanId] = $classRows->pluck('classification')->toArray();
            }
        }

        // Apply classification filters if needed
        if ($request->filled('classification') || ($userScope && !empty($userScope['classifications']))) {
            $allowedClassifications = [];
            
            if ($request->filled('classification')) {
                $allowedClassifications[] = $request->classification;
            }
            
            if ($userScope && !empty($userScope['classifications'])) {
                $allowedClassifications = array_merge($allowedClassifications, $userScope['classifications']);
            }
            
            if (!empty($allowedClassifications)) {
                $salesmen = $salesmen->filter(function($salesman) use ($classifications, $allowedClassifications) {
                    $salesmanClassifications = $classifications[$salesman->salesman_id] ?? [];
                    return !empty(array_intersect($salesmanClassifications, $allowedClassifications));
                });
            }
        }

        // Apply user scope filters to targets for non-admin users
        if ($userScope) {
            $allowedSalesmenIds = $salesmen->pluck('salesman_id')->toArray();
            $allowedSupplierIds = $suppliers->where(function($supplier) use ($userScope) {
                return empty($userScope['classifications']) || 
                       in_array($supplier->supplier_classification, $userScope['classifications']);
            })->pluck('supplier_id')->toArray();
            
            $targets = $targets->filter(function($target) use ($allowedSalesmenIds, $allowedSupplierIds) {
                return in_array($target->salesman_id, $allowedSalesmenIds) && 
                       in_array($target->supplier_id, $allowedSupplierIds);
            });
        }

        // Transform salesmen data
        $salesmenData = $salesmen->map(function($s) use ($classifications) {
            return [
                'salesman_id' => $s->salesman_id,
                'salesman_code' => $s->salesman_code,
                'salesman_name' => $s->salesman_name,
                'salesman_classifications' => $classifications[$s->salesman_id] ?? [],
                'region_name' => $s->region_name ?: 'N/A',
                'channel_name' => $s->channel_name ?: 'N/A'
            ];
        })->values();

        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

        $response = [
            'data' => [
                'salesmen' => $salesmenData,
                'suppliers' => $suppliers->values(),
                'targets' => $targets->values(),
                'is_period_open' => $isPeriodOpen
            ],
            'meta' => [
                'execution_time_ms' => $executionTime,
                'salesmen_count' => $salesmenData->count(),
                'suppliers_count' => $suppliers->count(),
                'targets_count' => $targets->count(),
                'from_cache' => false
            ]
        ];
        
        // Cache the response for 5 minutes (300 seconds)
        Cache::put($cacheKey, $response, 300);
        
        return response()->json($response);
    }

    public function bulkSave(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'targets' => 'required|array|min:1',
            'targets.*.salesman_id' => 'required|exists:salesmen,id',
            'targets.*.supplier_id' => 'required|exists:suppliers,id',
            'targets.*.category_id' => 'required|exists:categories,id',
            'targets.*.target_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        // Check if period is open
        $period = ActiveMonthYear::where('year', $request->year)
                                ->where('month', $request->month)
                                ->first();

        if (!$period || !$period->is_open) {
            return response()->json([
                'success' => false,
                'message' => 'This period is not open for target entry'
            ], 422);
        }

        $savedCount = 0;
        $errors = [];

        foreach ($request->targets as $targetData) {
            try {
                // Get salesman details for scope checking
                $salesman = \App\Models\Salesman::find($targetData['salesman_id']);
                
                // Apply user scope for non-admin users
                if (!$user->isAdmin()) {
                    $scope = $user->scope();
                    
                    // Check region permission
                    if (!empty($scope['region_ids']) && !in_array($salesman->region_id, $scope['region_ids'])) {
                        continue; // Skip this target
                    }
                    
                    // Check channel permission
                    if (!empty($scope['channel_ids']) && !in_array($salesman->channel_id, $scope['channel_ids'])) {
                        continue; // Skip this target
                    }
                    
                    // Check classification permission
                    if (isset($scope['classification']) && $scope['classification'] !== 'both') {
                        if ($salesman->classification !== $scope['classification'] && $salesman->classification !== 'both') {
                            continue; // Skip this target
                        }
                    }
                    
                    // Check supplier classification permission
                    $supplier = \App\Models\Supplier::find($targetData['supplier_id']);
                    if (isset($scope['classification']) && $scope['classification'] !== 'both') {
                        if ($supplier && $supplier->classification !== $scope['classification']) {
                            continue; // Skip this target
                        }
                    }
                }

                SalesTarget::updateOrCreate(
                    [
                        'year' => $request->year,
                        'month' => $request->month,
                        'salesman_id' => $targetData['salesman_id'],
                        'supplier_id' => $targetData['supplier_id'],
                        'category_id' => $targetData['category_id'],
                    ],
                    [
                        'region_id' => $salesman->region_id,
                        'channel_id' => $salesman->channel_id,
                        'target_amount' => $targetData['target_amount'],
                        'notes' => $targetData['notes'] ?? null,
                    ]
                );

                $savedCount++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'saved_count' => $savedCount,
            'message' => "Successfully saved {$savedCount} targets",
            'errors' => $errors
        ]);
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'region_id' => 'nullable|exists:regions,id',
            'channel_id' => 'nullable|exists:channels,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'nullable|exists:categories,id',
            'salesman_id' => 'nullable|exists:salesmen,id',
            'classification' => 'nullable|in:food,non_food,both',
        ]);

        $user = Auth::user();
        
        // Apply user scope restrictions for non-admin users
        $userScope = null;
        if (!$user->isAdmin()) {
            $userScope = $user->scope();
        }
        
        // Start with salesmen query to get filtered salesmen
        $salesmenQuery = \App\Models\Salesman::query();
        
        if ($request->filled('region_id')) {
            $salesmenQuery->where('region_id', $request->region_id);
        }
        if ($request->filled('channel_id')) {
            $salesmenQuery->where('channel_id', $request->channel_id);
        }
        if ($request->filled('salesman_id')) {
            $salesmenQuery->where('id', $request->salesman_id);
        }
        if ($request->filled('classification')) {
            $salesmenQuery->whereHas('classifications', function($q) use ($request) {
                $q->where('classification', $request->classification);
            });
        }
        
        // Apply user scope filters for non-admin users
        if ($userScope) {
            // Filter by user's assigned regions
            if (!empty($userScope['region_ids'])) {
                $salesmenQuery->whereIn('region_id', $userScope['region_ids']);
            }
            
            // Filter by user's assigned channels
            if (!empty($userScope['channel_ids'])) {
                $salesmenQuery->whereIn('channel_id', $userScope['channel_ids']);
            }
            
            // Filter by user's assigned classifications
            if (!empty($userScope['classifications'])) {
                $salesmenQuery->whereHas('classifications', function($q) use ($userScope) {
                    $q->whereIn('classification', $userScope['classifications']);
                });
            }
        }
        
        // Get filtered salesman IDs
        $salesmanIds = $salesmenQuery->pluck('id');

        // Build targets query with all filters
        $query = SalesTarget::with([
            'region', 'channel', 'salesman', 'supplier', 'category'
        ])->where('year', $request->year)
          ->where('month', $request->month)
          ->whereIn('salesman_id', $salesmanIds);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply user scope filters to targets for non-admin users
        if ($userScope) {
            // Filter targets by user's assigned suppliers (classifications)
            if (!empty($userScope['classifications'])) {
                $query->whereHas('supplier', function($q) use ($userScope) {
                    $q->whereIn('classification', $userScope['classifications']);
                });
            }
        }

        $targets = $query->get();

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Classification', 'Status', 'Year', 'Month', 'Region', 'Channel', 
            'Supplier', 'Category', 'RouteCode', 'Salesman Code', 'Employee Code', 'Salesmen Name', 'Amount'
        ];

        foreach ($targets as $target) {
            // Get supplier classification for this target
            $supplierClassification = $target->supplier->classification ?? '';
            
            $csvData[] = [
                $supplierClassification,
                'Active',
                $target->year,
                str_pad($target->month, 2, '0', STR_PAD_LEFT), // Format as 01, 02, etc.
                $target->region->name ?? '',
                $target->channel->name ?? '',
                $target->supplier->name ?? '',
                $target->category->name ?? '',
                '', // RouteCode - empty as in original CSV
                $target->salesman->salesman_code ?? '',
                $target->salesman->employee_code ?? '',
                $target->salesman->name ?? '',
                number_format($target->target_amount, 2, '.', '') // Format as 1000.00
            ];
        }

        $monthName = date('M', mktime(0, 0, 0, $request->month, 1));
        $filename = "targets_{$request->year}_{$monthName}.csv";
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function upload(Request $request)
    {
        $user = Auth::user();
        
        // Get user scope for permission checks
        $userScope = null;
        if (!$user->isAdmin()) {
            $userScope = $user->scope();
        }

        $request->validate([
            'file' => 'required|file|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:10240', // 10MB max
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $file = $request->file('file');
            $year = $request->input('year');
            $month = $request->input('month');
            
            // Check if period is open for uploads
            $period = ActiveMonthYear::where('year', $year)
                                    ->where('month', $month)
                                    ->first();

            if (!$period || !$period->is_open) {
                return response()->json([
                    'message' => 'Upload not allowed. The period ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . ' is closed for target updates.'
                ], 422);
            }
            
            $results = [
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'errors' => 0,
                'error_details' => []
            ];

            // Try to detect the encoding and convert if necessary
            $content = file_get_contents($file->getPathname());
            $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            // Remove BOM if present but keep line endings
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            
            // Normalize line endings to \n
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            
            // Create a temporary file with the cleaned content
            $tempFile = tmpfile();
            fwrite($tempFile, $content);
            fseek($tempFile, 0);

            // Get headers from first line
            $headers = fgetcsv($tempFile);
            if ($headers === false) {
                fclose($tempFile);
                return response()->json(['message' => 'File is empty or could not be read.'], 400);
            }

            // Clean and normalize headers - more permissive now
            $headers = array_map(function($header) {
                // Remove any non-printable characters
                $header = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $header);
                // Convert to lowercase and trim
                $header = strtolower(trim($header));
                // Replace multiple spaces with single space
                $header = preg_replace('/\s+/', ' ', $header);
                return $header;
            }, $headers);

            // Expected headers mapping with more variations
            $headerMap = [
                'salesman_code' => [
                    'salesman code', 'salesmancode', 'sal code', 'salcode', 'scode',
                    'salesman id', 'salesmanid', 'sal id', 'salid'
                ],
                'employee_code' => [
                    'employee code', 'employeecode', 'emp code', 'empcode', 'ecode',
                    'employee id', 'employeeid', 'emp id', 'empid', 'staff id', 'staffid'
                ],
                'salesman_name' => [
                    'salesman name', 'salesmanname', 'name', 'salesman',
                    'employee name', 'employeename', 'staff name', 'staffname'
                ],
                'region' => [
                    'region', 'region name', 'regionname', 'area', 'area name', 'areaname',
                    'territory', 'territory name', 'territoryname'
                ],
                'channel' => [
                    'channel', 'channel name', 'channelname', 'sales channel',
                    'distribution channel', 'dist channel'
                ],
                'supplier' => [
                    'supplier', 'supplier name', 'suppliername', 'vendor',
                    'vendor name', 'vendorname'
                ],
                'category' => [
                    'category', 'category name', 'categoryname', 'product category',
                    'product line', 'productline'
                ],
                'amount' => [
                    'amount', 'target amount', 'targetamount', 'target',
                    'value', 'target value', 'sales target', 'quota'
                ]
            ];

            // Find header positions with more flexible matching
            $positions = [];
            foreach ($headerMap as $field => $variations) {
                foreach ($variations as $variation) {
                    // Try exact match first
                    $pos = array_search($variation, $headers);
                    if ($pos !== false) {
                        $positions[$field] = $pos;
                        break;
                    }
                    
                    // Try partial match if exact match fails
                    foreach ($headers as $index => $header) {
                        if (strpos($header, $variation) !== false) {
                            $positions[$field] = $index;
                            break 2;
                        }
                    }
                }
            }


            // Check required headers with better error messages
            // Only employee_code is required for salesman identification (not salesman_code)
            $requiredFields = ['employee_code', 'supplier', 'category', 'amount'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($positions[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $expectedVariations = [];
                foreach ($missingFields as $field) {
                    $expectedVariations[$field] = $headerMap[$field];
                }
                
                    return response()->json([
                    'message' => "Required column(s) not found in file headers: " . implode(", ", $missingFields),
                    'found_headers' => $headers,
                    'expected_variations' => $expectedVariations
                    ], 400);
            }

            // Process each data row
            $lineNumber = 1; // Start from 1 since headers are line 0
            while (($data = fgetcsv($tempFile)) !== false) {
                // Skip empty lines or lines with only whitespace
                if (empty(array_filter($data, function($cell) { return trim($cell) !== ''; }))) {
                    continue;
                }

                $results['processed']++;
                
                try {
                    // Clean and extract data based on positions
                    $salesmanCode = isset($positions['salesman_code']) ? trim($data[$positions['salesman_code']] ?? '') : '';
                    $employeeCode = isset($positions['employee_code']) ? trim($data[$positions['employee_code']] ?? '') : '';
                    $supplierName = isset($positions['supplier']) ? trim($data[$positions['supplier']] ?? '') : '';
                    $categoryName = isset($positions['category']) ? trim($data[$positions['category']] ?? '') : '';
                    $amount = isset($positions['amount']) ? trim($data[$positions['amount']] ?? '') : '';
                    
                    // Clean up codes - remove any special characters but keep alphanumeric and common separators
                    $salesmanCode = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $salesmanCode);
                    $employeeCode = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $employeeCode);
                    
                    // Skip row if required data is missing
                    if (empty($employeeCode)) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Missing employee code - skipped";
                        $lineNumber++;
                        continue;
                    }
                    
                    if (empty($supplierName)) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Missing supplier name - skipped";
                        $lineNumber++;
                        continue;
                    }
                    
                    if (empty($categoryName)) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Missing category name - skipped";
                        $lineNumber++;
                        continue;
                    }
                    
                    if (empty($amount)) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Missing amount - skipped";
                        $lineNumber++;
                        continue;
                    }

                    // Validate and parse amount
                    $amount = str_replace([',', ' '], ['', ''], $amount); // Remove commas and spaces
                    if (!is_numeric($amount)) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Invalid amount format '{$amount}' - skipped";
                        $lineNumber++;
                        continue;
                    }
                    $amount = floatval($amount);
                    
                    if ($amount < 0) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Negative amount '{$amount}' - skipped";
                        $lineNumber++;
                        continue;
                    }

                    // Find salesman by employee_code (primary method) - load relationships for permission checks
                    $salesman = \App\Models\Salesman::with(['region', 'channel', 'classifications'])
                        ->where('employee_code', $employeeCode)->first();
                    
                    // Fallback: try salesman_code if provided and employee_code didn't work
                    if (!$salesman && !empty($salesmanCode)) {
                        $salesman = \App\Models\Salesman::with(['region', 'channel', 'classifications'])
                            ->where('salesman_code', $salesmanCode)->first();
                    }
                    
                    if (!$salesman) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Salesman not found for employee code: {$employeeCode} - skipped";
                        $lineNumber++;
                        continue;
                    }

                    // Check user permissions for this salesman (if not admin)
                    if ($userScope) {
                        // Check region permission
                        if (!empty($userScope['region_ids']) && !in_array($salesman->region_id, $userScope['region_ids'])) {
                            $results['errors']++;
                            $regionName = $salesman->region ? $salesman->region->name : 'Unknown';
                            $results['error_details'][] = "Row " . ($lineNumber + 1) . ": No permission for salesman's region '{$regionName}' - skipped";
                            $lineNumber++;
                            continue;
                        }
                        
                        // Check channel permission
                        if (!empty($userScope['channel_ids']) && !in_array($salesman->channel_id, $userScope['channel_ids'])) {
                            $results['errors']++;
                            $channelName = $salesman->channel ? $salesman->channel->name : 'Unknown';
                            $results['error_details'][] = "Row " . ($lineNumber + 1) . ": No permission for salesman's channel '{$channelName}' - skipped";
                            $lineNumber++;
                            continue;
                        }
                        
                        // Check salesman classification permission
                        if (!empty($userScope['classifications'])) {
                            $salesmanClassifications = $salesman->classifications->pluck('classification')->toArray();
                            $hasCommonClassification = !empty(array_intersect($salesmanClassifications, $userScope['classifications']));
                            if (!$hasCommonClassification) {
                                $results['errors']++;
                                $results['error_details'][] = "Row " . ($lineNumber + 1) . ": No permission for salesman's classifications [" . implode(', ', $salesmanClassifications) . "] - skipped";
                                $lineNumber++;
                                continue;
                            }
                        }
                    }

                    // Find supplier - skip if not found in master data
                    $supplier = \App\Models\Supplier::where('name', 'LIKE', '%' . $supplierName . '%')->first();
                    if (!$supplier) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Supplier '{$supplierName}' not found in master data - skipped";
                        $lineNumber++;
                        continue;
                    }

                    // Check user permissions for this supplier (if not admin)
                    if ($userScope) {
                        // Check supplier classification permission
                        if (!empty($userScope['classifications']) && !in_array($supplier->classification, $userScope['classifications'])) {
                            $results['errors']++;
                            $results['error_details'][] = "Row " . ($lineNumber + 1) . ": No permission for supplier classification '{$supplier->classification}' - skipped";
                            $lineNumber++;
                            continue;
                        }
                        
                        // Additional check: ensure salesman and supplier classifications are compatible
                        $salesmanClassifications = $salesman->classifications->pluck('classification')->toArray();
                        if (!in_array($supplier->classification, $salesmanClassifications)) {
                            $results['errors']++;
                            $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Salesman classifications [" . implode(', ', $salesmanClassifications) . "] not compatible with supplier classification '{$supplier->classification}' - skipped";
                            $lineNumber++;
                            continue;
                        }
                    }

                    // Find category - skip if not found in master data
                    $category = \App\Models\Category::where('supplier_id', $supplier->id)
                                                  ->where('name', 'LIKE', '%' . $categoryName . '%')
                                                  ->first();
                    if (!$category) {
                        $results['errors']++;
                        $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Category '{$categoryName}' not found for supplier '{$supplierName}' - skipped";
                        $lineNumber++;
                        continue;
                    }

                    // All master data found - create or update target (only amount is updated)
                    $target = \App\Models\SalesTarget::updateOrCreate([
                        'salesman_id' => $salesman->id,
                        'supplier_id' => $supplier->id,
                        'category_id' => $category->id,
                        'year' => $year,
                        'month' => $month,
                    ], [
                        'region_id' => $salesman->region_id,  // These come from existing master data
                        'channel_id' => $salesman->channel_id, // These come from existing master data
                        'classification' => $supplier->classification, // Get from supplier
                        'target_amount' => $amount,  // Only this field is updated from CSV
                    ]);

                    if ($target->wasRecentlyCreated) {
                        $results['created']++;
                    } else {
                        $results['updated']++;
                    }

                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['error_details'][] = "Row " . ($lineNumber + 1) . ": Unexpected error - " . $e->getMessage();
                }
                $lineNumber++;
            }

            fclose($tempFile);
            
            // Add summary message
            $totalSuccess = $results['created'] + $results['updated'];
            $summaryMessage = "Upload completed: {$totalSuccess} targets processed successfully";
            if ($results['errors'] > 0) {
                $summaryMessage .= ", {$results['errors']} rows skipped due to invalid/non-matching data";
            }
            $results['message'] = $summaryMessage;

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $user = Auth::user();
        
        // Get user scope for filtering template data
        $userScope = null;
        if (!$user->isAdmin()) {
            $userScope = $user->scope();
        }

        try {
            // Get sample salesmen data from database - filtered by user scope
            $salesmenQuery = \App\Models\Salesman::with(['region', 'channel', 'classifications']);
            
            // Apply user scope filters for non-admin users
            if ($userScope) {
                // Filter by user's assigned regions
                if (!empty($userScope['region_ids'])) {
                    $salesmenQuery->whereIn('region_id', $userScope['region_ids']);
                }
                
                // Filter by user's assigned channels
                if (!empty($userScope['channel_ids'])) {
                    $salesmenQuery->whereIn('channel_id', $userScope['channel_ids']);
                }
                
                // Filter by user's assigned classifications
                if (!empty($userScope['classifications'])) {
                    $salesmenQuery->whereHas('classifications', function($q) use ($userScope) {
                        $q->whereIn('classification', $userScope['classifications']);
                    });
                }
            }
            
            $salesmen = $salesmenQuery->take(10)->get();
            
            if ($salesmen->isEmpty()) {
                // Fallback to static sample data if no salesmen in database
                $csvContent = [
                    'Classification,Status,Year,Month,Region,Channel,Supplier,Category,RouteCode,Salesman Code,Employee Code,Salesmen Name,Amount',
                    'food,Active,2025,08,North Region,Retail,AGUS,FLOUR,,SAL0001,EMP001,John Doe,1000.00',
                    'food,Active,2025,08,South Region,Wholesale,KORHAN,DAIRY,,SAL0002,EMP002,Jane Smith,1500.00',
                    'non_food,Active,2025,08,East Region,Retail,FONTERRA,CHEESE,,SAL0003,EMP003,Mike Johnson,1200.00',
                    'non_food,Active,2025,08,West Region,Wholesale,HOBBY,SNACKS,,SAL0004,EMP004,Sarah Wilson,800.00',
                    'food,Active,2025,08,Central Region,Retail,MAZRAA,BEVERAGES,,SAL0005,EMP005,David Brown,2000.00'
                ];
            } else {
                // Create CSV with real data - match export format exactly
                $csvContent = ['Classification,Status,Year,Month,Region,Channel,Supplier,Category,RouteCode,Salesman Code,Employee Code,Salesmen Name,Amount'];
                
                // Get some real suppliers and categories for the template - filtered by user scope
                $suppliersQuery = \App\Models\Supplier::with('categories');
                
                // Apply user scope filters for non-admin users
                if ($userScope && !empty($userScope['classifications'])) {
                    $suppliersQuery->whereIn('classification', $userScope['classifications']);
                }
                
                $suppliers = $suppliersQuery->take(3)->get();
                $sampleSupplierCategory = [];
                foreach ($suppliers as $supplier) {
                    foreach ($supplier->categories->take(2) as $category) {
                        $sampleSupplierCategory[] = [
                            'supplier' => $supplier->name,
                            'category' => $category->name,
                            'classification' => $supplier->classification
                        ];
                    }
                }
                
                // If no suppliers/categories, use defaults
                if (empty($sampleSupplierCategory)) {
                    $sampleSupplierCategory = [
                        ['supplier' => 'SAMPLE_SUPPLIER', 'category' => 'SAMPLE_CATEGORY', 'classification' => 'food']
                    ];
                }
                
                foreach ($salesmen as $index => $salesman) {
                    $supplierCategory = $sampleSupplierCategory[$index % count($sampleSupplierCategory)];
                    $csvContent[] = sprintf(
                        '%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s',
                        $supplierCategory['classification'], // Classification (from supplier)
                        'Active', // Status
                        '2025', // Year
                        '08', // Month
                        $salesman->region ? $salesman->region->name : 'Sample Region', // Region
                        $salesman->channel ? $salesman->channel->name : 'Sample Channel', // Channel
                        $supplierCategory['supplier'], // Supplier
                        $supplierCategory['category'], // Category
                        '', // RouteCode (empty)
                        $salesman->salesman_code ?? 'SAL' . str_pad($salesman->id, 4, '0', STR_PAD_LEFT), // Salesman Code
                        $salesman->employee_code ?? '', // Employee Code
                        $salesman->name, // Salesmen Name
                        '1000.00' // Amount
                    );
                }
            }
            
            $filename = 'targets_upload_template_' . date('Y-m-d') . '.csv';
            
            $callback = function() use ($csvContent) {
                $file = fopen('php://output', 'w');
                foreach ($csvContent as $line) {
                    fputcsv($file, str_getcsv($line));
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Template download failed: ' . $e->getMessage()
            ], 500);
        }
    }
} 
