<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportLog extends Model
{
    use HasFactory;

    protected $keyType = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        'import_id',
        'user_id',
        'merchant_id',
        'filename',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_rows' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_PARTIAL = 'partial';

    /**
     * Get the user who initiated the import
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant associated with this import
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get all vouchers from this import
     */
    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'import_id', 'import_id');
    }

    /**
     * Mark import as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark import as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Increment processed count
     */
    public function incrementProcessed($count = 1)
    {
        $this->increment('processed_rows', $count);
    }

    /**
     * Increment failed count
     */
    public function incrementFailed($count = 1)
    {
        $this->increment('failed_rows', $count);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Scope for recent imports
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by merchant
     */
    public function scopeByMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * UUID generation on creating a new import log
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            
            // Generate import_id if not set
            if (empty($model->import_id)) {
                $model->import_id = 'VCH_' . date('Ymd_His') . '_' . strtoupper(Str::random(8));
            }
        });
    }
}