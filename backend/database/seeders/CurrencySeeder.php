<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'BDT',
                'name' => 'Bangladeshi Taka',
                'symbol' => '৳',
                'rate_to_base' => 1,
                'is_base' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'USD',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'rate_to_base' => 120.00, // Example rate: 1 USD = 120 BDT
                'is_base' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'CNY',
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'rate_to_base' => 18.00, // Example rate: 1 CNY = 18 BDT
                'is_base' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'rate_to_base' => 130.00, // Example rate: 1 EUR = 130 BDT
                'is_base' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'rate_to_base' => 150.00, // Example rate: 1 GBP = 150 BDT
                'is_base' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
