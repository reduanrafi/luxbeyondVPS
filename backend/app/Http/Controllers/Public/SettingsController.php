<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get settings required for checkout calculation.
     */
    public function getCheckoutSettings()
    {
        $keys = [
            'min_payment_percentage_shop',
            'delivery_charge_inside_city',
            'delivery_charge_outside_city',
            'delivery_charge_per_kg',
            'free_delivery_threshold',
        ];

        $settings = Setting::whereIn('key', $keys)
            ->pluck('value', 'key');

        return response()->json($settings);
    }
}
