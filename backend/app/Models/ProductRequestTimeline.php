<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequestTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_request_id',
        'status',
        'note',
        'created_by',
    ];

    public function request()
    {
        return $this->belongsTo(ProductRequest::class, 'product_request_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
