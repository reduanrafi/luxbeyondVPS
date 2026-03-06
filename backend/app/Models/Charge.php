<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    protected $fillable = [
        'name',
        'type',
        'currency_id',
        'calculation_type',
        'value',
        'min_value',
        'max_value',
        'conditions',
        'is_active',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'currency_id' => 'integer',
        'calculation_type' => 'string',
        'value' => 'decimal:2',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Calculate charge amount based on base amount
     */
    public function calculate($baseAmount, $additionalData = [])
    {
        $amount = 0;

        if ($this->calculation_type === 'fixed') {
            $amount = $this->value;
        } elseif ($this->calculation_type === 'percentage') {
            $amount = ($baseAmount * $this->value) / 100;
        }

        // Apply min/max constraints
        if ($this->min_value != null && $amount < $this->min_value) {
            $amount = $this->min_value;
        }
        if ($this->max_value != null && $amount > $this->max_value) {
            $amount = $this->max_value;
        }

        // Convert to base currency if needed
        if ($this->currency && !$this->currency->is_base) {
            $amount = $amount * $this->currency->rate_to_base;
        }

        return round($amount, 2);
    }
}
