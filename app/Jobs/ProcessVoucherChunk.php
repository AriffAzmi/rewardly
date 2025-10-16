<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Models\Voucher;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProcessVoucherChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = [30, 60, 120];

    protected $rows;
    protected $importLogId;
    protected $importId;
    protected $merchantId;

    public function __construct(array $rows, string $importLogId, string $importId, ?string $merchantId = null)
    {
        $this->rows = $rows;
        $this->importLogId = $importLogId;
        $this->importId = $importId;
        $this->merchantId = $merchantId;
        $this->onQueue('imports');
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $processedCount = 0;
        $failedCount = 0;
        $vouchersToInsert = [];

        DB::beginTransaction();

        try {
            foreach ($this->rows as $index => $row) {
                $hasDeno = isset($row['deno']) && $row['deno'] !== '' && $row['deno'] !== null;
                $hasPercentage = isset($row['percentage']) && $row['percentage'] !== '' && $row['percentage'] !== null;

                $validator = Validator::make($row, [
                    'name' => 'required|string|max:255',
                    'unique_key' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $failedCount++;
                    Log::warning("Invalid voucher row - missing required fields", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'row_index' => $index,
                        'row' => $row,
                        'errors' => $validator->errors()->toArray(),
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                    continue;
                }

                if (!$hasDeno && !$hasPercentage) {
                    $failedCount++;
                    Log::warning("Invalid voucher row - both deno and percentage are empty", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'row_index' => $index,
                        'row' => $row,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                    continue;
                }

                if ($hasDeno && !is_numeric($row['deno'])) {
                    $failedCount++;
                    Log::warning("Invalid voucher row - deno is not numeric", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'row_index' => $index,
                        'deno' => $row['deno'],
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                    continue;
                }

                if ($hasPercentage && !is_numeric($row['percentage'])) {
                    $failedCount++;
                    Log::warning("Invalid voucher row - percentage is not numeric", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'row_index' => $index,
                        'percentage' => $row['percentage'],
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                    continue;
                }

                $exists = Voucher::where('code', $row['unique_key'])->exists();
                
                if ($exists) {
                    $failedCount++;
                    Log::warning("Duplicate voucher code in import", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'code' => $row['unique_key'],
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                    continue;
                }

                $denomination = $hasDeno ? floatval($row['deno']) : 0;
                $percentage = $hasPercentage ? floatval($row['percentage']) : 0;

                if (!$hasDeno && $hasPercentage) {
                    $retailPrice = 0;
                    $costPrice = 0;
                    $denomination = 0;
                } elseif ($hasDeno && !$hasPercentage) {
                    $retailPrice = $denomination;
                    $costPrice = $denomination;
                    $percentage = 0;
                } else {
                    $retailPrice = $denomination;
                    $costPrice = $this->calculateCostPrice($denomination, $percentage);
                }

                $sku = $this->generateSku($row);

                $vouchersToInsert[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'merchant_id' => $this->merchantId,
                    'code' => $row['unique_key'],
                    'sku' => $sku,
                    'description' => $row['name'],
                    'cost_price' => $costPrice,
                    'retail_price' => $retailPrice,
                    'discount_percentage' => $percentage,
                    'denominations' => $denomination,
                    'expiry_date' => $this->getExpiryDate($row),
                    'status' => Voucher::STATUS_ACTIVE,
                    'import_id' => $this->importId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $processedCount++;
            }

            if (!empty($vouchersToInsert)) {
                $chunks = array_chunk($vouchersToInsert, 100);
                foreach ($chunks as $chunk) {
                    Voucher::insert($chunk);
                }
            }

            DB::commit();

            // Update import log and check completion
            $this->updateImportLog($processedCount, $failedCount);

            Log::info("Voucher chunk processed successfully", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'processed' => $processedCount,
                'failed' => $failedCount,
                'total_in_chunk' => count($this->rows),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 07:51:26'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Failed to process voucher chunk", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 07:51:26'
            ]);

            $this->updateImportLog(0, count($this->rows));

            throw $e;
        }
    }

    private function generateSku(array $row): string
    {
        $merchantPrefix = $this->merchantId ? 'M' . substr($this->merchantId, -6) : 'GENERAL';
        
        $denoValue = isset($row['deno']) && $row['deno'] !== '' 
            ? number_format($row['deno'], 0, '', '') 
            : 'X';
        
        $uniqueKey = substr($row['unique_key'], -6);
        
        return strtoupper("{$merchantPrefix}-{$denoValue}-{$uniqueKey}");
    }

    private function calculateCostPrice($denomination, $discountPercentage): float
    {
        if ($denomination <= 0 || $discountPercentage <= 0) {
            return $denomination;
        }
        
        return $denomination - ($denomination * ($discountPercentage / 100));
    }

    private function getExpiryDate(array $row): string
    {
        if (isset($row['expiry_date']) && !empty($row['expiry_date'])) {
            try {
                return \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Log::warning("Invalid expiry date format, using default", [
                    'expiry_date' => $row['expiry_date'],
                    'error' => $e->getMessage(),
                    'user_login' => 'AriffAzmi',
                    'timestamp' => '2025-10-14 07:51:26'
                ]);
            }
        }
        
        return now()->addYear()->format('Y-m-d H:i:s');
    }

    /**
     * Update import log with progress and check if completed
     */
    private function updateImportLog(int $processed, int $failed): void
    {
        try {
            $importLog = ImportLog::lockForUpdate()->where('id', $this->importLogId)->first();

            if (!$importLog) {
                Log::error("Import log not found", [
                    'import_log_id' => $this->importLogId,
                    'user_login' => 'AriffAzmi',
                    'timestamp' => '2025-10-14 07:51:26'
                ]);
                return;
            }

            // Increment counters
            $importLog->processed_rows += $processed;
            $importLog->failed_rows += $failed;
            $importLog->save();

            // Refresh to get latest data
            $importLog->refresh();

            $totalProcessed = $importLog->processed_rows + $importLog->failed_rows;

            Log::info("Import log progress updated", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'total_rows' => $importLog->total_rows,
                'processed_rows' => $importLog->processed_rows,
                'failed_rows' => $importLog->failed_rows,
                'total_processed' => $totalProcessed,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 07:51:26'
            ]);

            // Check if import is complete
            if ($totalProcessed >= $importLog->total_rows && $importLog->total_rows > 0) {
                if ($importLog->failed_rows > 0 && $importLog->processed_rows > 0) {
                    // Partial success
                    $importLog->update([
                        'status' => ImportLog::STATUS_PARTIAL,
                        'completed_at' => now(),
                    ]);

                    Log::info("Import marked as PARTIAL", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'processed' => $importLog->processed_rows,
                        'failed' => $importLog->failed_rows,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);

                } elseif ($importLog->processed_rows > 0) {
                    // All successful
                    $importLog->update([
                        'status' => ImportLog::STATUS_COMPLETED,
                        'completed_at' => now(),
                    ]);

                    Log::info("Import marked as COMPLETED", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'processed' => $importLog->processed_rows,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);

                } else {
                    // All failed
                    $importLog->update([
                        'status' => ImportLog::STATUS_FAILED,
                        'completed_at' => now(),
                        'error_message' => 'All rows failed validation',
                    ]);

                    Log::warning("Import marked as FAILED - all rows failed", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'failed' => $importLog->failed_rows,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 07:51:26'
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error("Failed to update import log", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 07:51:26'
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Voucher job permanently failed", [
            'import_log_id' => $this->importLogId,
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
            'rows_count' => count($this->rows),
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-14 07:51:26'
        ]);

        $this->updateImportLog(0, count($this->rows));

        $importLog = ImportLog::where('id', $this->importLogId)->first();
        if ($importLog && $importLog->processed_rows === 0) {
            $importLog->markAsFailed($exception->getMessage());
        }
    }
}