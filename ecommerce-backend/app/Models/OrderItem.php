<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relationship: belongs to order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: belongs to product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor: calculate subtotal (quantity × price)
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Automatically set total before creating or updating
     */
    protected static function booted()
    {
        static::creating(function ($item) {
            $item->total = $item->quantity * $item->price;
        });

        static::updating(function ($item) {
            $item->total = $item->quantity * $item->price;
        });
    }

    /**
     * Optionally hide timestamps if unused
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}