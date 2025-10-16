<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Merchant;
use App\Services\VoucherImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    protected $importService;

    /**
     * Constructor
     */
    public function __construct(VoucherImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Display a listing of the resource with filters and statistics.
     */
    public function index(Request $request)
    {
        try {
            $query = Voucher::with('merchant');
            
            // Apply search filter (code, SKU, description)
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            
            // Apply merchant filter
            if ($request->filled('merchant_id')) {
                $query->merchant($request->merchant_id);
            }
            
            // Apply status filter
            if ($request->filled('status')) {
                $query->status($request->status);
            }
            
            // Apply expiry date filter
            if ($request->filled('expiry_date')) {
                $query->expiryDate($request->expiry_date);
            }
            
            // Apply import ID filter
            if ($request->filled('import_id')) {
                $query->byImport($request->import_id);
            }
            
            // Order by latest
            $vouchers = $query->latest()->paginate(20)->withQueryString();
            
            // Get merchants for filter dropdown
            $merchants = Merchant::orderBy('company_name')->get();
            
            // Get statistics
            $activeCount = Voucher::status('active')->count();
            $inactiveCount = Voucher::status('inactive')->count();
            $usedCount = Voucher::status('used')->count();
            $expiredCount = Voucher::where('expiry_date', '<', now())->count();
            
            Log::info('Vouchers index page viewed', [
                'user_id' => auth()->id(),
                'user_login' => auth()->user()->name ?? 'AriffAzmi',
                'total_vouchers' => $vouchers->total(),
                'filters' => $request->only(['search', 'merchant_id', 'status', 'expiry_date', 'import_id']),
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return view('pages.vouchers.index', compact(
                'vouchers', 
                'merchants', 
                'activeCount',
                'inactiveCount',
                'usedCount', 
                'expiredCount'
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to load vouchers index', [
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('dashboard')
                ->with('error', 'Failed to load vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource (bulk upload page).
     */
    public function create()
    {
        try {
            // Get all merchants for dropdown
            $merchants = Merchant::orderBy('name')->get();
            
            Log::info('Voucher bulk upload page viewed', [
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return view('pages.vouchers.create', compact('merchants'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load voucher create page', [
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'error' => $e->getMessage(),
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Failed to load create page: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage (single voucher).
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'merchant_id' => 'nullable|exists:merchants,id',
                'code' => 'required|string|max:255|unique:vouchers,code',
                'sku' => 'nullable|string|max:255',
                'description' => 'required|string|max:255',
                'cost_price' => 'required|numeric|min:0',
                'retail_price' => 'required|numeric|min:0',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'denominations' => 'required|numeric|min:0',
                'expiry_date' => 'required|date|after:today',
                'status' => 'required|in:active,inactive,used',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();
            
            // Generate SKU if not provided
            if (empty($validated['sku'])) {
                $validated['sku'] = $this->generateSKU($validated);
            }
            
            // Create voucher
            $voucher = Voucher::create($validated);
            
            Log::info('Voucher created manually', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'sku' => $voucher->sku,
                'merchant_id' => $voucher->merchant_id,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.show', $voucher->id)
                ->with('success', 'Voucher created successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to create voucher', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create voucher: ' . $e->getMessage());
        }
    }

    /**
     * Store a bulk upload voucher from Excel file.
     * Uses the new VoucherImportService for better performance.
     */
    public function storeBulk(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:20480', // Max 20MB
                'merchant_id' => 'nullable|exists:merchants,id',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Use VoucherImportService for import
            $result = $this->importService->import(
                $request->file('file'),
                auth()->id(),
                $request->input('merchant_id')
            );

            Log::info('Voucher bulk import initiated via storeBulk', [
                'import_log_id' => $result['import_log_id'],
                'import_id' => $result['import_id'],
                'filename' => $result['filename'],
                'merchant_id' => $request->input('merchant_id'),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);

            return redirect()
                ->route('vouchers.import.details', $result['import_log_id'])
                ->with('success', 'File uploaded successfully! Vouchers are being processed in the background. You can monitor the progress here.');

        } catch (\Exception $e) {
            Log::error('Voucher bulk import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $voucher = Voucher::with(['merchant', 'importLog'])->findOrFail($id);
            
            Log::info('Voucher details viewed', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return view('pages.vouchers.show', compact('voucher'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load voucher details', [
                'voucher_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Voucher not found or failed to load.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $voucher = Voucher::with('merchant')->findOrFail($id);
            $merchants = Merchant::orderBy('name')->get();
            
            Log::info('Voucher edit page viewed', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return view('pages.vouchers.edit', compact('voucher', 'merchants'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load voucher edit page', [
                'voucher_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Voucher not found or failed to load.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'merchant_id' => 'nullable|exists:merchants,id',
                'code' => 'required|string|max:255|unique:vouchers,code,' . $id,
                'sku' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'cost_price' => 'required|numeric|min:0',
                'retail_price' => 'required|numeric|min:0',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'denominations' => 'required|numeric|min:0',
                'expiry_date' => 'required|date',
                'status' => 'required|in:active,inactive,used',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();
            
            // Store old values for logging
            $oldValues = $voucher->only([
                'code', 'sku', 'description', 'cost_price', 'retail_price',
                'discount_percentage', 'denominations', 'expiry_date', 'status', 'merchant_id'
            ]);
            
            // Update voucher
            $voucher->update($validated);
            
            Log::info('Voucher updated', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'old_values' => $oldValues,
                'new_values' => $validated,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.show', $voucher->id)
                ->with('success', 'Voucher updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to update voucher', [
                'voucher_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token', '_method']),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update voucher: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucherCode = $voucher->code;
            $voucherSku = $voucher->sku;
            
            $voucher->delete();
            
            Log::info('Voucher deleted', [
                'voucher_id' => $id,
                'code' => $voucherCode,
                'sku' => $voucherSku,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.index')
                ->with('success', 'Voucher deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete voucher', [
                'voucher_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete voucher: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete vouchers.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'voucher_ids' => 'required|array|min:1',
                'voucher_ids.*' => 'exists:vouchers,id'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->with('error', 'Please select at least one voucher to delete.');
            }

            $voucherIds = $request->input('voucher_ids');
            
            // Get voucher details before deletion for logging
            $vouchers = Voucher::whereIn('id', $voucherIds)->get(['id', 'code', 'sku']);
            
            // Delete vouchers
            $count = Voucher::whereIn('id', $voucherIds)->delete();
            
            Log::info('Vouchers bulk deleted', [
                'count' => $count,
                'voucher_ids' => $voucherIds,
                'vouchers_deleted' => $vouchers->toArray(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->route('vouchers.index')
                ->with('success', "{$count} voucher(s) deleted successfully!");
                
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete vouchers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'voucher_ids' => $request->input('voucher_ids', []),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Export vouchers to CSV.
     */
    public function export(Request $request)
    {
        try {
            $query = Voucher::with('merchant');
            
            // Apply filters
            if ($request->filled('merchant_id')) {
                $query->merchant($request->merchant_id);
            }
            
            if ($request->filled('status')) {
                $query->status($request->status);
            }
            
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            
            if ($request->filled('import_id')) {
                $query->byImport($request->import_id);
            }
            
            $vouchers = $query->get();
            
            $filename = 'vouchers_export_' . date('Ymd_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];
            
            $callback = function() use ($vouchers) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Headers
                fputcsv($file, [
                    'ID',
                    'Code',
                    'SKU',
                    'Description',
                    'Merchant',
                    'Cost Price (RM)',
                    'Retail Price (RM)',
                    'Discount (%)',
                    'Denominations (RM)',
                    'Status',
                    'Expiry Date',
                    'Import ID',
                    'Created At',
                    'Updated At'
                ]);
                
                // Data
                foreach ($vouchers as $voucher) {
                    fputcsv($file, [
                        $voucher->id,
                        $voucher->code,
                        $voucher->sku,
                        $voucher->description,
                        $voucher->merchant ? $voucher->merchant->name : 'N/A',
                        number_format($voucher->cost_price, 2),
                        number_format($voucher->retail_price, 2),
                        number_format($voucher->discount_percentage, 2),
                        number_format($voucher->denominations, 2),
                        $voucher->status,
                        $voucher->expiry_date->format('Y-m-d'),
                        $voucher->import_id ?? 'N/A',
                        $voucher->created_at->format('Y-m-d H:i:s'),
                        $voucher->updated_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };
            
            Log::info('Vouchers exported', [
                'count' => $vouchers->count(),
                'filename' => $filename,
                'filters' => $request->only(['merchant_id', 'status', 'search', 'import_id']),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Failed to export vouchers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to export vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Get voucher statistics (API endpoint).
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => Voucher::count(),
                'active' => Voucher::status('active')->count(),
                'inactive' => Voucher::status('inactive')->count(),
                'used' => Voucher::status('used')->count(),
                'expired' => Voucher::where('expiry_date', '<', now())->count(),
                'by_merchant' => Voucher::with('merchant')
                    ->select('merchant_id', DB::raw('count(*) as count'))
                    ->groupBy('merchant_id')
                    ->get()
                    ->map(function($item) {
                        return [
                            'merchant_id' => $item->merchant_id,
                            'merchant_name' => $item->merchant ? $item->merchant->name : 'No Merchant',
                            'count' => $item->count
                        ];
                    }),
                'by_status' => Voucher::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status'),
                'total_value' => [
                    'cost_price' => Voucher::sum('cost_price'),
                    'retail_price' => Voucher::sum('retail_price'),
                    'denominations' => Voucher::sum('denominations')
                ]
            ];
            
            Log::info('Voucher statistics retrieved', [
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get voucher statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if voucher code is available (API endpoint).
     */
    public function checkCode(Request $request)
    {
        try {
            $code = $request->input('code');
            $excludeId = $request->input('exclude_id');
            
            if (!$code) {
                return response()->json([
                    'available' => false,
                    'message' => 'Code is required'
                ], 400);
            }
            
            $query = Voucher::where('code', $code);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            $exists = $query->exists();
            
            return response()->json([
                'available' => !$exists,
                'message' => $exists ? 'This code is already in use' : 'This code is available',
                'code' => $code
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check voucher code', [
                'code' => $request->input('code'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return response()->json([
                'available' => false,
                'message' => 'Failed to check code availability'
            ], 500);
        }
    }

    /**
     * Update voucher status.
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,used'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator);
            }

            $oldStatus = $voucher->status;
            $voucher->update(['status' => $request->status]);
            
            Log::info('Voucher status updated', [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->with('success', 'Voucher status updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to update voucher status', [
                'voucher_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 03:18:15'
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Generate SKU for voucher.
     */
    private function generateSKU(array $data): string
    {
        $merchantPrefix = isset($data['merchant_id']) && $data['merchant_id'] 
            ? 'M' . substr($data['merchant_id'], -6) 
            : 'GENERAL';
        
        $deno = number_format($data['denominations'], 0, '', '');
        $randomString = strtoupper(substr(uniqid(), -6));
        
        return "{$merchantPrefix}-{$deno}-{$randomString}";
    }
}