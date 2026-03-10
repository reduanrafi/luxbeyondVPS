<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'event_id',
        'coupon_id',
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
        'bkash_trx_id',
        'payment_reference',
        'payment_slip',
        'shipping_address',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'notes',
        'shipped_at',
        'delivered_at',
        'paid_at',
        'request_id'
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
        'paid_at' => 'datetime',
        'shipping_address' => 'array',
    ];

    protected $appends = [
        'paid_amount',
        'due_amount',
        'is_fully_paid',
        'payment_slip_url',
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

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function productRequests()
{
    return $this->belongsToMany(ProductRequest::class, 'order_product_request');
}

    /**
     * Get the total amount paid (sum of completed payments)
     */
    public function getPaidAmountAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get the remaining unpaid amount
     */
    public function getDueAmountAttribute()
    {
        return max(0, $this->total - $this->paid_amount);
    }

    /**
     * Check if order is fully paid
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->due_amount <= 0;
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

    /**
     * Get the payment slip URL
     */
    public function getPaymentSlipUrlAttribute()
    {
        if (!$this->payment_slip) {
            return null;
        }
        return \Storage::disk('public')->url($this->payment_slip);
    }
}
