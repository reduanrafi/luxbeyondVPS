# Quick Guide: Update bKash Credentials

## Method 1: Using Tinker (Recommended)

```bash
php artisan tinker
```

Then run:

```php
$method = \App\Models\PaymentMethod::where('type', 'bkash')->first();
$method->config = [
    'is_sandbox' => true, // Set to false for production
    'app_key' => 'your_app_key_here',
    'app_secret' => 'your_app_secret_here',
    'username' => 'your_username_here',
    'password' => 'your_password_here',
];
$method->save();
exit
```

## Method 2: Using SQL

```sql
UPDATE payment_methods 
SET config = JSON_SET(
    config,
    '$.is_sandbox', true,
    '$.app_key', 'your_app_key_here',
    '$.app_secret', 'your_app_secret_here',
    '$.username', 'your_username_here',
    '$.password', 'your_password_here'
)
WHERE type = 'bkash';
```

## Method 3: Via Admin Panel

1. Go to Admin Panel > Settings > Payment Methods
2. Find "bKash" payment method
3. Edit and update the Config JSON field with your credentials

## Verify Configuration

```bash
php artisan tinker
```

```php
$method = \App\Models\PaymentMethod::where('type', 'bkash')->first();
print_r($method->config);
exit
```

## Test the API

After updating credentials, test the payment initiation:

```bash
curl -X POST http://127.0.0.1:8000/api/payments/bkash/initiate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "order_id": 1,
    "amount": 100,
    "currency": "BDT"
  }'
```

