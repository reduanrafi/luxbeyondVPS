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
        Setting::firstOrCreate(['key' => 'currency_rate_usd'], ['value' => '120']);
        Setting::firstOrCreate(['key' => 'currency_rate_cny'], ['value' => '18']);
        Setting::firstOrCreate(['key' => 'shipping_charge_per_kg'], ['value' => '650']);
    }
}
