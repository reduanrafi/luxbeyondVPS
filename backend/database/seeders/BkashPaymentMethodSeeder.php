<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class BkashPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if bKash payment method already exists
        $existingMethod = PaymentMethod::where('type', 'bkash')->first();
        
        if ($existingMethod) {
            $this->command->info('bKash payment method already exists. Updating...');
            $existingMethod->update($this->getPaymentMethodData());
        } else {
            $this->command->info('Creating bKash payment method...');
            PaymentMethod::create($this->getPaymentMethodData());
        }
        
        $this->command->info('bKash payment method configured successfully!');
        $this->command->warn('⚠️  Please update the config with your actual bKash API credentials in the admin panel or database.');
    }

    private function getPaymentMethodData(): array
    {
        return [
            'name' => 'bKash',
            'type' => 'bkash',
            'sub_type' => 'api',
            'description' => 'Pay securely with bKash mobile wallet',
            'config' => [
                'is_sandbox' => env('BKASH_SANDBOX', true), // Set to false for production
                'app_key' => env('BKASH_APP_KEY', ''), // Your bKash App Key
                'app_secret' => env('BKASH_APP_SECRET', ''), // Your bKash App Secret
                'username' => env('BKASH_USERNAME', ''), // Your bKash Username
                'password' => env('BKASH_PASSWORD', ''), // Your bKash Password
            ],
            'is_active' => true,
            'is_online' => true,
            'sort_order' => 1,
            'fee' => 0,
            'fee_percentage' => 0,
            'instructions' => 'Complete your payment using bKash mobile wallet. You will be redirected to bKash payment page.',
        ];
    }
}

