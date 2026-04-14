<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(['key' => 'currency_rate_usd'], ['value' => '120', 'type' => 'number', 'group' => 'rates']);
        Setting::firstOrCreate(['key' => 'currency_rate_cny'], ['value' => '18', 'type' => 'number', 'group' => 'rates']);
        Setting::firstOrCreate(['key' => 'shipping_charge_per_kg'], ['value' => '1200', 'type' => 'number', 'group' => 'shipping']); // International
        Setting::firstOrCreate(['key' => 'delivery_charge_inside_city'], ['value' => '70', 'type' => 'number', 'group' => 'shipping']); // Local
        Setting::firstOrCreate(['key' => 'delivery_charge_outside_city'], ['value' => '130', 'type' => 'number', 'group' => 'shipping']); // Local
        Setting::firstOrCreate(['key' => 'delivery_charge_per_kg'], ['value' => '20', 'type' => 'number', 'group' => 'shipping']); // Weight based local
        Setting::firstOrCreate(['key' => 'min_payment_percentage_shop'], ['value' => '60', 'type' => 'number', 'group' => 'checkout']);
        Setting::firstOrCreate(['key' => 'min_payment_percentage_request'], ['value' => '100', 'type' => 'number', 'group' => 'checkout']);
        Setting::firstOrCreate(['key' => 'site_name'], ['value' => 'Lux Global Shopping', 'group' => 'general']);
    }
}
