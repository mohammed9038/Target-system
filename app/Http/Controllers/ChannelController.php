<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChannelController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    public function index(Request $request)
    {
        $query = Channel::query();
        
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
        
        $channels = $query->paginate(15)->appends($request->all());
        
        return view('channels.index', compact('channels'));
    }

    public function create()
    {
        return view('channels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Channel::create($validated);
        return redirect()->route('channels.index')->with('success', 'Channel created successfully.');
    }

    public function edit(Channel $channel)
    {
        return view('channels.edit', compact('channel'));
    }

    public function update(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox - if unchecked, it won't be in the request
        $validated['is_active'] = $request->has('is_active');

        $channel->update($validated);
        return redirect()->route('channels.index')->with('success', 'Channel updated successfully.');
    }

    public function destroy(Channel $channel)
    {
        try {
            // Check if channel has any sales targets
            $targetsCount = \App\Models\SalesTarget::where('channel_id', $channel->id)->count();
            if ($targetsCount > 0) {
                return redirect()->route('channels.index')
                    ->with('error', "Cannot delete channel '{$channel->name}'. This channel has {$targetsCount} sales target(s) assigned. Please reassign or delete the targets first.");
            }

            // Check if channel has any salesmen
            if ($channel->salesmen()->exists()) {
                return redirect()->route('channels.index')
                    ->with('error', 'Cannot delete channel. Please reassign or delete associated salesmen first.');
            }

            $channel->delete();
            return redirect()->route('channels.index')
                ->with('success', 'Channel deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('channels.index')
                ->with('error', 'Failed to delete channel. Please try again.');
        }
    }

    /**
     * Export channels to Excel
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['is_active', 'name']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportChannels($filters, $format);
            
            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Channels export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Import channels from Excel
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
            
            $result = $this->masterDataService->importChannels($file, $updateExisting);
            
            $message = "Import completed: {$result['imported_rows']} imported, {$result['updated_rows']} updated, {$result['failed_rows']} failed";
            
            if ($result['failed_rows'] > 0) {
                $message .= ". Check logs for details.";
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Channels import failed', [
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
            $fileName = $this->masterDataService->generateTemplate('channels');
            $filePath = storage_path('app/temp/' . $fileName);
            
            if (!file_exists($filePath)) {
                throw new \Exception("Template file does not exist: {$fileName}");
            }
            
            return response()->download($filePath, 'channels_template.xlsx')->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Template download failed', [
                'type' => 'channels',
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
        return view('channels.import');
    }

    /**
     * Toggle active status of a channel
     */
    public function toggleStatus(Channel $channel)
    {
        try {
            $channel->is_active = !$channel->is_active;
            $channel->save();
            
            $status = $channel->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()->with('success', "Channel '{$channel->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Channel status toggle failed', [
                'channel_id' => $channel->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to toggle channel status: ' . $e->getMessage());
        }
    }
} 