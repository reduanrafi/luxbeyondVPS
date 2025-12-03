<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status_id',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'min_payment_amount',
        'currency',
        'payment_method',
        'payment_status',
        'shipping_address',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'min_payment_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function updateStatus($statusId, $note = null, $userId = null)
    {
        $status = OrderStatus::findOrFail($statusId);
        
        $this->update([
            'status_id' => $statusId,
            'status' => $status->name,
        ]);

        // Create history record
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'status_id' => $statusId,
            'status' => $status->name,
            'changed_by' => $userId ?? auth()->id(),
            'note' => $note,
        ]);

        return $this;
    }
}
