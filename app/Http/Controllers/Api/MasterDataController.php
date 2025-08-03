<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MasterDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterDataController extends Controller
{
    public function __construct(
        private MasterDataService $masterDataService
    ) {}

    /**
     * Get all salesmen with filtering
     */
    public function getSalesmen(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => 'sometimes|integer|exists:regions,id',
            'channel_id' => 'sometimes|integer|exists:channels,id',
            'classification' => 'sometimes|string|in:food,non_food,both',
            'search' => 'sometimes|string|max:255'
        ]);

        try {
            $filters = $request->only(['region_id', 'channel_id', 'classification', 'search']);
            $salesmen = $this->masterDataService->getSalesmen($filters);

            return response()->json([
                'status' => 'success',
                'data' => $salesmen,
                'total_count' => $salesmen->count(),
                'filters_applied' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get salesmen', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve salesmen data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Export salesmen to Excel
     */
    public function exportSalesmen(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'region_id' => 'sometimes|integer|exists:regions,id',
            'channel_id' => 'sometimes|integer|exists:channels,id',
            'classification' => 'sometimes|string|in:food,non_food,both',
            'format' => 'sometimes|string|in:xlsx,csv'
        ]);

        try {
            $filters = $request->only(['region_id', 'channel_id', 'classification']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportSalesmen($filters, $format);

            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Salesmen export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Import salesmen from Excel
     */
    public function importSalesmen(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'update_existing' => 'sometimes|boolean'
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            
            $result = $this->masterDataService->importSalesmen($file, $updateExisting);

            return response()->json([
                'status' => 'success',
                'message' => 'Salesmen import completed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Salesmen import failed', [
                'error' => $e->getMessage(),
                'file_name' => $request->file('file')?->getClientOriginalName()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Import failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all suppliers with categories
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        $request->validate([
            'classification' => 'sometimes|string|max:255',
            'search' => 'sometimes|string|max:255'
        ]);

        try {
            $filters = $request->only(['classification', 'search']);
            $suppliers = $this->masterDataService->getSuppliers($filters);

            return response()->json([
                'status' => 'success',
                'data' => $suppliers,
                'total_count' => $suppliers->count(),
                'filters_applied' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get suppliers', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve suppliers data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Export suppliers to Excel
     */
    public function exportSuppliers(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'classification' => 'sometimes|string|max:255',
            'format' => 'sometimes|string|in:xlsx,csv'
        ]);

        try {
            $filters = $request->only(['classification']);
            $format = $request->get('format', 'xlsx');
            
            $result = $this->masterDataService->exportSuppliers($filters, $format);

            return response()->download($result['file_path'], $result['filename'])->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Suppliers export failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Import suppliers from Excel
     */
    public function importSuppliers(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'update_existing' => 'sometimes|boolean'
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            
            $result = $this->masterDataService->importSuppliers($file, $updateExisting);

            return response()->json([
                'status' => 'success',
                'message' => 'Suppliers import completed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Suppliers import failed', [
                'error' => $e->getMessage(),
                'file_name' => $request->file('file')?->getClientOriginalName()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Import failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get download template for salesmen
     */
    public function getSalesmenTemplate(): BinaryFileResponse
    {
        $templatePath = $this->masterDataService->generateSalesmenTemplate();
        return response()->download($templatePath, 'salesmen_template.xlsx')->deleteFileAfterSend();
    }

    /**
     * Get download template for suppliers
     */
    public function getSuppliersTemplate(): BinaryFileResponse
    {
        $templatePath = $this->masterDataService->generateSuppliersTemplate();
        return response()->download($templatePath, 'suppliers_template.xlsx')->deleteFileAfterSend();
    }

    /**
     * Get regions list
     */
    public function getRegions(): JsonResponse
    {
        try {
            $regions = $this->masterDataService->getRegions();

            return response()->json([
                'status' => 'success',
                'data' => $regions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get regions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve regions data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get channels list
     */
    public function getChannels(): JsonResponse
    {
        try {
            $channels = $this->masterDataService->getChannels();

            return response()->json([
                'status' => 'success',
                'data' => $channels
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get channels', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve channels data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
