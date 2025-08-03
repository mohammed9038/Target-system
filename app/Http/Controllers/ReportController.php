<?php

namespace App\Http\Controllers;

use App\Models\SalesTarget;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;
use Illuminate\Http\Request;
use App\Exports\TargetsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $regions = Region::all();
        $channels = Channel::all();
        $suppliers = Supplier::all();
        $categories = Category::all();
        $salesmen = Salesman::all();
        
        return view('reports.index', compact('regions', 'channels', 'suppliers', 'categories', 'salesmen'));
    }

    public function summary(Request $request)
    {
        $query = SalesTarget::with(['salesman.region', 'salesman.channel', 'supplier', 'category']);

        // Apply filters
        if ($request->filled('region_id')) {
            $query->whereHas('salesman', function ($q) use ($request) {
                $q->where('region_id', $request->region_id);
            });
        }

        if ($request->filled('channel_id')) {
            $query->whereHas('salesman', function ($q) use ($request) {
                $q->where('channel_id', $request->channel_id);
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('salesman_id')) {
            $query->where('salesman_id', $request->salesman_id);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $targets = $query->get();

        $summary = [
            'total_targets' => $targets->count(),
            'total_amount' => $targets->sum('target_amount'),
            'average_amount' => $targets->avg('target_amount'),
            'by_region' => $targets->groupBy('salesman.region.name')->map->sum('target_amount'),
            'by_channel' => $targets->groupBy('salesman.channel.name')->map->sum('target_amount'),
            'by_supplier' => $targets->groupBy('supplier.name')->map->sum('target_amount'),
        ];

        return view('reports.summary', compact('targets', 'summary'));
    }
} 