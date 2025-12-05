# bKash Payment Gateway Setup Guide

## Overview
This guide will help you configure the bKash payment gateway for your application.

## Prerequisites
1. bKash merchant account
2. bKash API credentials (App Key, App Secret, Username, Password)
3. Access to bKash merchant portal

## Step 1: Get bKash API Credentials

1. Log in to your bKash merchant portal
2. Navigate to API settings
3. Get the following credentials:
   - **App Key**: Your bKash application key
   - **App Secret**: Your bKash application secret
   - **Username**: Your bKash API username
   - **Password**: Your bKash API password

## Step 2: Configure Environment Variables

Add the following to your `.env` file:

```env
# bKash Configuration
BKASH_SANDBOX=true
BKASH_APP_KEY=your_app_key_here
BKASH_APP_SECRET=your_app_secret_here
BKASH_USERNAME=your_username_here
BKASH_PASSWORD=your_password_here

# bKash Base URLs (usually don't need to change)
SANDBOX_BASE_URL=https://tokenized.sandbox.bka.sh
LIVE_BASE_URL=https://tokenized.pay.bka.sh
```

**Important Notes:**
- Set `BKASH_SANDBOX=true` for testing/sandbox mode
- Set `BKASH_SANDBOX=false` for production/live mode
- Never commit your `.env` file with real credentials to version control

## Step 3: Run the Seeder

Run the following command to create the payment method in the database:

```bash
php artisan db:seed --class=BkashPaymentMethodSeeder
```

Or if you want to seed all seeders:

```bash
php artisan db:seed
```

## Step 4: Update Payment Method via Admin Panel (Alternative)

If you prefer to configure via the admin panel:

1. Log in to your admin panel
2. Navigate to Settings > Payment Methods
3. Find or create the bKash payment method
4. Set the following:
   - **Name**: bKash
   - **Type**: bkash
   - **Sub Type**: api
   - **Is Active**: Yes
   - **Is Online**: Yes
   - **Config** (JSON):
     ```json
     {
       "is_sandbox": true,
       "app_key": "your_app_key",
       "app_secret": "your_app_secret",
       "username": "your_username",
       "password": "your_password"
     }
     ```

## Step 5: Test the Configuration

1. Make a test payment through your application
2. Check the logs for any errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Troubleshooting

### Error: "bKash payment method is not configured"

**Solution:**
1. Make sure you've run the seeder: `php artisan db:seed --class=BkashPaymentMethodSeeder`
2. Check that the payment method exists in the database:
   ```bash
   php artisan tinker
   >>> \App\Models\PaymentMethod::where('type', 'bkash')->first();
   ```
3. Verify the config field is not null and contains all required keys

### Error: "Token generation failed"

**Solution:**
1. Verify your credentials are correct
2. Check if you're using sandbox credentials in sandbox mode
3. Ensure your bKash account is active
4. Check bKash API status

### Error: "Payment creation failed"

**Solution:**
1. Verify callback URL is accessible
2. Check that the amount is valid
3. Ensure your merchant account has sufficient permissions

## Production Checklist

Before going live:

- [ ] Set `BKASH_SANDBOX=false` in `.env`
- [ ] Update config with production credentials
- [ ] Test a real transaction
- [ ] Verify callback URL is accessible from bKash servers
- [ ] Set up proper error logging and monitoring
- [ ] Configure SSL certificate (required for production)

## API Endpoints

- **Initiate Payment**: `POST /api/payments/bkash/initiate`
- **Callback**: `GET /api/payments/bkash/callback`
- **Verify Payment**: `POST /api/payments/verify-bkash` (Admin only)

## Support

For bKash API support, contact:
- bKash Merchant Support: support@bkash.com
- bKash Developer Portal: https://developer.bka.sh

