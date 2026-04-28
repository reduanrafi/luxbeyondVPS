<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PricingService;

$service = new PricingService();
$result = $service->calculateBreakdown(
    3.00,    // $3 USD
    'USD',   // Currency
    'request', 
    [
        'weight' => 1.5, 
        'is_inside_city' => true, 
        'seller_shipping_cost' => 0
    ]
);

echo json_encode($result, JSON_PRETTY_PRINT);
