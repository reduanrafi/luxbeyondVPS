<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'request_id',
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'price',
        'quantity',
        'subtotal',
        'image',
        'variant_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'variant_data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function request()
    {
        return $this->belongsTo(ProductRequest::class, 'request_id');
    }
}
