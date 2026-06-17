<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Clients Management
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::inertia('/', 'Clients/Index')->name('index');
        Route::inertia('create', 'Clients/Create')->name('create');
        Route::inertia('{client}/edit', 'Clients/Edit')->name('edit');
    });

    // Orders Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::inertia('/', 'Orders/Index')->name('index');
        Route::inertia('create', 'Orders/Create')->name('create');
        Route::inertia('{order}/edit', 'Orders/Edit')->name('edit');
    });

    // Inputs Management
    Route::prefix('inputs')->name('inputs.')->group(function () {
        Route::inertia('/', 'Inputs/Index')->name('index');
        Route::inertia('create', 'Inputs/Create')->name('create');
        Route::inertia('{input}/edit', 'Inputs/Edit')->name('edit');
    });
});

require __DIR__.'/settings.php';
