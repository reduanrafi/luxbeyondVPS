<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Get settings required for checkout calculation.
     */
    public function getCheckoutSettings()
    {
        $settings = Cache::remember('settings_checkout', 600, function () {
            $keys = [
                'min_payment_percentage_shop',
                'delivery_charge_inside_city',
                'delivery_charge_outside_city',
                'delivery_charge_per_kg',
                'free_delivery_threshold',
            ];
            return Setting::whereIn('key', $keys)->pluck('value', 'key');
        });

        return response()->json($settings)
            ->header('Cache-Control', 'public, max-age=300, s-maxage=600');
    }

    public function getGeneralSettings()
    {
        $settings = Cache::remember('settings_general', 600, function () {
            $keys = [
                'site_name',
                'site_email',
                'site_phone',
                'site_logo',
                'footer_description',
                'contact_address',
                'facebook_url',
                'instagram_url',
                'linkedin_url',
                'whatsapp_number',
            ];

            return Setting::whereIn('key', $keys)
                ->get()
                ->mapWithKeys(function ($setting) {
                    $value = $setting->value;
                    if ($setting->key === 'site_logo' && $value) {
                        $value = \Illuminate\Support\Facades\Storage::disk('public')->url($value);
                    }
                    return [$setting->key => $value];
                });
        });

        return response()->json($settings)
            ->header('Cache-Control', 'public, max-age=300, s-maxage=600');
    }
}
