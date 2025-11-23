<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\CouponUsage;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'description',
        'usage_limit',
        'usage_limit_per_user',
        'usage_count',
        'min_spend',
        'max_discount',
        'starts_at',
        'expires_at',
        'is_active',
        'is_featured',
        'is_private',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_private' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_user')->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_product')->withTimestamps();
    }

    public function usages()
        {
            return $this->hasMany(CouponUsage::class);
        }

    // Helper Methods
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function canBeUsedBy($userId)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check if private and user is assigned
        if ($this->is_private && !$this->users()->where('user_id', $userId)->exists()) {
            return false;
        }

        // Check per-user limit
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($subtotal)
    {
        if ($this->type === 'fixed') {
            $discount = $this->value;
        } else {
            $discount = ($subtotal * $this->value) / 100;
        }

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return min($discount, $subtotal);
    }
}
