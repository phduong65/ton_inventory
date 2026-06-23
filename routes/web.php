<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Danh mục ngành hàng
    Route::resource('categories', CategoryController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-categories');

    // Sản phẩm
    Route::resource('products', ProductController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-products');

    // Nhà cung cấp
    Route::resource('suppliers', SupplierController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-suppliers');

    // Phiếu nhập / xuất
    Route::resource('transactions', TransactionController::class)
        ->except(['edit', 'update'])
        ->middleware('can:view-transactions');

    Route::post('transactions/{transaction}/submit', [TransactionController::class, 'submit'])
        ->name('transactions.submit')->middleware('can:create-transactions');

    Route::post('transactions/{transaction}/approve', [TransactionController::class, 'approve'])
        ->name('transactions.approve')->middleware('can:approve-transactions');

    Route::post('transactions/{transaction}/reject', [TransactionController::class, 'reject'])
        ->name('transactions.reject')->middleware('can:reject-transactions');

    Route::get('transactions/{transaction}/print', [TransactionController::class, 'print'])
        ->name('transactions.print')->middleware('can:print-transactions');

    Route::delete('transaction-attachments/{attachment}', [TransactionController::class, 'destroyAttachment'])
        ->name('transaction-attachments.destroy')->middleware('can:edit-transactions');

    // Tồn kho
    Route::get('inventory', [InventoryController::class, 'index'])
        ->name('inventory.index')->middleware('can:view-inventory');

    // Thẻ kho
    Route::get('stock-ledger', [StockLedgerController::class, 'index'])
        ->name('stock-ledger.index')->middleware('can:view-stock-ledger');

    // Kiểm kê
    Route::resource('stocktakes', StocktakeController::class)
        ->except(['edit', 'update'])
        ->middleware('can:view-stocktakes');

    Route::post('stocktakes/{stocktake}/submit', [StocktakeController::class, 'submit'])
        ->name('stocktakes.submit')->middleware('can:create-stocktakes');

    Route::post('stocktakes/{stocktake}/approve', [StocktakeController::class, 'approve'])
        ->name('stocktakes.approve')->middleware('can:approve-stocktakes');

    // Người dùng
    Route::resource('users', UserController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:manage-users');

    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password')->middleware('can:manage-users');
});
