<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/deploy', function () {
    Artisan::call('migrate --force');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back();
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return redirect()->back();
});
Route::get('/content-seed', function () {
    try {
        Artisan::call('db:seed', [
            '--class' => 'PageContentSeeder',
            '--force' => true
        ]);
        return "<h1>Seeding Successful!</h1><pre>" . Artisan::output() . "</pre><br><a href='/'>Back to Home</a>";
    } catch (\Exception $e) {
        return "<h1>Seeding Failed</h1><p>" . $e->getMessage() . "</p>";
    }
});

Route::get('/google-merchant-catalog.xml', [App\Http\Controllers\CatalogController::class, 'googleMerchantFeed']);
