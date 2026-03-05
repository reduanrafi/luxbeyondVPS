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
