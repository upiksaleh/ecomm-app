<?php

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::name('central.')->group(function () use ($domain) {
        Route::domain($domain)->group(function () {
            Route::inertia('/', 'central/Home')->name('home');
        });
    });
}
