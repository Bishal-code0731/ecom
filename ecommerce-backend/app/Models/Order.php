<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'status',
        'payment_status',
        'shipping_address',
        'billing_address',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    /**
     * The user who placed the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Human-readable order status
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Human-readable payment status
     */
    public function getPaymentStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];

        return $statuses[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Automatically calculate subtotal and total before saving
     */
    protected static function booted()
    {
        static::saving(function ($order) {
            // Subtotal: sum of all item totals
            $order->subtotal = $order->items->sum(function ($item) {
                return $item->subtotal; // uses OrderItem accessor
            });

            // Total: subtotal + tax + shipping
            $order->total = $order->subtotal + ($order->tax ?? 0) + ($order->shipping ?? 0);
        });
    }

    /**
     * Optionally hide timestamps from API response
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
