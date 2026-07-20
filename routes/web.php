<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\{
    WelcomeController,
    LegalConsentController,
    CartController,
    CheckoutController,
    ClientProfileController,
    DashboardController,
    StoreController,
    StoreDetailController,
    TenantProfileController
};

use App\Http\Controllers\Auth\{
    LoginCarrierController,
    LoginClientController,
    RegisterCarrierController,
    RegisterClientController
};

use App\Http\Controllers\Inertia\{
    CarrierController as InertiaCarrierController,
    ClientController as InertiaClientController,
    FreightContractController as InertiaFreightContractController,
    InputController as InertiaInputController,
    OrderController as InertiaOrderController,
    ProductController as InertiaProductController,
    ReportController,
    AdminUserController as InertiaAdminUserController,
    ToolsController as InertiaToolsController,
    SupplierController as InertiaSupplierController
};

// ─────────────────────────────────────────────────
// PÚBLICO: Welcome & Geral
// ─────────────────────────────────────────────────
Route::get('/', WelcomeController::class)->name('welcome');
Route::inertia('/home', 'Dashboard')->name('home');
Route::inertia('/sobre', 'About')->name('about');

// ─────────────────────────────────────────────────
// LOJA: Listagem e Detalhes
// ─────────────────────────────────────────────────
// Importante: A ordem define prioridade. /store (index) antes de /store/{slug}
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/api/store/products', [StoreController::class, 'moreProducts'])->name('store.products');
Route::get('/store/{slug}', [StoreDetailController::class, 'show'])
    ->middleware(['check.age'])
    ->name('store.detail');

// ─────────────────────────────────────────────────
// TENANTS: Listagem e Perfis
// ─────────────────────────────────────────────────
Route::get('/tenants', [TenantProfileController::class, 'index'])->name('tenants.index');
Route::get('/tenant/{fantasy_slug}', [TenantProfileController::class, 'show'])->name('tenant.profile');
Route::get('/api/tenant/{fantasy_slug}/products', [TenantProfileController::class, 'moreProducts'])->name('tenant.products');

// ─────────────────────────────────────────────────
// LEGAL & CONSENTIMENTO
// ─────────────────────────────────────────────────
Route::get('/legal/{type}', [LegalConsentController::class, 'show'])
    ->where('type', 'terms|privacy')
    ->name('legal.show');
Route::post('/legal/accept', [LegalConsentController::class, 'accept'])->name('legal.accept');
Route::post('/legal/decline', [LegalConsentController::class, 'decline'])->name('legal.decline');
Route::post('/legal/accept-both', [LegalConsentController::class, 'acceptBoth'])->name('legal.acceptBoth');

Route::middleware(['auth_or_clients_or_carriers'])->group(function () {
    Route::post('/consent/accept', [\App\Http\Controllers\ConsentController::class, 'accept'])->name('consent.accept');
    Route::post('/consent/dismiss', [\App\Http\Controllers\ConsentController::class, 'dismiss'])->name('consent.dismiss');
});

// ─────────────────────────────────────────────────
// AUTH: Clientes & Transportadoras
// ─────────────────────────────────────────────────
Route::post('/logout_cli', [LoginClientController::class, 'destroy'])->name('logout.client');
Route::post('/logout_carrier', [LoginCarrierController::class, 'destroy'])->name('logout.carrier');

Route::middleware(['redirect_if_authenticated'])->group(function () {
    Route::get('/login_cli', [LoginClientController::class, 'create'])->name('login.client');
    Route::post('/login_cli', [LoginClientController::class, 'store'])->name('login.client.store');
    Route::get('/register_cli', [RegisterClientController::class, 'create'])->name('register.client');
    Route::post('/register_cli', [RegisterClientController::class, 'store'])->name('register.client.store');
});

Route::middleware(['redirect_if_authenticated:carriers'])->group(function () {
    Route::get('/login_carrier', [LoginCarrierController::class, 'create'])->name('login.carrier');
    Route::post('/login_carrier', [LoginCarrierController::class, 'store'])->name('login.carrier.store');
    Route::get('/register_carrier', [RegisterCarrierController::class, 'create'])->name('register.carrier');
    Route::post('/register_carrier', [RegisterCarrierController::class, 'store'])->name('register.carrier.store');
});

// ─────────────────────────────────────────────────
// CART & CHECKOUT
// ─────────────────────────────────────────────────
Route::middleware('auth:clients')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/items', [CartController::class, 'index'])->name('index');
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::put('/{cartItem}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    });
});

Route::controller(CheckoutController::class)->group(function () {
    Route::get('/checkout', 'show')->name('checkout.show');
    Route::post('/checkout', 'store')->name('checkout.store');
    Route::get('/checkout/success', 'success')->name('checkout.success');
    Route::get('/checkout/cancel', 'cancel')->name('checkout.cancel');
});

// ─────────────────────────────────────────────────
// PERFIL DO CLIENTE (Logado)
// ─────────────────────────────────────────────────
Route::middleware(['auth:clients', 'verify.user.exists'])->group(function () {
    Route::get('/perfil', [ClientProfileController::class, 'profile'])->name('client.profile');
    Route::put('/perfil', [ClientProfileController::class, 'updateProfile'])->name('client.profile.update');
    Route::get('/perfil/enderecos', [ClientProfileController::class, 'addresses'])->name('client.addresses');
    Route::put('/perfil/enderecos', [ClientProfileController::class, 'updateAddress'])->name('client.addresses.update');
    Route::get('/perfil/pedidos', [ClientProfileController::class, 'orders'])->name('client.orders');
    Route::post('/perfil/pedidos/{order}/devolucao', [ClientProfileController::class, 'requestReturn'])->name('client.orders.return');
});

// ─────────────────────────────────────────────────
// TRANSPORTADORA (Logado)
// ─────────────────────────────────────────────────
Route::middleware(['auth:carriers'])->group(function () {
    Route::get('/carrier/dashboard', [App\Http\Controllers\CarrierDashboardController::class, 'dashboard'])->name('carrier.dashboard');
    Route::get('/carrier/profile', [App\Http\Controllers\CarrierDashboardController::class, 'profile'])->name('carrier.profile');
    Route::put('/carrier/profile', [App\Http\Controllers\CarrierDashboardController::class, 'updateProfile'])->name('carrier.profile.update');
    Route::get('/carrier/agreements', [App\Http\Controllers\CarrierDashboardController::class, 'agreements'])->name('carrier.agreements');
    Route::post('/carrier/agreements/{agreement}/accept', [App\Http\Controllers\CarrierDashboardController::class, 'acceptAgreement'])->name('carrier.agreements.accept');
    Route::post('/carrier/agreements/{agreement}/reject', [App\Http\Controllers\CarrierDashboardController::class, 'rejectAgreement'])->name('carrier.agreements.reject');
    Route::get('/carrier/orders', [App\Http\Controllers\CarrierDashboardController::class, 'orders'])->name('carrier.orders');
});

// ─────────────────────────────────────────────────
// ADMIN / STAFF (Dashboard & Management)
// ─────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'ensure.staff', 'verify.user.exists'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUDs
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [InertiaClientController::class, 'index'])->name('index');
        Route::get('create', [InertiaClientController::class, 'create'])->name('create');
        Route::post('/', [InertiaClientController::class, 'store'])->name('store');
        Route::get('{client}/edit', [InertiaClientController::class, 'edit'])->name('edit');
        Route::put('{client}', [InertiaClientController::class, 'update'])->name('update');
        Route::delete('{client}', [InertiaClientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [InertiaOrderController::class, 'index'])->name('index');
        Route::get('create', [InertiaOrderController::class, 'create'])->name('create');
        Route::post('/', [InertiaOrderController::class, 'store'])->name('store');
        Route::get('{order}/edit', [InertiaOrderController::class, 'edit'])->name('edit');
        Route::put('{order}', [InertiaOrderController::class, 'update'])->name('update');
        Route::delete('{order}', [InertiaOrderController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('inputs')->name('inputs.')->group(function () {
        Route::get('/', [InertiaInputController::class, 'index'])->name('index');
        Route::get('create', [InertiaInputController::class, 'create'])->name('create');
        Route::post('/', [InertiaInputController::class, 'store'])->name('store');
        Route::get('{input}/edit', [InertiaInputController::class, 'edit'])->name('edit');
        Route::put('{input}', [InertiaInputController::class, 'update'])->name('update');
        Route::delete('{input}', [InertiaInputController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [InertiaProductController::class, 'index'])->name('index');
        Route::get('create', [InertiaProductController::class, 'create'])->name('create');
        Route::post('/', [InertiaProductController::class, 'store'])->name('store');
        Route::get('{product}/edit', [InertiaProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [InertiaProductController::class, 'update'])->name('update');
        Route::delete('{product}', [InertiaProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [InertiaSupplierController::class, 'index'])->name('index');
        Route::get('create', [InertiaSupplierController::class, 'create'])->name('create');
        Route::post('/', [InertiaSupplierController::class, 'store'])->name('store');
        Route::get('{supplier}/edit', [InertiaSupplierController::class, 'edit'])->name('edit');
        Route::put('{supplier}', [InertiaSupplierController::class, 'update'])->name('update');
        Route::delete('{supplier}', [InertiaSupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('carriers')->name('carriers.')->group(function () {
        Route::get('/', [InertiaCarrierController::class, 'index'])->name('index');
        Route::get('create', [InertiaCarrierController::class, 'create'])->name('create');
        Route::post('/', [InertiaCarrierController::class, 'store'])->name('store');
        Route::get('{carrier}/edit', [InertiaCarrierController::class, 'edit'])->name('edit');
        Route::put('{carrier}', [InertiaCarrierController::class, 'update'])->name('update');
        Route::delete('{carrier}', [InertiaCarrierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('freight-contracts')->name('freight-contracts.')->group(function () {
        Route::get('/', [InertiaFreightContractController::class, 'index'])->name('index');
        Route::get('create', [InertiaFreightContractController::class, 'create'])->name('create');
        Route::post('/', [InertiaFreightContractController::class, 'store'])->name('store');
        Route::get('{contract}/edit', [InertiaFreightContractController::class, 'edit'])->name('edit');
        Route::put('{contract}', [InertiaFreightContractController::class, 'update'])->name('update');
        Route::delete('{contract}', [InertiaFreightContractController::class, 'destroy'])->name('destroy');
    });

    // Ferramentas Admin & Staff
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/tools/security', [\App\Http\Controllers\Admin\ToolsController::class, 'index'])->name('tools.security');
        Route::post('/tools/security', [\App\Http\Controllers\Admin\ToolsController::class, 'updateSecurityLevels'])->name('tools.security.update');
        Route::get('/orders/{order}/label', [\App\Http\Controllers\Admin\ToolsController::class, 'generateLabel'])->name('orders.label');
        
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [InertiaAdminUserController::class, 'index'])->name('index');
            Route::put('{user}', [InertiaAdminUserController::class, 'update'])->name('update');
            Route::patch('{user}/toggle', [InertiaAdminUserController::class, 'toggle'])->name('toggle');
            Route::post('{user}/reset-password', [InertiaAdminUserController::class, 'resetPassword'])->name('reset-password');
        });
    });

    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [InertiaToolsController::class, 'index'])->name('index');
        Route::post('/sitemap', [InertiaToolsController::class, 'generateSitemap'])->name('sitemap.generate');
        Route::post('/hero-images', [InertiaToolsController::class, 'uploadHeroImages'])->name('hero-images.upload');
        Route::delete('/hero-images', [InertiaToolsController::class, 'deleteHeroImage'])->name('hero-images.delete');
        Route::post('/hero-images/rebuild', [InertiaToolsController::class, 'rebuildHeroImages'])->name('hero-images.rebuild');
    });

    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inertia\AuditLogController::class, 'index'])->name('index');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/inputs', [ReportController::class, 'inputs'])->name('inputs');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    });

    // Marketplace B2B
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/carriers', [\App\Http\Controllers\Marketplace\CarrierController::class, 'index'])->name('carriers.index');
        Route::get('/carriers/{carrier}', [\App\Http\Controllers\Marketplace\CarrierController::class, 'show'])->name('carriers.show');
        Route::post('/carriers/{carrier}/invite', [\App\Http\Controllers\Marketplace\CarrierController::class, 'invite'])->name('carriers.invite');
        Route::post('/agreements/{agreement}/accept', [\App\Http\Controllers\Marketplace\CarrierController::class, 'accept'])->name('agreements.accept');
        Route::post('/agreements/{agreement}/reject', [\App\Http\Controllers\Marketplace\CarrierController::class, 'reject'])->name('agreements.reject');
    });
});

require __DIR__.'/settings.php';