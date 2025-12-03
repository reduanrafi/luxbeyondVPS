<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'name',
        'label',
        'color',
        'icon',
        'description',
        'sort_order',
        'is_default',
        'is_final',
        'is_active',
        'allowed_next_statuses',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'is_active' => 'boolean',
        'allowed_next_statuses' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id');
    }

    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'status_id');
    }

    public function canTransitionTo($statusId)
    {
        if ($this->is_final) {
            return false;
        }

        if ($this->allowed_next_statuses === null) {
            return true; // No restrictions
        }

        return in_array($statusId, $this->allowed_next_statuses);
    }
}
