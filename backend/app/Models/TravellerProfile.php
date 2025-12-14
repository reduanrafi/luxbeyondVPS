<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'passport_number',
        'phone_number',
        'verification_status',
        'documents',
        'travel_history',
    ];

    protected $casts = [
        'documents' => 'array',
        'travel_history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
