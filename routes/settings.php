<?php

use App\Http\Controllers\Inertia\BankDetailController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('settings/tenant', [ProfileController::class, 'updateTenant'])->name('tenant.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    // Bank details (dados bancários) — Admin vê todos, SELLER_1 vê apenas o próprio
    // Admin: listagem de todos os vendedores
    Route::get('admin/bank', [BankDetailController::class, 'adminIndex'])
        ->middleware(\App\Http\Middleware\CheckAccessLevel::class . ':10')
        ->name('admin.bank.index');
    Route::get('admin/bank/{tenant}/edit', [BankDetailController::class, 'adminEdit'])
        ->middleware(\App\Http\Middleware\CheckAccessLevel::class . ':10')
        ->name('admin.bank.edit');

    // Seller 1: apenas o próprio tenant
    Route::get('settings/bank', [BankDetailController::class, 'edit'])
        ->middleware(\App\Http\Middleware\CheckAccessLevel::class . ':10,1')
        ->name('bank.edit');
    Route::post('settings/bank', [BankDetailController::class, 'store'])
        ->middleware(\App\Http\Middleware\CheckAccessLevel::class . ':10,1')
        ->name('bank.store');
});

// BrasilAPI — CNPJ lookup (API interna)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/brasilapi/cnpj', [BankDetailController::class, 'lookupCnpj'])->name('brasilapi.cnpj');
});

// Dados bancários da transportadora (guard: carriers)
Route::middleware(['auth:carriers'])->group(function () {
    Route::get('/carrier/bank', [BankDetailController::class, 'editCarrier'])->name('carrier.bank.edit');
    Route::post('/carrier/bank', [BankDetailController::class, 'storeCarrier'])->name('carrier.bank.store');
});

// Verificação de dados bancários via e-mail (link público, sem auth)
Route::get('/bank/verify', [BankDetailController::class, 'verify'])->name('bank.verify');
