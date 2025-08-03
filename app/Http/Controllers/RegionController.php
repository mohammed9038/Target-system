<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegionController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    public function index(Request $request)
    {
        $query = Region::query();
        
        // Handle search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Handle sorting
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field
        $allowedSorts = ['id', 'name', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        $regions = $query->paginate(15)->appends($request->all());
        
        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        return view('regions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Region::create($validated);
        return redirect()->route('regions.index')->with('success', 'Region created successfully.');
    }

    public function show(Region $region)
    {
        // Manual fallback if route model binding fails
        if (!$region->exists) {
            $region = Region::findOrFail(request()->route('region'));
        }
        
        return view('regions.show', compact('region'));
    }

    public function edit(Region $region)
    {
        // Manual fallback if route model binding fails
        if (!$region->exists) {
            $region = Region::findOrFail(request()->route('region'));
        }
        
        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        // Manual fallback if route model binding fails
        if (!$region->exists) {
            $region = Region::findOrFail($request->route('region'));
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active');

        $region->update($validated);

        return redirect()->route('regions.index')->with('success', 'Region updated successfully.');
    }

    public function destroy(Region $region)
    {
        // Manual fallback if route model binding fails
        if (!$region->exists) {
            $region = Region::findOrFail(request()->route('region'));
        }
        
        try {
            $region->delete();
            return redirect()->route('regions.index')->with('success', 'Region deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('regions.index')->with('error', 'Cannot delete region: ' . $e->getMessage());
        }
    }

    /**
     * Export regions to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['is_active', 'name']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportRegions($filters, $format);
            
            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Regions export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import regions from Excel
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
            
            $result = $this->masterDataService->importRegions($file, $updateExisting);
            
            $message = "Import completed: {$result['imported_rows']} imported, {$result['updated_rows']} updated, {$result['failed_rows']} failed";
            
            if ($result['failed_rows'] > 0) {
                $message .= ". Check logs for details.";
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Regions import failed', [
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
            $fileName = $this->masterDataService->generateTemplate('regions');
            $filePath = storage_path('app/temp/' . $fileName);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Template file does not exist: {$fileName}");
            }
            
            return response()->download($filePath, 'regions_template.xlsx')->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Template download failed', [
                'type' => 'regions',
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
        return view('regions.import');
    }

    /**
     * Toggle active status of a region
     */
    public function toggleStatus(Region $region)
    {
        try {
            $region->is_active = !$region->is_active;
            $region->save();
            
            $status = $region->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()->with('success', "Region '{$region->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Region status toggle failed', [
                'region_id' => $region->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle region status: ' . $e->getMessage());
        }
    }
} 