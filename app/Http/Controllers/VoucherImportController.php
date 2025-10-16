<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherImportRequest;
use App\Services\VoucherImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Merchant;
use App\Models\ImportLog;
use App\Models\Voucher;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\RedemptionLinksExport;

class VoucherImportController extends Controller
{
    protected $importService;

    public function __construct(VoucherImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show import form page
     */
    public function showImportForm()
    {
        $merchants = Merchant::orderBy('company_name')->get();
        
        return view('pages.vouchers.bulk', [
            'merchants' => $merchants,
            'pageTitle' => 'Import Vouchers'
        ]);
    }

    /**
     * Show import history page
     */
    public function historyPage(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $limit = $request->input('limit', 20);
        
        $imports = $this->importService->getUserImports(
            auth()->id(),
            $merchantId,
            $limit
        );

        $merchants = Merchant::orderBy('company_name')->get();
        
        return view('pages.vouchers.import.history', [
            'imports' => $imports,
            'merchants' => $merchants,
            'selectedMerchantId' => $merchantId,
            'pageTitle' => 'Import History'
        ]);
    }

    /**
     * Show import details page
     */
    public function showDetails(string $identifier)
    {
        $progress = $this->importService->getProgress($identifier);

        if (!$progress) {
            abort(404, 'Import not found');
        }

        return view('pages.vouchers.import.details', [
            'import' => $progress,
            'pageTitle' => 'Import Details - ' . $progress['import_id']
        ]);
    }

    /**
     * Import vouchers from Excel file
     */
    public function import(VoucherImportRequest $request)
    {
        try {
            $result = $this->importService->import(
                $request->file('file'),
                auth()->id(),
                $request->input('merchant_id')
            );

            Log::info('Voucher import initiated', [
                'import_log_id' => $result['import_log_id'],
                'import_id' => $result['import_id'],
                'user_id' => auth()->id(),
                'user_login' => auth()->user()->name ?? 'AriffAzmi',
                'merchant_id' => $request->input('merchant_id'),
                'filename' => $result['filename'],
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'File is being processed in the background',
                    'data' => $result
                ], 202);
            }

            // Web response - redirect to details page
            return redirect()
                ->route('vouchers.import.details', $result['import_log_id'])
                ->with('success', 'File uploaded successfully and is being processed in the background.');

        } catch (\Exception $e) {
            Log::error('Voucher import failed', [
                'user_id' => auth()->id(),
                'user_login' => auth()->user()->name ?? 'AriffAzmi',
                'merchant_id' => $request->input('merchant_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 500);
            }

            // Web response - redirect back with error
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Get import progress (AJAX endpoint)
     */
    public function progress(string $identifier): JsonResponse
    {
        $progress = $this->importService->getProgress($identifier);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }

    /**
     * Get user's import history (for AJAX)
     */
    public function history(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $merchantId = $request->input('merchant_id');
        
        $imports = $this->importService->getUserImports(
            auth()->id(),
            $merchantId,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $imports
        ]);
    }

    /**
     * Get merchant's import history
     */
    public function merchantHistory(Request $request, string $merchantId)
    {
        $limit = $request->input('limit', 20);
        
        $imports = $this->importService->getMerchantImports($merchantId, $limit);
        $merchant = Merchant::findOrFail($merchantId);

        // Check if AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $imports
            ]);
        }

        // Web response
        return view('vouchers.import.merchant-history', [
            'imports' => $imports,
            'merchant' => $merchant,
            'pageTitle' => 'Import History - ' . $merchant->name
        ]);
    }

    /**
     * Get vouchers from specific import (AJAX endpoint)
     */
    public function vouchers(Request $request, string $identifier): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            
            $vouchers = $this->importService->getImportVouchers($identifier, $perPage);

            return response()->json([
                'success' => true,
                'data' => $vouchers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Cancel an import
     */
    public function cancel(string $identifier, Request $request)
    {
        $cancelled = $this->importService->cancelImport($identifier);

        if (!$cancelled) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import cannot be cancelled'
                ], 400);
            }

            return redirect()
                ->back()
                ->with('error', 'Import cannot be cancelled');
        }

        Log::info('Import cancelled', [
            'identifier' => $identifier,
            'user_id' => auth()->id(),
            'user_login' => auth()->user()->name ?? 'AriffAzmi',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Import cancelled successfully'
            ]);
        }

        return redirect()
            ->route('vouchers.import.history')
            ->with('success', 'Import cancelled successfully');
    }

    /**
     * Delete import and associated vouchers
     */
    public function delete(string $identifier, Request $request)
    {
        $deleted = $this->importService->deleteImport($identifier);

        if (!$deleted) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete import'
                ], 400);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete import');
        }

        Log::info('Import deleted', [
            'identifier' => $identifier,
            'user_id' => auth()->id(),
            'user_login' => auth()->user()->name ?? 'AriffAzmi',
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Import deleted successfully'
            ]);
        }

        return redirect()
            ->route('vouchers.import.history')
            ->with('success', 'Import deleted successfully');
    }

    /**
     * Export voucher redemption links using Laravel Excel
     */
    public function exportRedemptionLinks(string $identifier)
    {
        try {
            $importLog = ImportLog::where('id', $identifier)
                ->orWhere('import_id', $identifier)
                ->first();

            if (!$importLog) {
                return redirect()
                    ->back()
                    ->with('error', 'Import not found');
            }

            // Check if there are vouchers
            $voucherCount = Voucher::where('import_id', $importLog->import_id)->count();

            if ($voucherCount === 0) {
                return redirect()
                    ->back()
                    ->with('error', 'No vouchers found for this import');
            }

            Log::info("Exporting redemption links", [
                'import_log_id' => $importLog->id,
                'import_id' => $importLog->import_id,
                'voucher_count' => $voucherCount,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-15 09:38:23'
            ]);

            // Generate filename
            $filename = "redemption-links-{$importLog->import_id}-" . date('YmdHis') . ".xlsx";

            // Export using Laravel Excel
            return Excel::download(
                new RedemptionLinksExport($importLog->import_id), 
                $filename
            );

        } catch (\Exception $e) {
            Log::error("Failed to export redemption links", [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-15 09:38:23'
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to export redemption links: ' . $e->getMessage());
        }
    }
}