<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
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
        'status',
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

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_product')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    protected $appends = ['image_url', 'total_stock', 'in_stock'];
    
    /**
     * The attributes that should be visible in serialization.
     */
    protected $visible = [];
    
    /**
     * The attributes that should be hidden in serialization.
     */
    protected $hidden = [];

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return \Storage::disk('public')->url($this->image);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        static::updating(function ($product) {
            // Only regenerate slug if name changed and slug is empty
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    /**
     * Generate a slug from a string
     */
    public static function generateSlug($string)
    {
        return Str::slug($string);
    }

    /**
     * Generate a unique slug
     */
    public static function generateUniqueSlug($string, $excludeId = null)
    {
        $slug = static::generateSlug($string);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
