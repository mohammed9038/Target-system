<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\Region;
use App\Models\Channel;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalesmanController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    public function index(Request $request)
    {
        $query = Salesman::with(['region', 'channel', 'classifications']);
        
        // Handle search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('region', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('channel', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        // Handle sorting
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field
        $allowedSorts = ['id', 'name', 'employee_code', 'region_name', 'channel_name', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        // Handle relationship-based sorting
        if ($sortBy === 'region_name') {
            $query->join('regions', 'salesmen.region_id', '=', 'regions.id')
                  ->orderBy('regions.name', $sortDirection)
                  ->select('salesmen.*');
        } elseif ($sortBy === 'channel_name') {
            $query->join('channels', 'salesmen.channel_id', '=', 'channels.id')
                  ->orderBy('channels.name', $sortDirection)
                  ->select('salesmen.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $salesmen = $query->paginate(15)->appends($request->all());
        
        return view('salesmen.index', compact('salesmen'));
    }

    public function create()
    {
        $regions = Region::all();
        $channels = Channel::all();
        return view('salesmen.create', compact('regions', 'channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'channel_id' => 'required|exists:channels,id',
            'classifications' => 'required|array|min:1',
            'classifications.*' => 'in:food,non_food',
        ]);

        // Remove classifications from validated data for salesman creation
        $classifications = $validated['classifications'];
        unset($validated['classifications']);

        $salesman = Salesman::create($validated);
        
        // Add classifications
        foreach ($classifications as $classification) {
            \App\Models\SalesmanClassification::create([
                'salesman_id' => $salesman->id,
                'classification' => $classification
            ]);
        }
        return redirect()->route('salesmen.index')->with('success', 'Salesman created successfully.');
    }

    public function edit(Salesman $salesman)
    {
        $regions = Region::all();
        $channels = Channel::all();
        return view('salesmen.edit', compact('salesman', 'regions', 'channels'));
    }

    public function update(Request $request, Salesman $salesman)
    {
        $validated = $request->validate([
            'employee_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'channel_id' => 'required|exists:channels,id',
            'classifications' => 'required|array|min:1',
            'classifications.*' => 'in:food,non_food',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active');

        // Remove classifications from validated data for salesman update
        $classifications = $validated['classifications'];
        unset($validated['classifications']);

        $salesman->update($validated);
        
        // Update classifications
        $salesman->classifications()->delete(); // Remove existing classifications
        foreach ($classifications as $classification) {
            \App\Models\SalesmanClassification::create([
                'salesman_id' => $salesman->id,
                'classification' => $classification
            ]);
        }
        return redirect()->route('salesmen.index')->with('success', 'Salesman updated successfully.');
    }

    public function destroy(Salesman $salesman)
    {
        // Check if salesman has any sales targets
        $targetsCount = \App\Models\SalesTarget::where('salesman_id', $salesman->id)->count();
        
        if ($targetsCount > 0) {
            return redirect()->route('salesmen.index')->with('error', "Cannot delete salesman '{$salesman->name}'. This salesman has {$targetsCount} sales target(s) assigned. Please reassign or delete the targets first.");
        }

        $salesman->delete();
        return redirect()->route('salesmen.index')->with('success', 'Salesman deleted successfully.');
    }

    /**
     * Export salesmen to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['is_active', 'name', 'region_id', 'channel_id']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportSalesmen($filters, $format);
            
            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Salesmen export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import salesmen from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'update_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing');
            
            $result = $this->masterDataService->importSalesmen($file, $updateExisting);
            
            $message = "Import completed: {$result['imported_rows']} imported, {$result['updated_rows']} updated, {$result['failed_rows']} failed";
            
            if ($result['failed_rows'] > 0) {
                $message .= ". Check logs for details.";
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Salesmen import failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('file')?->getClientOriginalName()
            ]);
            
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        try {
            $fileName = $this->masterDataService->generateTemplate('salesmen');
            $filePath = storage_path('app/temp/' . $fileName);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Template file does not exist: {$fileName}");
            }
            
            return response()->download($filePath, 'salesmen_template.xlsx')->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Template download failed', [
                'type' => 'salesmen',
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Template download failed: ' . $e->getMessage());
        }
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('salesmen.import');
    }

    /**
     * Toggle active status of a salesman
     */
    public function toggleStatus(Salesman $salesman)
    {
        try {
            $salesman->is_active = !$salesman->is_active;
            $salesman->save();
            
            $status = $salesman->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()->with('success', "Salesman '{$salesman->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Salesman status toggle failed', [
                'salesman_id' => $salesman->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle salesman status: ' . $e->getMessage());
        }
    }
}
 