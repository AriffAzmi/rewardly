<?php

namespace App\Services;

use App\Jobs\ReadExcelFileJob;
use App\Models\ImportLog;
use App\Models\Merchant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoucherImportService
{
    /**
     * Process voucher import - Dispatch to background queue
     * 
     * @param UploadedFile $file
     * @param string|int $userId - Can be UUID string or integer
     * @param string|null $merchantId - UUID string
     */
    public function import(UploadedFile $file, string|int $userId, ?string $merchantId = null): array
    {
        // Validate merchant if provided
        if ($merchantId) {
            $merchant = Merchant::find($merchantId);
            if (!$merchant) {
                throw new \Exception('Merchant not found');
            }
        }

        // Generate unique import ID
        $importId = $this->generateImportId();

        // Store file
        $filePath = $this->storeFile($file, $importId);

        // Create import log
        $importLog = $this->createImportLog(
            $importId,
            $userId,
            $merchantId,
            $file->getClientOriginalName(),
            $filePath
        );

        // Dispatch job to read Excel file in background
        ReadExcelFileJob::dispatch(
            $importLog->id,
            $importId,
            $filePath,
            $merchantId
        )->onQueue('imports');

        Log::info("Voucher import dispatched to background", [
            'import_log_id' => $importLog->id,
            'import_id' => $importId,
            'user_id' => $userId,
            'user_login' => 'AriffAzmi',
            'merchant_id' => $merchantId,
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'timestamp' => '2025-10-14 08:40:46'
        ]);

        return [
            'import_log_id' => $importLog->id,
            'import_id' => $importId,
            'filename' => $file->getClientOriginalName(),
            'merchant_id' => $merchantId,
            'status' => $importLog->status,
            'started_at' => $importLog->started_at,
        ];
    }

    /**
     * Generate unique import ID
     */
    private function generateImportId(): string
    {
        return 'VCH_' . date('Ymd_His') . '_' . strtoupper(Str::random(8));
    }

    /**
     * Store uploaded file
     */
    private function storeFile(UploadedFile $file, string $importId): string
    {
        $filename = $importId . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('imports/vouchers', $filename, 'local');
    }

    /**
     * Create import log record
     */
    private function createImportLog(
        string $importId,
        string|int $userId,
        ?string $merchantId,
        string $filename,
        string $filePath
    ): ImportLog {
        return ImportLog::create([
            'import_id' => $importId,
            'user_id' => $userId,
            'merchant_id' => $merchantId,
            'filename' => $filename,
            'file_path' => $filePath,
            'status' => ImportLog::STATUS_PENDING,
            'total_rows' => 0,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Get import progress by import_log_id or import_id
     */
    public function getProgress(string $identifier): ?array
    {
        $importLog = ImportLog::with(['merchant', 'user'])
            ->where('id', $identifier)
            ->orWhere('import_id', $identifier)
            ->first();

        if (!$importLog) {
            return null;
        }

        return [
            'import_log_id' => $importLog->id,
            'import_id' => $importLog->import_id,
            'filename' => $importLog->filename,
            'merchant' => $importLog->merchant ? [
                'id' => $importLog->merchant->id,
                'name' => $importLog->merchant->company_name ?? $importLog->merchant->name ?? 'N/A',
            ] : null,
            'user' => [
                'id' => $importLog->user->id,
                'name' => $importLog->user->name ?? 'AriffAzmi',
            ],
            'status' => $importLog->status,
            'total_rows' => $importLog->total_rows,
            'processed_rows' => $importLog->processed_rows,
            'failed_rows' => $importLog->failed_rows,
            'progress_percentage' => $importLog->progress_percentage,
            'started_at' => $importLog->started_at?->toIso8601String(),
            'completed_at' => $importLog->completed_at?->toIso8601String(),
            'error_message' => $importLog->error_message,
        ];
    }

    /**
     * Get user's import history
     */
    public function getUserImports(string|int $userId, ?string $merchantId = null, int $limit = 10): array
    {
        $query = ImportLog::with(['merchant', 'user'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($merchantId) {
            $query->where('merchant_id', $merchantId);
        }

        $imports = $query->get();

        return $imports->map(function ($import) {
            return [
                'import_log_id' => $import->id,
                'import_id' => $import->import_id,
                'filename' => $import->filename,
                'merchant' => $import->merchant ? [
                    'id' => $import->merchant->id,
                    'name' => $import->merchant->company_name ?? $import->merchant->name ?? 'N/A',
                ] : null,
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'failed_rows' => $import->failed_rows,
                'progress_percentage' => $import->progress_percentage,
                'created_at' => $import->created_at->toIso8601String(),
                'completed_at' => $import->completed_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Get merchant's import history
     */
    public function getMerchantImports(string $merchantId, int $limit = 10): array
    {
        $imports = ImportLog::with(['merchant', 'user'])
            ->where('merchant_id', $merchantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $imports->map(function ($import) {
            return [
                'import_log_id' => $import->id,
                'import_id' => $import->import_id,
                'filename' => $import->filename,
                'user' => [
                    'id' => $import->user->id,
                    'name' => $import->user->name ?? 'AriffAzmi',
                ],
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'failed_rows' => $import->failed_rows,
                'progress_percentage' => $import->progress_percentage,
                'created_at' => $import->created_at->toIso8601String(),
                'completed_at' => $import->completed_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Get vouchers from import
     */
    public function getImportVouchers(string $identifier, int $perPage = 50): array
    {
        $importLog = ImportLog::where('id', $identifier)
            ->orWhere('import_id', $identifier)
            ->first();

        if (!$importLog) {
            throw new \Exception('Import not found');
        }

        $vouchers = \App\Models\Voucher::with('merchant')
            ->where('import_id', $importLog->import_id)
            ->paginate($perPage);

        return [
            'import_log_id' => $importLog->id,
            'import_id' => $importLog->import_id,
            'data' => $vouchers->items(),
            'current_page' => $vouchers->currentPage(),
            'per_page' => $vouchers->perPage(),
            'total' => $vouchers->total(),
            'last_page' => $vouchers->lastPage(),
        ];
    }

    /**
     * Cancel import
     */
    public function cancelImport(string $identifier): bool
    {
        $importLog = ImportLog::where('id', $identifier)
            ->orWhere('import_id', $identifier)
            ->first();

        if (!$importLog || $importLog->status === ImportLog::STATUS_COMPLETED) {
            return false;
        }

        $importLog->update([
            'status' => ImportLog::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => 'Import cancelled by user',
        ]);

        Log::info("Import cancelled", [
            'import_log_id' => $importLog->id,
            'import_id' => $importLog->import_id,
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-14 08:40:46'
        ]);

        return true;
    }

    /**
     * Delete import and associated vouchers
     */
    public function deleteImport(string $identifier): bool
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $importLog = ImportLog::where('id', $identifier)
                ->orWhere('import_id', $identifier)
                ->first();

            if (!$importLog) {
                return false;
            }

            $vouchersDeleted = \App\Models\Voucher::where('import_id', $importLog->import_id)->delete();

            if ($importLog->file_path && Storage::disk('local')->exists($importLog->file_path)) {
                Storage::disk('local')->delete($importLog->file_path);
            }

            $importLog->delete();

            \Illuminate\Support\Facades\DB::commit();

            Log::info("Import deleted", [
                'import_log_id' => $importLog->id,
                'import_id' => $importLog->import_id,
                'vouchers_deleted' => $vouchersDeleted,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 08:40:46'
            ]);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            Log::error('Failed to delete import', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 08:40:46'
            ]);
            
            return false;
        }
    }
}