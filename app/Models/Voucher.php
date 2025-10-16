<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use HasFactory;

    protected $keyType = 'uuid';
    public $incrementing = false;

    // Status constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_USED = 2;
    const STATUS_REDEEMED = 2; 

    protected $fillable = [
        'merchant_id',
        'code',
        'sku',
        'description',
        'cost_price',
        'retail_price',
        'discount_percentage',
        'denominations',
        'expiry_date',
        'status',
        'import_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'denominations' => 'decimal:2',
        'expiry_date' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Relationship to Merchant. A voucher belongs to a merchant.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Relationship to ImportLog
     */
    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_id', 'import_id');
    }

    /**
     * Scope to filter vouchers by status
     */
    public function scopeStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            // Convert string status to integer if needed
            if (is_string($status)) {
                $status = $this->convertStatusToInt($status);
            }
            return $query->where('status', $status);
        }
    }

    /**
     * Scope to filter vouchers by expiry date
     */
    public function scopeExpiryDate($query, $expiryDate)
    {
        if ($expiryDate) {
            return $query->whereDate('expiry_date', '<=', $expiryDate);
        }
    }

    /**
     * Scope to filter vouchers by merchant
     */
    public function scopeMerchant($query, $merchantId)
    {
        if ($merchantId) {
            return $query->where('merchant_id', $merchantId);
        }
    }

    /**
     * Scope to filter vouchers by code or SKU
     */
    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
    }

    /**
     * Scope by import ID
     */
    public function scopeByImport($query, $importId)
    {
        return $query->where('import_id', $importId);
    }

    /**
     * Scope for active vouchers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('expiry_date', '>', now());
    }

    /**
     * Accessor to get formatted retail price
     */
    public function getFormattedRetailPriceAttribute()
    {
        return 'RM ' . number_format($this->retail_price, 2);
    }

    /**
     * Accessor to get formatted cost price
     */
    public function getFormattedCostPriceAttribute()
    {
        return 'RM ' . number_format($this->cost_price, 2);
    }

    /**
     * Accessor to get formatted discount percentage
     */
    public function getFormattedDiscountPercentageAttribute()
    {
        return number_format($this->discount_percentage, 2) . '%';
    }

    /**
     * Accessor to check if voucher is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date < now();
    }

    /**
     * Accessor to get formatted denominations
     */
    public function getFormattedDenominationsAttribute()
    {
        return 'RM ' . number_format($this->denominations, 2);
    }

    /**
     * Accessor to get status as string
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'active',
            self::STATUS_INACTIVE => 'inactive',
            self::STATUS_USED => 'used',
            default => 'unknown',
        };
    }

    /**
     * Convert string status to integer
     */
    public function convertStatusToInt($status)
    {
        return match(strtolower($status)) {
            'active' => self::STATUS_ACTIVE,
            'inactive' => self::STATUS_INACTIVE,
            'used' => self::STATUS_USED,
            default => self::STATUS_ACTIVE,
        };
    }

    /**
     * UUID generation on creating a new voucher
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}