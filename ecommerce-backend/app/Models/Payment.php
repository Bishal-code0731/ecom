<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_status',
        'transaction_id',
        'amount',
        'currency',
        'payment_data', // JSON field to store additional payment info
    ];

    protected $casts = [
        'payment_data' => 'array',
    ];

    /**
     * Payment belongs to an order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
