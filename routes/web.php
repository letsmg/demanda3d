<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inertia\ClientController as InertiaClientController;
use App\Http\Controllers\Inertia\InputController as InertiaInputController;
use App\Http\Controllers\Inertia\OrderController as InertiaOrderController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');
Route::inertia('/home', 'Dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
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
});

require __DIR__.'/settings.php';
