<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TargetsExport;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        $user = Auth::user();
        $query = SalesTarget::with(['region', 'channel', 'salesman', 'supplier', 'category']);

        // Apply user scope
        if (!$user->isAdmin()) {
            $userScope = $user->scope();
            
            if ($userScope) {
                // Apply region scope
                if (!empty($userScope['region_ids'])) {
                    $query->whereHas('salesman', function($q) use ($userScope) {
                        $q->whereIn('region_id', $userScope['region_ids']);
                    });
                }
                
                // Apply channel scope
                if (!empty($userScope['channel_ids'])) {
                    $query->whereHas('salesman', function($q) use ($userScope) {
                        $q->whereIn('channel_id', $userScope['channel_ids']);
                    });
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

        $groupBy = $request->get('group_by', 'supplier');

        switch ($groupBy) {
            case 'supplier':
                $summary = $query->selectRaw('
                    suppliers.name as supplier_name,
                    suppliers.supplier_code,
                    COUNT(*) as target_count,
                    SUM(target_amount) as total_amount
                ')
                ->join('suppliers', 'sales_targets.supplier_id', '=', 'suppliers.id')
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.supplier_code')
                ->orderBy('total_amount', 'desc')
                ->get();
                break;

            case 'region':
                $summary = $query->selectRaw('
                    regions.name as region_name,
                    regions.region_code,
                    COUNT(*) as target_count,
                    SUM(target_amount) as total_amount
                ')
                ->join('regions', 'sales_targets.region_id', '=', 'regions.id')
                ->groupBy('regions.id', 'regions.name', 'regions.region_code')
                ->orderBy('total_amount', 'desc')
                ->get();
                break;

            case 'channel':
                $summary = $query->selectRaw('
                    channels.name as channel_name,
                    channels.channel_code,
                    COUNT(*) as target_count,
                    SUM(target_amount) as total_amount
                ')
                ->join('channels', 'sales_targets.channel_id', '=', 'channels.id')
                ->groupBy('channels.id', 'channels.name', 'channels.channel_code')
                ->orderBy('total_amount', 'desc')
                ->get();
                break;

            case 'salesman':
                $summary = $query->selectRaw('
                    salesmen.name as salesman_name,
                    salesmen.salesman_code,
                    salesmen.employee_code,
                    COUNT(*) as target_count,
                    SUM(target_amount) as total_amount
                ')
                ->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
                ->groupBy('salesmen.id', 'salesmen.name', 'salesmen.salesman_code', 'salesmen.employee_code')
                ->orderBy('total_amount', 'desc')
                ->get();
                break;

            default:
                $summary = $query->selectRaw('
                    suppliers.name as supplier_name,
                    suppliers.supplier_code,
                    COUNT(*) as target_count,
                    SUM(target_amount) as total_amount
                ')
                ->join('suppliers', 'sales_targets.supplier_id', '=', 'suppliers.id')
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.supplier_code')
                ->orderBy('total_amount', 'desc')
                ->get();
        }

        return response()->json([
            'data' => $summary,
            'group_by' => $groupBy
        ]);
    }
} 