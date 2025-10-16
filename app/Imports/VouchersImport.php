<?php

namespace App\Imports;

use App\Jobs\ProcessVoucherChunk;
use App\Models\ImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class VouchersImport implements 
    ToCollection, 
    WithHeadingRow, 
    WithChunkReading, 
    WithEvents,
    ShouldQueue  // Add this interface
{
    private $importLogId;
    private $importId;
    private $merchantId;
    private $totalRows = 0;
    private $chunksDispatched = 0;

    /**
     * Constructor
     * 
     * @param string $importLogId - UUID of import log
     * @param string $importId - VCH_YYYYMMDD_HHMMSS_XXXXXXXX format
     * @param string|null $merchantId - UUID of merchant
     */
    public function __construct(string $importLogId, string $importId, ?string $merchantId = null)
    {
        $this->importLogId = $importLogId;
        $this->importId = $importId;
        $this->merchantId = $merchantId;
    }

    /**
     * Process each collection chunk
     */
    public function collection(Collection $rows)
    {
        // Filter out empty rows - must have name and unique_key
        // Either deno OR percentage must be filled
        $validRows = $rows->filter(function ($row) {
            $hasName = !empty($row['name']);
            $hasUniqueKey = !empty($row['unique_key']);
            $hasDeno = isset($row['deno']) && $row['deno'] !== '' && $row['deno'] !== null;
            $hasPercentage = isset($row['percentage']) && $row['percentage'] !== '' && $row['percentage'] !== null;
            
            // Must have name, unique_key, and either deno or percentage
            return $hasName && $hasUniqueKey && ($hasDeno || $hasPercentage);
        });

        if ($validRows->isNotEmpty()) {
            // Dispatch chunk to queue for background processing
            ProcessVoucherChunk::dispatch(
                $validRows->toArray(), 
                $this->importLogId,
                $this->importId,
                $this->merchantId
            )->onQueue('imports');

            $this->chunksDispatched++;
        }

        $this->totalRows += $validRows->count();
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info("Starting voucher import", [
                    'import_log_id' => $this->importLogId,
                    'import_id' => $this->importId,
                    'merchant_id' => $this->merchantId,
                    'user_login' => 'AriffAzmi',
                    'timestamp' => '2025-10-14 08:38:52'
                ]);
                
                // Update import log status to processing
                ImportLog::where('id', $this->importLogId)->update([
                    'status' => ImportLog::STATUS_PROCESSING,
                    'started_at' => now(),
                ]);
            },

            AfterImport::class => function (AfterImport $event) {
                Log::info("Voucher import chunks dispatched", [
                    'import_log_id' => $this->importLogId,
                    'import_id' => $this->importId,
                    'total_rows' => $this->totalRows,
                    'chunks_dispatched' => $this->chunksDispatched,
                    'user_login' => 'AriffAzmi',
                    'timestamp' => '2025-10-14 08:38:52'
                ]);

                // Update total rows count
                ImportLog::where('id', $this->importLogId)->update([
                    'total_rows' => $this->totalRows,
                ]);

                // If no rows were processed (empty file or all invalid)
                if ($this->totalRows === 0) {
                    ImportLog::where('id', $this->importLogId)->update([
                        'status' => ImportLog::STATUS_FAILED,
                        'completed_at' => now(),
                        'error_message' => 'No valid rows found in the file',
                    ]);

                    Log::warning("Import failed - no valid rows", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 08:38:52'
                    ]);
                } else {
                    Log::info("Import file reading completed, processing chunks in background", [
                        'import_log_id' => $this->importLogId,
                        'import_id' => $this->importId,
                        'total_rows' => $this->totalRows,
                        'chunks_dispatched' => $this->chunksDispatched,
                        'user_login' => 'AriffAzmi',
                        'timestamp' => '2025-10-14 08:38:52'
                    ]);
                }
            },

            ImportFailed::class => function (ImportFailed $event) {
                Log::error("Import failed with exception", [
                    'import_log_id' => $this->importLogId,
                    'import_id' => $this->importId,
                    'error' => $event->getException()->getMessage(),
                    'trace' => $event->getException()->getTraceAsString(),
                    'user_login' => 'AriffAzmi',
                    'timestamp' => '2025-10-14 08:38:52'
                ]);

                ImportLog::where('id', $this->importLogId)->update([
                    'status' => ImportLog::STATUS_FAILED,
                    'completed_at' => now(),
                    'error_message' => $event->getException()->getMessage(),
                ]);
            },
        ];
    }

    /**
     * Get import log ID
     */
    public function getImportLogId(): string
    {
        return $this->importLogId;
    }

    /**
     * Get import ID
     */
    public function getImportId(): string
    {
        return $this->importId;
    }

    /**
     * Get total rows processed
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * Get chunks dispatched count
     */
    public function getChunksDispatched(): int
    {
        return $this->chunksDispatched;
    }
}