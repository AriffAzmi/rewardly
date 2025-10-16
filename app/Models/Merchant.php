<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $keyType = 'uuid';
    public $incrementing = false;

    protected $fillable = [
        'user_id', // Foreign key to associate merchant with a user,
        'company_name',
        'contact_person',
        'status',
    ];

    // Relationship to User. A merchant belongs to a user.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Vouchers. One merchant can have many vouchers.
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    // uuid generation on creating a new merchant
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
