<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'price',
        'quantity',
        'currency',
        'declared_shipping_cost',
        'is_inside_city',
        'status',
        'status_id',
        'admin_image_url',
        'admin_note',
        'total_amount_bdt',
        'weight',
        'payment_method',
        'tax',
        'additional_charges',
        'delivery_charge',
        'payment_processing_fee',
        'payment_status',
        'payment_reference',
        'bkash_trx_id',
        'payment_slip',
        'paid_at',
        'min_payment_amount',
    ];

    protected $appends = ['payment_slip_url'];

    public function getPaymentSlipUrlAttribute()
    {
        return $this->payment_slip ? asset('storage/' . $this->payment_slip) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
}
