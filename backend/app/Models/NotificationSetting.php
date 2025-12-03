<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'channel',
        'recipient_type',
        'enabled',
        'template_name',
        'subject',
        'message_template',
        'variables',
        'conditions',
        'recipients',
        'delay_minutes',
        'priority',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'variables' => 'array',
        'conditions' => 'array',
        'recipients' => 'array',
        'delay_minutes' => 'integer',
        'sort_order' => 'integer',
    ];
}
