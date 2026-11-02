<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\SupplierController;
use App\Livewire\Catalog\ProductManager;
use App\Livewire\Catalog\SupplierManager;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Inventory\InventoryManager;
use App\Livewire\Reports\ReportOverview;
use App\Livewire\Transactions\StockTransactionManager;
use App\Livewire\Users\UserManager;
use App\Livewire\Forecasts\ForecastManager;
use App\Livewire\Explainability\ExplanationManager;
use App\Livewire\Recommendations\RecommendationManager;
use App\Livewire\AuditLogs\AuditLogManager;

Route::view('/', 'welcome');

Route::get('dashboard', Overview::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('products', ProductManager::class)->name('products.index');
    Route::get('suppliers', SupplierManager::class)->name('suppliers.index');
    Route::get('inventory', InventoryManager::class)->name('inventory.index');
    Route::get('stock-transactions', StockTransactionManager::class)->name('stock-transactions.index');
    Route::get('reports', ReportOverview::class)->name('reports.index');
    Route::get('users', UserManager::class)->middleware('role:admin')->name('users.index');
    Route::get('forecasts', ForecastManager::class)->name('forecasts.index');
    Route::get('explainability', ExplanationManager::class)->name('explainability.index');
    Route::get('recommendations', RecommendationManager::class)->name('recommendations.index');
});

Route::middleware(['auth', 'verified'])->prefix('internal')->name('internal.')->group(function () {
    Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);
    Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
    Route::apiResource('suppliers', SupplierController::class)->except(['create', 'edit']);
    Route::post('suppliers/{supplier}/products/{product}', [SupplierController::class, 'attachProduct'])->name('suppliers.products.attach');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('stock-transactions', [StockTransactionController::class, 'index'])->name('stock-transactions.index');
    Route::post('stock-transactions', [StockTransactionController::class, 'store'])->name('stock-transactions.store');
    Route::post('stock-transactions/{stockTransaction}/reverse', [StockTransactionController::class, 'reverse'])->name('stock-transactions.reverse');
    Route::get('audit-logs', AuditLogManager::class)->middleware('role:admin')->name('audit-logs.index');
    Route::get('audit-logs/data', [AuditLogController::class, 'index'])->middleware('role:admin')->name('audit-logs.data');
});
