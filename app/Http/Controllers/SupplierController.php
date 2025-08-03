<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    public function index(Request $request)
    {
        $query = Supplier::query();
        
        // Handle search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('classification', 'like', '%' . $request->search . '%');
        }
        
        // Handle sorting
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field
        $allowedSorts = ['id', 'supplier_code', 'name', 'phone', 'email', 'city', 'classification', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        $suppliers = $query->paginate(15)->appends($request->all());
        
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classification' => 'required|in:food,non_food',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classification' => 'required|in:food,non_food',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active');

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has any sales targets
        $targetsCount = \App\Models\SalesTarget::where('supplier_id', $supplier->id)->count();
        
        if ($targetsCount > 0) {
            return redirect()->route('suppliers.index')->with('error', "Cannot delete supplier '{$supplier->name}'. This supplier has {$targetsCount} sales target(s) assigned. Please reassign or delete the targets first.");
        }

        // Check if supplier has any categories
        $categoriesCount = \App\Models\Category::where('supplier_id', $supplier->id)->count();
        
        if ($categoriesCount > 0) {
            return redirect()->route('suppliers.index')->with('error', "Cannot delete supplier '{$supplier->name}'. This supplier has {$categoriesCount} categor(ies) assigned. Please delete the categories first.");
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Export suppliers to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['is_active', 'name']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportSuppliers($filters, $format);
            
            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Suppliers export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import suppliers from Excel
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
            
            $result = $this->masterDataService->importSuppliers($file, $updateExisting);
            
            $message = "Import completed: {$result['imported_rows']} imported, {$result['updated_rows']} updated, {$result['failed_rows']} failed";
            
            if ($result['failed_rows'] > 0) {
                $message .= ". Check logs for details.";
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Suppliers import failed', [
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
            $fileName = $this->masterDataService->generateTemplate('suppliers');
            $filePath = storage_path('app/temp/' . $fileName);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Template file does not exist: {$fileName}");
            }
            
            return response()->download($filePath, 'suppliers_template.xlsx')->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Template download failed', [
                'type' => 'suppliers',
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
        return view('suppliers.import');
    }

    /**
     * Toggle active status of a supplier
     */
    public function toggleStatus(Supplier $supplier)
    {
        try {
            $supplier->is_active = !$supplier->is_active;
            $supplier->save();
            
            $status = $supplier->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()->with('success', "Supplier '{$supplier->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Supplier status toggle failed', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle supplier status: ' . $e->getMessage());
        }
    }
} 