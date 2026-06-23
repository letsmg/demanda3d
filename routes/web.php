<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use App\Http\Controllers\Auth\LoginClientController;
use App\Http\Controllers\Auth\RegisterClientController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inertia\ClientController as InertiaClientController;
use App\Http\Controllers\Inertia\InputController as InertiaInputController;
use App\Http\Controllers\Inertia\OrderController as InertiaOrderController;
use App\Http\Controllers\Inertia\ProductController as InertiaProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');
Route::inertia('/home', 'Dashboard')->name('home');

// Public store (loja) — shows all tenants' products
Route::get('/store', [StoreController::class, 'index'])->name('store.index');

// Client auth (customers) — separate from staff auth
Route::post('/logout_cli', [LoginClientController::class, 'destroy'])->name('logout.client');

Route::middleware(['redirect_if_authenticated'])->group(function () {
    Route::get('/login_cli', [LoginClientController::class, 'create'])->name('login.client');
    Route::post('/login_cli', [LoginClientController::class, 'store'])->name('login.client.store');

    // Client registration
    Route::get('/register_cli', [RegisterClientController::class, 'create'])->name('register.client');
    Route::post('/register_cli', [RegisterClientController::class, 'store'])->name('register.client.store');
});

// Client cart — Inertia page
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');

// Client cart — JSON API
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/items', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::put('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Checkout (Stripe)
Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Client profile routes (authenticated via clients guard)
Route::get('/perfil', [ClientProfileController::class, 'profile'])->name('client.profile');
Route::put('/perfil', [ClientProfileController::class, 'updateProfile'])->name('client.profile.update');
Route::get('/perfil/enderecos', [ClientProfileController::class, 'addresses'])->name('client.addresses');
Route::put('/perfil/enderecos', [ClientProfileController::class, 'updateAddress'])->name('client.addresses.update');

Route::middleware(['auth', 'verified', 'ensure.staff'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clients Management - Full CRUD
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [InertiaClientController::class, 'index'])->name('index');
        Route::get('create', [InertiaClientController::class, 'create'])->name('create');
        Route::post('/', [InertiaClientController::class, 'store'])->name('store');
        Route::get('{client}/edit', [InertiaClientController::class, 'edit'])->name('edit');
        Route::put('{client}', [InertiaClientController::class, 'update'])->name('update');
        Route::delete('{client}', [InertiaClientController::class, 'destroy'])->name('destroy');
    });

    // Orders Management - Full CRUD
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [InertiaOrderController::class, 'index'])->name('index');
        Route::get('create', [InertiaOrderController::class, 'create'])->name('create');
        Route::post('/', [InertiaOrderController::class, 'store'])->name('store');
        Route::get('{order}/edit', [InertiaOrderController::class, 'edit'])->name('edit');
        Route::put('{order}', [InertiaOrderController::class, 'update'])->name('update');
        Route::delete('{order}', [InertiaOrderController::class, 'destroy'])->name('destroy');
    });

    // Inputs Management - Full CRUD
    Route::prefix('inputs')->name('inputs.')->group(function () {
        Route::get('/', [InertiaInputController::class, 'index'])->name('index');
        Route::get('create', [InertiaInputController::class, 'create'])->name('create');
        Route::post('/', [InertiaInputController::class, 'store'])->name('store');
        Route::get('{input}/edit', [InertiaInputController::class, 'edit'])->name('edit');
        Route::put('{input}', [InertiaInputController::class, 'update'])->name('update');
        Route::delete('{input}', [InertiaInputController::class, 'destroy'])->name('destroy');
    });

    // Products Management - Full CRUD (producers only)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [InertiaProductController::class, 'index'])->name('index');
        Route::get('create', [InertiaProductController::class, 'create'])->name('create');
        Route::post('/', [InertiaProductController::class, 'store'])->name('store');
        Route::get('{product}/edit', [InertiaProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [InertiaProductController::class, 'update'])->name('update');
        Route::delete('{product}', [InertiaProductController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';