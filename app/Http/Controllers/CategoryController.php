<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    public function index(Request $request)
    {
        $query = Category::with('supplier');
        
        // Handle search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('supplier', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        // Handle sorting
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort field
        $allowedSorts = ['id', 'category_code', 'name', 'supplier_name', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        // Handle relationship-based sorting
        if ($sortBy === 'supplier_name') {
            $query->join('suppliers', 'categories.supplier_id', '=', 'suppliers.id')
                  ->orderBy('suppliers.name', $sortDirection)
                  ->select('categories.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $categories = $query->paginate(15)->appends($request->all());
        
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('categories.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $suppliers = Supplier::all();
        return view('categories.edit', compact('category', 'suppliers'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has any sales targets
        $targetsCount = \App\Models\SalesTarget::where('category_id', $category->id)->count();
        
        if ($targetsCount > 0) {
            return redirect()->route('categories.index')->with('error', "Cannot delete category '{$category->name}'. This category has {$targetsCount} sales target(s) assigned. Please reassign or delete the targets first.");
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Export categories to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['supplier_id', 'name']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportCategories($filters, $format);
            
            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Categories export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import categories from Excel
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
            
            $result = $this->masterDataService->importCategories($file, $updateExisting);
            
            $message = "Import completed: {$result['imported_rows']} imported, {$result['updated_rows']} updated, {$result['failed_rows']} failed";
            
            if ($result['failed_rows'] > 0) {
                $message .= ". Check logs for details.";
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Categories import failed', [
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
            $fileName = $this->masterDataService->generateTemplate('categories');
            $filePath = storage_path('app/temp/' . $fileName);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Template file does not exist: {$fileName}");
            }
            
            return response()->download($filePath, 'categories_template.xlsx')->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Template download failed', [
                'type' => 'categories',
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
        return view('categories.import');
    }

    /**
     * Toggle active status of a category
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->is_active = !$category->is_active;
            $category->save();
            
            $status = $category->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()->with('success', "Category '{$category->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Category status toggle failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle category status: ' . $e->getMessage());
        }
    }
} 