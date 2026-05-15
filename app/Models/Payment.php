<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'request_id',
        'user_id',
        'amount',
        'payment_method',
        'status',
        'transaction_id',
        'currency',
        'receipt_path',
        'gateway_metadata',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'gateway_metadata' => 'array',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}