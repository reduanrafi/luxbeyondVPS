<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'type', // 'image' or 'video'
        'sort_order',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        if (!$this->path) {
            return null;
        }
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }
        return \Storage::disk('public')->url($this->path);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
