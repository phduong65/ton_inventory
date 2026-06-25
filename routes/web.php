<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Dev\TestRunnerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\UnitController;
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

    // Đơn vị tính
    Route::resource('units', UnitController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-units');

    // Sản phẩm
    Route::resource('products', ProductController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-products');

    // Nhà cung cấp
    Route::resource('suppliers', SupplierController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:view-suppliers');

    // Kho nhận hàng
    Route::resource('destinations', DestinationController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:manage-destinations');

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
    Route::get('inventory/export', [InventoryController::class, 'export'])
        ->name('inventory.export')->middleware('can:export-inventory');

    // Thẻ kho
    Route::get('stock-ledger', [StockLedgerController::class, 'index'])
        ->name('stock-ledger.index')->middleware('can:view-stock-ledger');
    Route::get('stock-ledger/export', [StockLedgerController::class, 'export'])
        ->name('stock-ledger.export')->middleware('can:export-stock-ledger');

    // Kiểm kê
    Route::resource('stocktakes', StocktakeController::class)
        ->except(['edit', 'update'])
        ->middleware('can:view-stocktakes');

    Route::post('stocktakes/{stocktake}/submit', [StocktakeController::class, 'submit'])
        ->name('stocktakes.submit')->middleware('can:create-stocktakes');

    Route::post('stocktakes/{stocktake}/approve', [StocktakeController::class, 'approve'])
        ->name('stocktakes.approve')->middleware('can:approve-stocktakes');

    // Báo cáo
    Route::prefix('reports')->name('reports.')->middleware('can:view-reports')->group(function () {
        Route::get('receipts',      [ReportController::class, 'receipts'])->name('receipts');
        Route::get('issues',        [ReportController::class, 'issues'])->name('issues');
        Route::get('inventory',     [ReportController::class, 'inventory'])->name('inventory');
        Route::get('summary',       [ReportController::class, 'summary'])->name('summary');
        Route::get('internal-debt', [ReportController::class, 'internalDebt'])->name('internal-debt');

        // Redirect sang inventory.index để thống nhất giao diện tồn kho
        Route::get('destination-inventory', function (\Illuminate\Http\Request $req) {
            return redirect()->route('inventory.index', $req->query());
        })->name('destination-inventory');
        Route::get('destination-inventory/export', function (\Illuminate\Http\Request $req) {
            return redirect()->route('inventory.export', $req->query());
        })->name('destination-inventory.export');

        Route::get('receipts/export',  [ReportController::class, 'exportReceipts'])->name('receipts.export')->middleware('can:export-reports');
        Route::get('issues/export',    [ReportController::class, 'exportIssues'])->name('issues.export')->middleware('can:export-reports');
        Route::get('summary/export',   [ReportController::class, 'exportSummary'])->name('summary.export')->middleware('can:export-reports');
    });

    // Người dùng
    Route::resource('users', UserController::class)
        ->except(['create', 'edit', 'show'])
        ->middleware('can:manage-users');

    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password')->middleware('can:manage-users');

    // Lịch sử hoạt động
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index')->middleware('can:view-activity-logs');

    // Cài đặt hệ thống
    Route::get('settings', [SettingController::class, 'index'])
        ->name('settings.index')->middleware('can:manage-settings');
    Route::put('settings', [SettingController::class, 'update'])
        ->name('settings.update')->middleware('can:manage-settings');
});

// Dev tools — local only
Route::middleware('App\Http\Middleware\LocalOnly')->prefix('dev')->name('dev.')->group(function () {
    Route::get('test-runner',      [TestRunnerController::class, 'index'])->name('test-runner');
    Route::post('test-runner/run', [TestRunnerController::class, 'run'])->name('test-runner.run');
    Route::get('notifications',    fn() => view('dev.notifications'))->name('notifications');
});
