<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'sub_type',
        'description',
        'config',
        'account_number',
        'account_name',
        'bank_name',
        'branch_name',
        'routing_number',
        'swift_code',
        'instructions',
        'icon',
        'is_active',
        'is_online',
        'sort_order',
        'fee',
        'fee_percentage',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'sort_order' => 'integer',
        'fee' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
    ];
}
