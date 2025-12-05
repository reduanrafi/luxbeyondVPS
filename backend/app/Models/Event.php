<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'image',
        'banner_image',
        'bg_color',
        'position',
        'url',
        'show_button',
        'button_text',
        'button_color',
        'price',
        'price_type',
        'discount_percentage',
        'start_date',
        'end_date',
        'is_active',
        'send_notification',
        'sort_order',
        'priority',
        'meta',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'show_button' => 'boolean',
        'send_notification' => 'boolean',
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'sort_order' => 'integer',
        'priority' => 'integer',
        'meta' => 'array',
    ];

    protected $appends = ['image_url', 'banner_image_url', 'is_live', 'is_upcoming', 'is_expired'];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->name);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('name') && empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->name, $event->id);
            }
        });
    }

    /**
     * Generate unique slug from name
     */
    public static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get image URL accessor
     */
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

    /**
     * Get banner image URL accessor
     */
    public function getBannerImageUrlAttribute()
    {
        if (!$this->banner_image) {
            return null;
        }
        if (filter_var($this->banner_image, FILTER_VALIDATE_URL)) {
            return $this->banner_image;
        }
        return asset('storage/' . $this->banner_image);
    }

    /**
     * Check if event is currently live
     */
    public function getIsLiveAttribute()
    {
        $now = Carbon::now();
        return $this->is_active 
            && $this->start_date <= $now 
            && $this->end_date >= $now;
    }

    /**
     * Check if event is upcoming
     */
    public function getIsUpcomingAttribute()
    {
        $now = Carbon::now();
        return $this->is_active && $this->start_date > $now;
    }

    /**
     * Check if event is expired
     */
    public function getIsExpiredAttribute()
    {
        $now = Carbon::now();
        return $this->end_date < $now;
    }

    /**
     * Scope for active events
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for live events (currently running)
     */
    public function scopeLive($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('start_date', '>', $now);
    }

    /**
     * Scope for events by position
     */
    public function scopeByPosition($query, $position)
    {
        return $query->where(function($q) use ($position) {
            $q->where('position', $position)
              ->orWhere('position', 'both');
        });
    }

    /**
     * Products relationship
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'event_product')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get products count
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }
}

