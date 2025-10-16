<?php

namespace App\Jobs;

use App\Imports\VoucherImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessVoucherImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $userId = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting voucher import process', ['file' => $this->filePath]);
            
            // Import the Excel file
            Excel::import(new VoucherImport(), $this->filePath);
            
            Log::info('Voucher import completed successfully', ['file' => $this->filePath]);
            
            // Clean up the uploaded file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
            
        } catch (\Exception $e) {
            Log::error('Voucher import failed', [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up the uploaded file even on failure
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Voucher import job failed permanently', [
            'file' => $this->filePath,
            'error' => $exception->getMessage()
        ]);
    }
}
