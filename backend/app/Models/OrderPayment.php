<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'payment_reference',
        'payment_slip',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the URL for the payment slip
     */
    public function getPaymentSlipUrlAttribute()
    {
        if (!$this->payment_slip) {
            return null;
        }

        if (filter_var($this->payment_slip, FILTER_VALIDATE_URL)) {
            return $this->payment_slip;
        }

        return \Storage::disk('public')->url($this->payment_slip);
    }

    /**
     * Scope to get completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
