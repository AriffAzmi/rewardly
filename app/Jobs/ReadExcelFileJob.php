<?php

namespace App\Jobs;

use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReadExcelFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    protected $importLogId;
    protected $importId;
    protected $filePath;
    protected $merchantId;

    public function __construct(string $importLogId, string $importId, string $filePath, ?string $merchantId = null)
    {
        $this->importLogId = $importLogId;
        $this->importId = $importId;
        $this->filePath = $filePath;
        $this->merchantId = $merchantId;
        $this->onQueue('imports');
    }

    public function handle(): void
    {
        try {
            Log::info("Starting Excel file reading", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'file_path' => $this->filePath,
                'merchant_id' => $this->merchantId,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 09:05:58'
            ]);

            // Update status to processing
            ImportLog::where('id', $this->importLogId)->update([
                'status' => ImportLog::STATUS_PROCESSING,
                'started_at' => now(),
            ]);

            // Get the full file path
            $fullPath = Storage::disk('local')->path($this->filePath);

            // Read Excel file and process in chunks
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            // Get headers from first row
            $rawHeaders = [];
            $headerRow = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1', null, true, false)[0];
            
            foreach ($headerRow as $header) {
                $rawHeaders[] = $header;
            }

            Log::info("Raw Excel headers detected", [
                'import_log_id' => $this->importLogId,
                'raw_headers' => $rawHeaders,
                'total_rows' => $highestRow - 1,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 09:05:58'
            ]);

            // Normalize and map headers
            $headerMapping = $this->normalizeHeaders($rawHeaders);

            Log::info("Normalized headers mapping", [
                'import_log_id' => $this->importLogId,
                'header_mapping' => $headerMapping,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 09:05:58'
            ]);

            // Validate required headers
            $requiredColumns = ['name', 'unique_key'];
            $optionalColumns = ['deno', 'percentage'];
            
            foreach ($requiredColumns as $required) {
                if (!isset($headerMapping[$required])) {
                    $possibleNames = $this->getColumnVariations($required);
                    throw new \Exception("Required column '{$required}' not found. Expected one of: " . implode(', ', $possibleNames));
                }
            }

            // Check if at least one of deno or percentage exists
            $hasDeno = isset($headerMapping['deno']);
            $hasPercentage = isset($headerMapping['percentage']);
            
            if (!$hasDeno && !$hasPercentage) {
                throw new \Exception("At least one of 'DENO' or 'PERCENTAGE' column is required. Found headers: " . implode(', ', $rawHeaders));
            }

            // Process rows in chunks
            $chunkSize = 500;
            $totalValidRows = 0;
            $chunksDispatched = 0;

            for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
                $endRow = min($startRow + $chunkSize - 1, $highestRow);
                
                $rows = $worksheet->rangeToArray(
                    'A' . $startRow . ':' . $worksheet->getHighestColumn() . $endRow,
                    null,
                    true,
                    false
                );

                // Convert to associative array with normalized keys
                $processedRows = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    
                    // Map each cell to normalized header
                    foreach ($headerMapping as $normalizedKey => $columnIndex) {
                        $rowData[$normalizedKey] = $row[$columnIndex] ?? null;
                    }

                    // Filter valid rows
                    $hasName = !empty($rowData['name']);
                    $hasUniqueKey = !empty($rowData['unique_key']);
                    $rowHasDeno = isset($rowData['deno']) && $rowData['deno'] !== '' && $rowData['deno'] !== null;
                    $rowHasPercentage = isset($rowData['percentage']) && $rowData['percentage'] !== '' && $rowData['percentage'] !== null;

                    if ($hasName && $hasUniqueKey && ($rowHasDeno || $rowHasPercentage)) {
                        $processedRows[] = $rowData;
                        $totalValidRows++;
                    }
                }

                // Dispatch chunk if has valid rows
                if (!empty($processedRows)) {
                    ProcessVoucherChunk::dispatch(
                        $processedRows,
                        $this->importLogId,
                        $this->importId,
                        $this->merchantId
                    )->onQueue('imports');

                    $chunksDispatched++;
                }
            }

            // Update total rows
            ImportLog::where('id', $this->importLogId)->update([
                'total_rows' => $totalValidRows,
            ]);

            Log::info("Excel file reading completed", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'total_rows_in_file' => $highestRow - 1,
                'total_valid_rows' => $totalValidRows,
                'chunks_dispatched' => $chunksDispatched,
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 09:05:58'
            ]);

            // If no valid rows
            if ($totalValidRows === 0) {
                ImportLog::where('id', $this->importLogId)->update([
                    'status' => ImportLog::STATUS_FAILED,
                    'completed_at' => now(),
                    'error_message' => 'No valid rows found in the file',
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Excel file reading failed", [
                'import_log_id' => $this->importLogId,
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_login' => 'AriffAzmi',
                'timestamp' => '2025-10-14 09:05:58'
            ]);

            ImportLog::where('id', $this->importLogId)->update([
                'status' => ImportLog::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Normalize headers and create mapping to column indices
     * Handles various column name variations
     */
    private function normalizeHeaders(array $rawHeaders): array
    {
        $mapping = [];
        
        foreach ($rawHeaders as $index => $header) {
            $normalized = $this->normalizeColumnName($header);
            
            if ($normalized) {
                $mapping[$normalized] = $index;
            }
        }
        
        return $mapping;
    }

    /**
     * Normalize column name to standard format
     * Handles case insensitivity and variations
     */
    private function normalizeColumnName(?string $header): ?string
    {
        if (empty($header)) {
            return null;
        }

        // Remove extra spaces and convert to lowercase
        $cleaned = strtolower(trim($header));
        
        // Remove special characters except underscore
        $cleaned = preg_replace('/[^a-z0-9_]/', '', $cleaned);

        // Map variations to standard names
        $columnMap = [
            // NAME variations
            'name' => 'name',
            'vouchername' => 'name',
            'voucher_name' => 'name',
            'description' => 'name',
            'desc' => 'name',
            
            // UNIQUE_KEY variations
            'uniquekey' => 'unique_key',
            'unique_key' => 'unique_key',
            'code' => 'unique_key',
            'vouchercode' => 'unique_key',
            'voucher_code' => 'unique_key',
            'key' => 'unique_key',
            
            // DENO variations
            'deno' => 'deno',
            'denomination' => 'deno',
            'denominations' => 'deno',
            'price' => 'deno',
            'retailprice' => 'deno',
            'retail_price' => 'deno',
            'value' => 'deno',
            'amount' => 'deno',
            
            // PERCENTAGE variations
            'percentage' => 'percentage',
            'percent' => 'percentage',
            'discount' => 'percentage',
            'discountpercentage' => 'percentage',
            'discount_percentage' => 'percentage',
            'disc' => 'percentage',
        ];

        return $columnMap[$cleaned] ?? null;
    }

    /**
     * Get possible column name variations for error messages
     */
    private function getColumnVariations(string $column): array
    {
        $variations = [
            'name' => ['NAME', 'Name', 'Voucher Name', 'Description'],
            'unique_key' => ['UNIQUE_KEY', 'Unique_Key', 'Code', 'Voucher Code', 'Key'],
            'deno' => ['DENO', 'Denomination', 'Price', 'Retail Price', 'Value', 'Amount'],
            'percentage' => ['PERCENTAGE', 'Percent', 'Discount', 'Discount Percentage'],
        ];

        return $variations[$column] ?? [$column];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ReadExcelFileJob permanently failed", [
            'import_log_id' => $this->importLogId,
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
            'user_login' => 'AriffAzmi',
            'timestamp' => '2025-10-14 09:05:58'
        ]);

        ImportLog::where('id', $this->importLogId)->update([
            'status' => ImportLog::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => 'File reading failed: ' . $exception->getMessage(),
        ]);
    }
}