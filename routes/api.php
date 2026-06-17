<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Clients API
    Route::apiResource('clients', ClientController::class);

    // Orders API
    Route::apiResource('orders', OrderController::class);
    Route::get('clients/{clientId}/orders', [OrderController::class, 'byClient']);

    // Inputs API
    Route::apiResource('inputs', InputController::class);
});
