<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'user_id',
        'url',
        'product_name',
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
        'charges_breakdown',
        'shipping_address',
    ];

    protected $casts = [
        'charges_breakdown' => 'array',
        'shipping_address' => 'array',
        'is_inside_city' => 'boolean',
        'payment_method_config' => 'array',
    ];

    protected $appends = ['payment_slip_url'];

    protected static function booted()
    {
        static::creating(function ($request) {
            $request->request_number = 'REQ-' . strtoupper(uniqid());
        });
    }

    public function getPaymentSlipUrlAttribute()
    {
        return $this->payment_slip ? asset(config('app.storage_repo').'/' . $this->payment_slip) : null;
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product_request')
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function timeline()
    {
        return $this->hasMany(ProductRequestTimeline::class)->orderBy('created_at', 'desc');
    }
    public function orderItem()
    {
        return $this->hasOne(OrderItem::class, 'request_id');
    }
}
