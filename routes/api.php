<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductApiController;
use App\Http\Controllers\CarrierApiController;
use App\Http\Controllers\SupplierApiController;
use App\Http\Controllers\Api\CepController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;

// Store lazy-loading — carrega mais produtos ("Mostrar mais")
Route::get('/store/products', [StoreController::class, 'moreProducts'])->name('api.store.more-products');

// Rotas públicas (acessíveis sem autenticação, mas com verificação de idade)
Route::prefix('produtos')->name('api.produtos.')->group(function () {
    Route::get('/', [ProductApiController::class, 'index'])->name('index');
    Route::get('/{slug}', [ProductApiController::class, 'show'])
        ->middleware(['check.age'])
        ->name('show');
});

// Consulta de CEP — retorna estado baseado na faixa de CEP
Route::get('/cep/{cep}', [CepController::class, 'show'])->name('api.cep.show');

// Validação de cupom de desconto
Route::post('/coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'check'])
    ->middleware('auth:clients')
    ->name('api.coupons.validate');

Route::middleware(['auth', 'verified'])->name('api.')->group(function () {
    // Clients API
    Route::apiResource('clients', ClientController::class);

    // Orders API
    Route::apiResource('orders', OrderController::class);
    Route::get('clients/{clientId}/orders', [OrderController::class, 'byClient'])->name('api.clients.orders.byClient');

    // Inputs API
    Route::apiResource('inputs', InputController::class);

    // Suppliers API
    Route::apiResource('suppliers', SupplierApiController::class);

    // Carriers API
    Route::apiResource('carriers', CarrierApiController::class);
});
