<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = ['logo_url'];

    /**
     * Get the logo URL if this is a site logo setting.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->key === 'site_logo' && $this->value) {
            return Storage::disk('public')->url($this->value);
        }
        return null;
    }

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }
}
