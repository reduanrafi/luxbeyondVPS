<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'attributes', // JSON: {"Color": "Red", "Size": "XL"}
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'attributes' => 'array',
    ];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        // If it's already a full URL (http/https), return as is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        
        // Get the raw image value to check what's actually stored
        $imagePath = $this->getRawOriginal('image') ?? $this->image;
        
        // If it already starts with /storage/, use asset() to get full URL
        if (str_starts_with($imagePath, '/storage/')) {
            return asset($imagePath);
        }
        
        // If it starts with storage/ (without leading slash), add leading slash
        if (str_starts_with($imagePath, 'storage/')) {
            return asset('/' . $imagePath);
        }
        
        // Otherwise, assume it's a storage path (e.g., "products/variants/file.avif")
        // and prepend storage/ to make it accessible with full URL
        return asset('storage/' . $imagePath);
    }
}
