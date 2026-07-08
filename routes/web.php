<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use App\Http\Controllers\Auth\LoginCarrierController;
use App\Http\Controllers\Auth\LoginClientController;
use App\Http\Controllers\Auth\RegisterCarrierController;
use App\Http\Controllers\Auth\RegisterClientController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalConsentController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Inertia\CarrierController as InertiaCarrierController;
use App\Http\Controllers\Inertia\ClientController as InertiaClientController;
use App\Http\Controllers\Inertia\FreightContractController as InertiaFreightContractController;
use App\Http\Controllers\Inertia\InputController as InertiaInputController;
use App\Http\Controllers\Inertia\OrderController as InertiaOrderController;
use App\Http\Controllers\Inertia\ProductController as InertiaProductController;
use App\Http\Controllers\Inertia\ReportController;
use App\Http\Controllers\Inertia\AdminUserController as InertiaAdminUserController;
use App\Http\Controllers\Inertia\ToolsController as InertiaToolsController;
use App\Http\Controllers\Inertia\SupplierController as InertiaSupplierController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreDetailController;
use Illuminate\Support\Facades\Route;

// Welcome page — carrossel de imagens dinâmico da pasta imgs/home/
Route::get('/', WelcomeController::class)->name('welcome');
Route::inertia('/home', 'Dashboard')->name('home');

// Public store (loja) — shows all tenants' products
Route::get('/store', [StoreController::class, 'index'])->name('store.index');

// Public product detail page (store) — rota dinâmica por slug com verificação de idade
Route::get('/store/{slug}', [StoreDetailController::class, 'show'])
    ->middleware(['check.age'])
    ->name('store.detail');

// Legal consent routes (public)
Route::get('/legal/{type}', [LegalConsentController::class, 'show'])
    ->where('type', 'terms|privacy')
    ->name('legal.show');
Route::post('/legal/accept', [LegalConsentController::class, 'accept'])->name('legal.accept');
Route::post('/legal/decline', [LegalConsentController::class, 'decline'])->name('legal.decline');
Route::post('/legal/accept-both', [LegalConsentController::class, 'acceptBoth'])->name('legal.acceptBoth');

// ─────────────────────────────────────────────────
// Client auth (customers)
// ─────────────────────────────────────────────────
Route::post('/logout_cli', [LoginClientController::class, 'destroy'])->name('logout.client');

Route::middleware(['redirect_if_authenticated'])->group(function () {
    Route::get('/login_cli', [LoginClientController::class, 'create'])->name('login.client');
    Route::post('/login_cli', [LoginClientController::class, 'store'])->name('login.client.store');

    // Client registration
    Route::get('/register_cli', [RegisterClientController::class, 'create'])->name('register.client');
    Route::post('/register_cli', [RegisterClientController::class, 'store'])->name('register.client.store');
});

// ─────────────────────────────────────────────────
// Carrier auth (transportadoras)
// ─────────────────────────────────────────────────
Route::post('/logout_carrier', [LoginCarrierController::class, 'destroy'])->name('logout.carrier');

Route::middleware(['redirect_if_authenticated:carriers'])->group(function () {
    Route::get('/login_carrier', [LoginCarrierController::class, 'create'])->name('login.carrier');
    Route::post('/login_carrier', [LoginCarrierController::class, 'store'])->name('login.carrier.store');

    Route::get('/register_carrier', [RegisterCarrierController::class, 'create'])->name('register.carrier');
    Route::post('/register_carrier', [RegisterCarrierController::class, 'store'])->name('register.carrier.store');
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

// Checkout (Stripe) — multi-step page
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Client profile routes (authenticated via clients guard)
Route::middleware(['auth:clients', 'verify.user.exists'])->group(function () {
    Route::get('/perfil', [ClientProfileController::class, 'profile'])->name('client.profile');
    Route::put('/perfil', [ClientProfileController::class, 'updateProfile'])->name('client.profile.update');
    Route::get('/perfil/enderecos', [ClientProfileController::class, 'addresses'])->name('client.addresses');
    Route::put('/perfil/enderecos', [ClientProfileController::class, 'updateAddress'])->name('client.addresses.update');
    Route::get('/perfil/pedidos', [ClientProfileController::class, 'orders'])->name('client.orders');
    Route::post('/perfil/pedidos/{order}/devolucao', [ClientProfileController::class, 'requestReturn'])->name('client.orders.return');
});

// Carrier dashboard routes (authenticated via carriers guard)
Route::middleware(['auth:carriers'])->group(function () {
    Route::get('/carrier/dashboard', function () {
        return inertia('Carriers/Dashboard');
    })->name('carrier.dashboard');
});

Route::middleware(['auth', 'verified', 'ensure.staff', 'verify.user.exists'])->group(function () {
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

    // Suppliers Management - Full CRUD
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [InertiaSupplierController::class, 'index'])->name('index');
        Route::get('create', [InertiaSupplierController::class, 'create'])->name('create');
        Route::post('/', [InertiaSupplierController::class, 'store'])->name('store');
        Route::get('{supplier}/edit', [InertiaSupplierController::class, 'edit'])->name('edit');
        Route::put('{supplier}', [InertiaSupplierController::class, 'update'])->name('update');
        Route::delete('{supplier}', [InertiaSupplierController::class, 'destroy'])->name('destroy');
    });

    // Carriers Management - Full CRUD
    Route::prefix('carriers')->name('carriers.')->group(function () {
        Route::get('/', [InertiaCarrierController::class, 'index'])->name('index');
        Route::get('create', [InertiaCarrierController::class, 'create'])->name('create');
        Route::post('/', [InertiaCarrierController::class, 'store'])->name('store');
        Route::get('{carrier}/edit', [InertiaCarrierController::class, 'edit'])->name('edit');
        Route::put('{carrier}', [InertiaCarrierController::class, 'update'])->name('update');
        Route::delete('{carrier}', [InertiaCarrierController::class, 'destroy'])->name('destroy');
    });

    // Freight Contracts Management - Full CRUD
    Route::prefix('freight-contracts')->name('freight-contracts.')->group(function () {
        Route::get('/', [InertiaFreightContractController::class, 'index'])->name('index');
        Route::get('create', [InertiaFreightContractController::class, 'create'])->name('create');
        Route::post('/', [InertiaFreightContractController::class, 'store'])->name('store');
        Route::get('{contract}/edit', [InertiaFreightContractController::class, 'edit'])->name('edit');
        Route::put('{contract}', [InertiaFreightContractController::class, 'update'])->name('update');
        Route::delete('{contract}', [InertiaFreightContractController::class, 'destroy'])->name('destroy');
    });

    // Tools (admin only)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/tools/security', [\App\Http\Controllers\Admin\ToolsController::class, 'index'])
            ->name('tools.security');
        Route::post('/tools/security', [\App\Http\Controllers\Admin\ToolsController::class, 'updateSecurityLevels'])
            ->name('tools.security.update');
        Route::get('/orders/{order}/label', [\App\Http\Controllers\Admin\ToolsController::class, 'generateLabel'])
            ->name('orders.label');
    });

    // Tools (staff dashboard)
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [InertiaToolsController::class, 'index'])->name('index');
        Route::post('/sitemap', [InertiaToolsController::class, 'generateSitemap'])->name('sitemap.generate');
        // Hero images (carrossel da home) — admin only
        Route::post('/hero-images', [InertiaToolsController::class, 'uploadHeroImages'])->name('hero-images.upload');
        Route::delete('/hero-images', [InertiaToolsController::class, 'deleteHeroImage'])->name('hero-images.delete');
    });

    // Admin Users — CRUD visível apenas para Admin (access_level = 10)
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [InertiaAdminUserController::class, 'index'])->name('index');
        Route::put('{user}', [InertiaAdminUserController::class, 'update'])->name('update');
        Route::patch('{user}/toggle', [InertiaAdminUserController::class, 'toggle'])->name('toggle');
        Route::post('{user}/reset-password', [InertiaAdminUserController::class, 'resetPassword'])->name('reset-password');
    });

    // Reports (management + admin com canAccessFinancials)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/inputs', [ReportController::class, 'inputs'])->name('inputs');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    });
});

require __DIR__.'/settings.php';
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.