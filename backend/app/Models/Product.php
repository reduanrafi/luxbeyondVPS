<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'brand',
        'price',
        'sellable_price',
        'stock',
        'image',
        'description',
        'short_description',
        'meta_title',
        'meta_description',
        'has_variants',
        'is_featured',
        'attribute_definitions',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sellable_price' => 'decimal:2',
        'stock' => 'integer',
        'has_variants' => 'boolean',
        'is_featured' => 'boolean',
        'attribute_definitions' => 'array',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected $appends = ['image_url', 'total_stock', 'in_stock'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function getTotalStockAttribute()
    {
        // If product has variants, sum their stock
        if ($this->has_variants && $this->relationLoaded('variants')) {
            return $this->variants->sum('stock');
        }
        // Otherwise return the product's own stock
        return $this->stock;
    }

    public function getInStockAttribute()
    {
        return $this->total_stock > 0;
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
