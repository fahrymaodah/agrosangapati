<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\TransactionCategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CashBalanceController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\ConsolidatedReportController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// User API Routes
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/active', [UserController::class, 'active']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// Transaction Category API Routes
Route::prefix('transaction-categories')->group(function () {
    // Public routes (read-only, for viewing available categories)
    Route::get('/', [TransactionCategoryController::class, 'index']);
    Route::get('/income', [TransactionCategoryController::class, 'income']);
    Route::get('/expense', [TransactionCategoryController::class, 'expense']);
    Route::get('/{id}', [TransactionCategoryController::class, 'show']);

    // Protected routes (require authentication and proper roles)
    Route::middleware(['role:superadmin,ketua_gapoktan,pengurus_gapoktan,ketua_poktan,pengurus_poktan'])->group(function () {
        Route::post('/', [TransactionCategoryController::class, 'store']);
        Route::put('/{id}', [TransactionCategoryController::class, 'update']);
        Route::delete('/{id}', [TransactionCategoryController::class, 'destroy']);
    });
});

// Transaction API Routes
Route::prefix('transactions')->group(function () {
    // Public read routes
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/pending', [TransactionController::class, 'pending']);
    Route::get('/recent', [TransactionController::class, 'recent']);
    Route::get('/summary', [TransactionController::class, 'summary']);
    Route::get('/{id}', [TransactionController::class, 'show']);

    // Protected routes (require authentication and proper roles)
    Route::middleware(['role:superadmin,ketua_gapoktan,pengurus_gapoktan,ketua_poktan,pengurus_poktan,anggota'])->group(function () {
        Route::post('/', [TransactionController::class, 'store']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });

    // Approval route (only for ketua/pengurus)
    Route::middleware(['role:superadmin,ketua_gapoktan,pengurus_gapoktan,ketua_poktan,pengurus_poktan'])->group(function () {
        Route::post('/{id}/approve', [TransactionController::class, 'approve']);
        Route::post('/{id}/reject', [TransactionController::class, 'reject']);
        Route::post('/bulk-approve', [TransactionController::class, 'bulkApprove']);
    });

    // Approval history route (public for transparency)
    Route::get('/{id}/approval-history', [TransactionController::class, 'approvalHistory']);
});

// Cash Balance API Routes
Route::prefix('cash-balances')->group(function () {
    // Public read routes (all authenticated users can view)
    Route::get('/', [CashBalanceController::class, 'index']); // All balances
    Route::get('/total', [CashBalanceController::class, 'total']); // Total balance
    Route::get('/alerts', [CashBalanceController::class, 'alerts']); // Low balance alerts
    Route::get('/{poktanId}', [CashBalanceController::class, 'show']); // Specific poktan balance
    Route::get('/{poktanId}/history', [CashBalanceController::class, 'history']); // Balance history
    Route::get('/{poktanId}/recent', [CashBalanceController::class, 'recent']); // Recent changes
    Route::get('/{poktanId}/statistics', [CashBalanceController::class, 'statistics']); // Statistics
    Route::get('/{poktanId}/monthly-summary', [CashBalanceController::class, 'monthlySummary']); // Monthly summary
    Route::get('/{poktanId}/trend', [CashBalanceController::class, 'trend']); // Balance trend
    Route::post('/{poktanId}/can-transact', [CashBalanceController::class, 'canTransact']); // Check if transaction allowed
});

// Financial Report API Routes
Route::prefix('financial-reports')->group(function () {
    // All report routes are read-only
    Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement']);
    Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow']);
    Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
    Route::get('/transaction-summary', [FinancialReportController::class, 'transactionSummary']);
    Route::get('/monthly-detailed', [FinancialReportController::class, 'monthlyDetailed']);
    Route::get('/comparative', [FinancialReportController::class, 'comparative']);
});

// Consolidated Report API Routes (Gapoktan Level)
Route::prefix('consolidated-reports')->group(function () {
    // All consolidated report routes are read-only
    Route::get('/income-statement', [ConsolidatedReportController::class, 'consolidatedIncomeStatement']);
    Route::get('/cash-flow', [ConsolidatedReportController::class, 'consolidatedCashFlow']);
    Route::get('/balance-sheet', [ConsolidatedReportController::class, 'consolidatedBalanceSheet']);
    Route::get('/transaction-summary', [ConsolidatedReportController::class, 'consolidatedTransactionSummary']);
    Route::get('/poktan-comparison', [ConsolidatedReportController::class, 'poktanComparison']);
    Route::get('/summary', [ConsolidatedReportController::class, 'gapoktanSummary']);
});

// Dashboard API Routes
Route::prefix('dashboard')->group(function () {
    // Poktan dashboard
    Route::get('/poktan/{poktanId}', [DashboardController::class, 'poktanDashboard']);
    
    // Gapoktan dashboard (consolidated)
    Route::get('/gapoktan/{gapoktanId}', [DashboardController::class, 'gapoktanDashboard']);
});

