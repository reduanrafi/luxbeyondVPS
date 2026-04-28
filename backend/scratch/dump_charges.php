<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\Charge::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | Value: {$c->value} | Min: {$c->min_value} | Calc: {$c->calculation_type} | Cur: {$c->currency_id}\n";
}
