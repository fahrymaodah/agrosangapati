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
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\HarvestDashboardController;

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

// ==================== COMMODITY & GRADE API ROUTES ====================

// Commodity API Routes
Route::prefix('commodities')->group(function () {
    // Public read routes
    Route::get('/', [CommodityController::class, 'index']);
    Route::get('/search', [CommodityController::class, 'search']);
    Route::get('/{id}', [CommodityController::class, 'show']);
    
    // Commodity CRUD (protected)
    Route::post('/', [CommodityController::class, 'store']);
    Route::put('/{id}', [CommodityController::class, 'update']);
    Route::delete('/{id}', [CommodityController::class, 'destroy']);
    
    // Grade routes for specific commodity
    Route::get('/{commodityId}/grades', [CommodityController::class, 'getGrades']);
    Route::post('/{commodityId}/grades', [CommodityController::class, 'storeGrade']);
    Route::get('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'showGrade']);
    Route::put('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'updateGrade']);
    Route::delete('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'destroyGrade']);
});

// All Grades API Route (across all commodities)
Route::get('/grades', [CommodityController::class, 'getAllGrades']);

// Harvest API Routes
Route::prefix('harvests')->group(function () {
    // Public read routes
    Route::get('/', [HarvestController::class, 'index']); // All harvests for poktan
    Route::get('/summary', [HarvestController::class, 'summary']); // Summary statistics
    Route::get('/by-date-range', [HarvestController::class, 'byDateRange']); // Filter by date
    Route::get('/by-status/{status}', [HarvestController::class, 'byStatus']); // Filter by status
    Route::get('/member/{memberId}', [HarvestController::class, 'byMember']); // By member
    Route::get('/{id}', [HarvestController::class, 'show']); // Show detail
    
    // Protected routes (require authentication)
    Route::post('/', [HarvestController::class, 'store']); // Create harvest
    Route::put('/{id}', [HarvestController::class, 'update']); // Update harvest
    Route::patch('/{id}/status', [HarvestController::class, 'updateStatus']); // Update status only
    Route::delete('/{id}', [HarvestController::class, 'destroy']); // Delete harvest
});

// ==================== STOCK MANAGEMENT ROUTES ====================
Route::prefix('stocks')->group(function () {
    // Gapoktan routes (must be before /{id} to avoid conflict)
    Route::get('/gapoktan', [StockController::class, 'gapoktanStocks']); // All gapoktan stocks
    Route::get('/gapoktan/summary', [StockController::class, 'gapoktanSummary']); // Gapoktan summary
    Route::post('/transfer-to-gapoktan', [StockController::class, 'transferToGapoktan']); // Transfer to gapoktan
    
    // Public read routes
    Route::get('/', [StockController::class, 'index']); // All stocks for poktan
    Route::get('/summary', [StockController::class, 'summary']); // Summary statistics
    Route::get('/low-stock', [StockController::class, 'lowStock']); // Low stock alert
    Route::get('/by-location', [StockController::class, 'byLocation']); // By location
    Route::get('/recent-movements', [StockController::class, 'recentMovements']); // Recent movements
    Route::get('/{id}', [StockController::class, 'show']); // Show detail
    Route::get('/{id}/movements', [StockController::class, 'movements']); // Stock movements
    
    // Protected routes (require authentication)
    Route::post('/add', [StockController::class, 'addStock']); // Add stock (incoming)
    Route::post('/remove', [StockController::class, 'removeStock']); // Remove stock (outgoing)
    Route::post('/transfer', [StockController::class, 'transferStock']); // Transfer between locations
    Route::post('/damage', [StockController::class, 'recordDamage']); // Record damaged stock
});

// ==================== PRODUCTION REPORT ROUTES ====================
Route::prefix('reports/production')->group(function () {
    // Member production reports
    Route::get('/member/{memberId}', [ProductionReportController::class, 'memberReport']); // Complete member report
    Route::get('/member/{memberId}/summary', [ProductionReportController::class, 'memberSummary']); // Summary only
    Route::get('/member/{memberId}/by-commodity', [ProductionReportController::class, 'memberByCommodity']); // By commodity
    Route::get('/member/{memberId}/comparison', [ProductionReportController::class, 'memberComparison']); // Period comparison
    
    // Poktan production reports
    Route::get('/poktan/{poktanId}', [ProductionReportController::class, 'poktanReport']); // Complete poktan report
    Route::get('/poktan/{poktanId}/summary', [ProductionReportController::class, 'poktanSummary']); // Summary only
    Route::get('/poktan/{poktanId}/by-commodity', [ProductionReportController::class, 'poktanByCommodity']); // By commodity
    Route::get('/poktan/{poktanId}/by-member', [ProductionReportController::class, 'poktanByMember']); // By member
    Route::get('/poktan/{poktanId}/monthly-trend', [ProductionReportController::class, 'poktanMonthlyTrend']); // Monthly trend
    Route::get('/poktan/{poktanId}/top-producers', [ProductionReportController::class, 'topProducers']); // Top producers ranking
    
    // Gapoktan production reports
    Route::get('/gapoktan/{gapoktanId}', [ProductionReportController::class, 'gapoktanReport']); // Complete gapoktan report
    Route::get('/gapoktan/{gapoktanId}/summary', [ProductionReportController::class, 'gapoktanSummary']); // Summary only
    Route::get('/gapoktan/{gapoktanId}/by-commodity', [ProductionReportController::class, 'gapoktanByCommodity']); // By commodity
    Route::get('/gapoktan/{gapoktanId}/by-poktan', [ProductionReportController::class, 'gapoktanByPoktan']); // By poktan
    Route::get('/gapoktan/{gapoktanId}/poktan-comparison', [ProductionReportController::class, 'gapoktanPoktanComparison']); // Poktan comparison
    Route::get('/gapoktan/{gapoktanId}/monthly-trend', [ProductionReportController::class, 'gapoktanMonthlyTrend']); // Monthly trend
});

// ==================== HARVEST DASHBOARD ROUTES ====================
Route::prefix('dashboard/harvest')->group(function () {
    // Poktan harvest dashboard
    Route::get('/poktan/{poktanId}', [HarvestDashboardController::class, 'poktanDashboard']); // Complete dashboard
    Route::get('/poktan/{poktanId}/cards', [HarvestDashboardController::class, 'poktanDashboardCards']); // Quick summary cards
    
    // Gapoktan harvest dashboard
    Route::get('/gapoktan/{gapoktanId}', [HarvestDashboardController::class, 'gapoktanDashboard']); // Complete dashboard
    Route::get('/gapoktan/{gapoktanId}/cards', [HarvestDashboardController::class, 'gapoktanDashboardCards']); // Quick summary cards
});
