<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate_to_base',
        'is_base',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rate_to_base' => 'decimal:4',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }
}
