<?php

namespace App\Http\Controllers;

use App\Models\SalesTarget;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;
use App\Models\ActiveMonthYear;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesTarget::with(['salesman.region', 'salesman.channel', 'supplier', 'category']);
        
        // Handle search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('year', 'like', '%' . $request->search . '%')
                  ->orWhere('month', 'like', '%' . $request->search . '%')
                  ->orWhere('target_amount', 'like', '%' . $request->search . '%')
                  ->orWhereHas('salesman', function($subQ) use ($request) {
                      $subQ->where('name', 'like', '%' . $request->search . '%')
                           ->orWhere('employee_code', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('supplier', function($subQ) use ($request) {
                      $subQ->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('category', function($subQ) use ($request) {
                      $subQ->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        // Handle sorting
        $sortBy = $request->get('sort', 'year');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort field
        $allowedSorts = [
            'year', 'month', 'target_amount', 'created_at', 'updated_at',
            'salesman_name', 'region_name', 'channel_name', 'supplier_name', 'category_name'
        ];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'year';
        }
        
        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Apply sorting
        if ($sortBy === 'year' || $sortBy === 'month') {
            $query->orderBy('year', $sortDirection)
                  ->orderBy('month', $sortDirection);
        } elseif ($sortBy === 'salesman_name') {
            $query->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
                  ->orderBy('salesmen.name', $sortDirection)
                  ->select('sales_targets.*');
        } elseif ($sortBy === 'region_name') {
            $query->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
                  ->join('regions', 'salesmen.region_id', '=', 'regions.id')
                  ->orderBy('regions.name', $sortDirection)
                  ->select('sales_targets.*');
        } elseif ($sortBy === 'channel_name') {
            $query->join('salesmen', 'sales_targets.salesman_id', '=', 'salesmen.id')
                  ->join('channels', 'salesmen.channel_id', '=', 'channels.id')
                  ->orderBy('channels.name', $sortDirection)
                  ->select('sales_targets.*');
        } elseif ($sortBy === 'supplier_name') {
            $query->join('suppliers', 'sales_targets.supplier_id', '=', 'suppliers.id')
                  ->orderBy('suppliers.name', $sortDirection)
                  ->select('sales_targets.*');
        } elseif ($sortBy === 'category_name') {
            $query->join('categories', 'sales_targets.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $sortDirection)
                  ->select('sales_targets.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $targets = $query->paginate(15)->appends($request->all());
        
        $regions = Region::all();
        $channels = Channel::all();
        $suppliers = Supplier::all();
        $categories = Category::all();
        $salesmen = Salesman::all();
        $activePeriods = ActiveMonthYear::where('is_open', true)->get();
        
        return view('targets.index', compact('targets', 'regions', 'channels', 'suppliers', 'categories', 'salesmen', 'activePeriods'));
    }

    public function create()
    {
        $regions = Region::all();
        $channels = Channel::all();
        $suppliers = Supplier::all();
        $categories = Category::all();
        $salesmen = Salesman::all();
        $activePeriods = ActiveMonthYear::where('is_open', true)->get();
        
        return view('targets.create', compact('regions', 'channels', 'suppliers', 'categories', 'salesmen', 'activePeriods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'salesman_id' => 'required|exists:salesmen,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'target_amount' => 'required|numeric|min:0',
        ]);

        // Check for existing target
        $existingTarget = SalesTarget::where([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'salesman_id' => $validated['salesman_id'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
        ])->first();

        if ($existingTarget) {
            return back()->withErrors(['target' => 'A target already exists for this combination.'])->withInput();
        }

        SalesTarget::create($validated);
        return redirect()->route('targets.index')->with('success', 'Sales target created successfully.');
    }

    public function edit(SalesTarget $target)
    {
        $regions = Region::all();
        $channels = Channel::all();
        $suppliers = Supplier::all();
        $categories = Category::all();
        $salesmen = Salesman::all();
        $activePeriods = ActiveMonthYear::where('is_open', true)->get();
        
        return view('targets.edit', compact('target', 'regions', 'channels', 'suppliers', 'categories', 'salesmen', 'activePeriods'));
    }

    public function update(Request $request, SalesTarget $target)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'salesman_id' => 'required|exists:salesmen,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'target_amount' => 'required|numeric|min:0',
        ]);

        // Check for existing target (excluding current one)
        $existingTarget = SalesTarget::where([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'salesman_id' => $validated['salesman_id'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
        ])->where('id', '!=', $target->id)->first();

        if ($existingTarget) {
            return back()->withErrors(['target' => 'A target already exists for this combination.'])->withInput();
        }

        $target->update($validated);
        return redirect()->route('targets.index')->with('success', 'Sales target updated successfully.');
    }

    public function destroy(SalesTarget $target)
    {
        $target->delete();
        return redirect()->route('targets.index')->with('success', 'Sales target deleted successfully.');
    }
} 