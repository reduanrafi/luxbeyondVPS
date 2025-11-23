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
        'admin_image_url',
        'admin_note',
        'total_amount_bdt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
