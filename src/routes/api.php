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
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SalesDistributionController;
use App\Http\Controllers\Api\SalesReportController;
use App\Http\Controllers\Api\MarketingDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\ActivityLogController;

// ============================================================
// AUTHENTICATION ROUTES (Public - No Auth Required)
// ============================================================
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// ============================================================
// PASSWORD RESET ROUTES (Public - No Auth Required)
// ============================================================
Route::prefix('password')->group(function () {
    // Public routes
    Route::post('/forgot', [PasswordResetController::class, 'forgotPassword']); // Request reset token
    Route::post('/validate-token', [PasswordResetController::class, 'validateToken']); // Validate token
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']); // Reset password with token
    Route::get('/check-token/{email}', [PasswordResetController::class, 'checkToken']); // Check if token exists
    Route::delete('/cancel', [PasswordResetController::class, 'cancelResetRequest']); // Cancel reset request
    
    // Admin/Cron route (protected)
    Route::post('/cleanup-expired', [PasswordResetController::class, 'cleanupExpired'])
        ->middleware(['auth:sanctum', 'role:superadmin']); // Only superadmin
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============================================================
// USER MANAGEMENT ROUTES (Protected - Requires Authentication)
// ============================================================
Route::prefix('users')->middleware(['auth:sanctum', 'permission:view-users'])->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/active', [UserController::class, 'active']);
    Route::get('/{id}', [UserController::class, 'show']);
    
    // Management routes (require manage-users permission)
    Route::middleware('permission:manage-users')->group(function () {
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});

// ============================================================
// TRANSACTION CATEGORY ROUTES (Protected)
// ============================================================
Route::prefix('transaction-categories')->middleware('auth:sanctum')->group(function () {
    // Read routes (all authenticated users can view)
    Route::get('/', [TransactionCategoryController::class, 'index']);
    Route::get('/income', [TransactionCategoryController::class, 'income']);
    Route::get('/expense', [TransactionCategoryController::class, 'expense']);
    Route::get('/{id}', [TransactionCategoryController::class, 'show']);

    // Management routes (require manage-categories permission)
    Route::middleware('permission:manage-categories')->group(function () {
        Route::post('/', [TransactionCategoryController::class, 'store']);
        Route::put('/{id}', [TransactionCategoryController::class, 'update']);
        Route::delete('/{id}', [TransactionCategoryController::class, 'destroy']);
    });
});

// ============================================================
// TRANSACTION ROUTES (Protected)
// ============================================================
Route::prefix('transactions')->middleware(['auth:sanctum', 'permission:view-transactions'])->group(function () {
    // Read routes
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/pending', [TransactionController::class, 'pending']);
    Route::get('/recent', [TransactionController::class, 'recent']);
    Route::get('/summary', [TransactionController::class, 'summary']);
    Route::get('/{id}', [TransactionController::class, 'show']);
    Route::get('/{id}/approval-history', [TransactionController::class, 'approvalHistory']);

    // Management routes (require manage-transactions permission)
    Route::middleware('permission:manage-transactions')->group(function () {
        Route::post('/', [TransactionController::class, 'store']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });

    // Approval routes (require approve-transactions permission)
    Route::middleware('permission:approve-transactions')->group(function () {
        Route::post('/{id}/approve', [TransactionController::class, 'approve']);
        Route::post('/{id}/reject', [TransactionController::class, 'reject']);
        Route::post('/bulk-approve', [TransactionController::class, 'bulkApprove']);
    });
});

// ============================================================
// CASH BALANCE ROUTES (Protected - View Permission Required)
// ============================================================
Route::prefix('cash-balances')->middleware(['auth:sanctum', 'permission:view-cash-balance'])->group(function () {
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

// ============================================================
// FINANCIAL REPORT ROUTES (Protected - Poktan Level)
// ============================================================
Route::prefix('financial-reports')->middleware(['auth:sanctum', 'permission:view-poktan-reports'])->group(function () {
    Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement']);
    Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow']);
    Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
    Route::get('/transaction-summary', [FinancialReportController::class, 'transactionSummary']);
    Route::get('/monthly-detailed', [FinancialReportController::class, 'monthlyDetailed']);
    Route::get('/comparative', [FinancialReportController::class, 'comparative']);
});

// ============================================================
// CONSOLIDATED REPORT ROUTES (Protected - Gapoktan Level Only)
// ============================================================
Route::prefix('consolidated-reports')->middleware(['auth:sanctum', 'permission:view-consolidated-reports'])->group(function () {
    Route::get('/income-statement', [ConsolidatedReportController::class, 'consolidatedIncomeStatement']);
    Route::get('/cash-flow', [ConsolidatedReportController::class, 'consolidatedCashFlow']);
    Route::get('/balance-sheet', [ConsolidatedReportController::class, 'consolidatedBalanceSheet']);
    Route::get('/transaction-summary', [ConsolidatedReportController::class, 'consolidatedTransactionSummary']);
    Route::get('/poktan-comparison', [ConsolidatedReportController::class, 'poktanComparison']);
    Route::get('/summary', [ConsolidatedReportController::class, 'gapoktanSummary']);
});

// ============================================================
// DASHBOARD ROUTES (Protected)
// ============================================================
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    // Poktan dashboard (poktan-level users can access)
    Route::get('/poktan/{poktanId}', [DashboardController::class, 'poktanDashboard'])
        ->middleware('permission:view-poktan-dashboard');
    
    // Gapoktan dashboard (gapoktan-level users only)
    Route::get('/gapoktan/{gapoktanId}', [DashboardController::class, 'gapoktanDashboard'])
        ->middleware('permission:view-gapoktan-dashboard');
});

// ============================================================
// COMMODITY & GRADE ROUTES (Protected)
// ============================================================
Route::prefix('commodities')->middleware('auth:sanctum')->group(function () {
    // Read routes (all authenticated users can view)
    Route::get('/', [CommodityController::class, 'index']);
    Route::get('/search', [CommodityController::class, 'search']);
    Route::get('/{id}', [CommodityController::class, 'show']);
    Route::get('/{commodityId}/grades', [CommodityController::class, 'getGrades']);
    Route::get('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'showGrade']);
    
    // Management routes (gapoktan level only)
    Route::middleware('permission:manage-commodities')->group(function () {
        Route::post('/', [CommodityController::class, 'store']);
        Route::put('/{id}', [CommodityController::class, 'update']);
        Route::delete('/{id}', [CommodityController::class, 'destroy']);
        Route::post('/{commodityId}/grades', [CommodityController::class, 'storeGrade']);
        Route::put('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'updateGrade']);
        Route::delete('/{commodityId}/grades/{gradeId}', [CommodityController::class, 'destroyGrade']);
    });
});

// All Grades API Route (Protected)
Route::get('/grades', [CommodityController::class, 'getAllGrades'])->middleware('auth:sanctum');

// ============================================================
// HARVEST ROUTES (Protected)
// ============================================================
Route::prefix('harvests')->middleware(['auth:sanctum', 'permission:view-production-reports'])->group(function () {
    // Read routes
    Route::get('/', [HarvestController::class, 'index']); // All harvests for poktan
    Route::get('/summary', [HarvestController::class, 'summary']); // Summary statistics
    Route::get('/by-date-range', [HarvestController::class, 'byDateRange']); // Filter by date
    Route::get('/by-status/{status}', [HarvestController::class, 'byStatus']); // Filter by status
    Route::get('/member/{memberId}', [HarvestController::class, 'byMember']); // By member
    Route::get('/{id}', [HarvestController::class, 'show']); // Show detail
    
    // Management routes (require manage-harvests permission)
    Route::middleware('permission:manage-harvests')->group(function () {
        Route::post('/', [HarvestController::class, 'store']); // Create harvest
        Route::put('/{id}', [HarvestController::class, 'update']); // Update harvest
        Route::patch('/{id}/status', [HarvestController::class, 'updateStatus']); // Update status only
        Route::delete('/{id}', [HarvestController::class, 'destroy']); // Delete harvest
    });
});

// ============================================================
// STOCK MANAGEMENT ROUTES (Protected)
// ============================================================
Route::prefix('stocks')->middleware(['auth:sanctum', 'permission:view-stocks'])->group(function () {
    // Gapoktan stock routes (gapoktan level only)
    Route::middleware('permission:view-gapoktan-stocks')->group(function () {
        Route::get('/gapoktan', [StockController::class, 'gapoktanStocks']); // All gapoktan stocks
        Route::get('/gapoktan/summary', [StockController::class, 'gapoktanSummary']); // Gapoktan summary
    });
    
    // Read routes (poktan level)
    Route::get('/', [StockController::class, 'index']); // All stocks for poktan
    Route::get('/summary', [StockController::class, 'summary']); // Summary statistics
    Route::get('/low-stock', [StockController::class, 'lowStock']); // Low stock alert
    Route::get('/by-location', [StockController::class, 'byLocation']); // By location
    Route::get('/recent-movements', [StockController::class, 'recentMovements']); // Recent movements
    Route::get('/{id}', [StockController::class, 'show']); // Show detail
    Route::get('/{id}/movements', [StockController::class, 'movements']); // Stock movements
    
    // Management routes (require manage-stocks permission)
    Route::middleware('permission:manage-stocks')->group(function () {
        Route::post('/add', [StockController::class, 'addStock']); // Add stock (incoming)
        Route::post('/remove', [StockController::class, 'removeStock']); // Remove stock (outgoing)
        Route::post('/transfer', [StockController::class, 'transferStock']); // Transfer between locations
        Route::post('/damage', [StockController::class, 'recordDamage']); // Record damaged stock
    });
    
    // Transfer to gapoktan (require transfer-to-gapoktan permission)
    Route::post('/transfer-to-gapoktan', [StockController::class, 'transferToGapoktan'])
        ->middleware('permission:transfer-to-gapoktan');
});

// ============================================================
// PRODUCTION REPORT ROUTES (Protected)
// ============================================================
Route::prefix('reports/production')->middleware(['auth:sanctum', 'permission:view-production-reports'])->group(function () {
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
    
    // Gapoktan production reports (gapoktan level only)
    Route::middleware('permission:view-gapoktan-production')->group(function () {
        Route::get('/gapoktan/{gapoktanId}', [ProductionReportController::class, 'gapoktanReport']); // Complete gapoktan report
        Route::get('/gapoktan/{gapoktanId}/summary', [ProductionReportController::class, 'gapoktanSummary']); // Summary only
        Route::get('/gapoktan/{gapoktanId}/by-commodity', [ProductionReportController::class, 'gapoktanByCommodity']); // By commodity
        Route::get('/gapoktan/{gapoktanId}/by-poktan', [ProductionReportController::class, 'gapoktanByPoktan']); // By poktan
        Route::get('/gapoktan/{gapoktanId}/poktan-comparison', [ProductionReportController::class, 'gapoktanPoktanComparison']); // Poktan comparison
        Route::get('/gapoktan/{gapoktanId}/monthly-trend', [ProductionReportController::class, 'gapoktanMonthlyTrend']); // Monthly trend
    });
});

// ============================================================
// HARVEST DASHBOARD ROUTES (Protected)
// ============================================================
Route::prefix('dashboard/harvest')->middleware('auth:sanctum')->group(function () {
    // Poktan harvest dashboard
    Route::middleware('permission:view-poktan-dashboard')->group(function () {
        Route::get('/poktan/{poktanId}', [HarvestDashboardController::class, 'poktanDashboard']); // Complete dashboard
        Route::get('/poktan/{poktanId}/cards', [HarvestDashboardController::class, 'poktanDashboardCards']); // Quick summary cards
    });
    
    // Gapoktan harvest dashboard (gapoktan level only)
    Route::middleware('permission:view-gapoktan-dashboard')->group(function () {
        Route::get('/gapoktan/{gapoktanId}', [HarvestDashboardController::class, 'gapoktanDashboard']); // Complete dashboard
        Route::get('/gapoktan/{gapoktanId}/cards', [HarvestDashboardController::class, 'gapoktanDashboardCards']); // Quick summary cards
    });
});

// ============================================================
// PRODUCT MANAGEMENT ROUTES (FASE 3: PEMASARAN)
// ============================================================
Route::prefix('products')->group(function () {
    // Public routes (catalog) - NO authentication required
    Route::get('/catalog', [ProductController::class, 'catalog']); // Public product catalog
    Route::get('/available', [ProductController::class, 'available']); // Available products (in stock)
    Route::get('/popular', [ProductController::class, 'popular']); // Popular products by views
    Route::get('/search', [ProductController::class, 'search']); // Search products
    
    // Protected routes (gapoktan level - require authentication)
    Route::middleware(['auth:sanctum', 'permission:manage-products'])->group(function () {
        Route::get('/', [ProductController::class, 'index']); // All products
        Route::get('/statistics', [ProductController::class, 'statistics']); // Product statistics
        Route::get('/commodity/{commodityId}', [ProductController::class, 'byCommodity']); // Products by commodity
        Route::get('/status/{status}', [ProductController::class, 'byStatus']); // Products by status
        Route::get('/{id}', [ProductController::class, 'show']); // Product detail
        Route::post('/', [ProductController::class, 'store']); // Create product
        Route::put('/{id}', [ProductController::class, 'update']); // Update product
        Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete product
        Route::patch('/{id}/status', [ProductController::class, 'updateStatus']); // Update status only
        Route::post('/{id}/sync-stock', [ProductController::class, 'syncStock']); // Sync with gapoktan stock
    });
});

// ============================================================
// ORDER MANAGEMENT ROUTES (FASE 3: PEMASARAN)
// ============================================================
Route::prefix('orders')->group(function () {
    // Public routes (for customers/buyers) - NO authentication required
    Route::post('/', [OrderController::class, 'store']); // Create order (public)
    Route::post('/calculate', [OrderController::class, 'calculate']); // Calculate order price (public)
    Route::get('/track/{orderNumber}', [OrderController::class, 'track']); // Track order by order number (public)
    Route::get('/by-phone/{phone}', [OrderController::class, 'byPhone']); // Get orders by phone (public)
    
    // Protected routes (gapoktan level - require authentication)
    Route::middleware(['auth:sanctum', 'permission:manage-orders'])->group(function () {
        Route::get('/', [OrderController::class, 'index']); // All orders with filters
        Route::get('/pending', [OrderController::class, 'pending']); // Pending orders
        Route::get('/active', [OrderController::class, 'active']); // Active orders
        Route::get('/completed', [OrderController::class, 'completed']); // Completed orders
        Route::get('/statistics', [OrderController::class, 'statistics']); // Order statistics
        Route::get('/{id}', [OrderController::class, 'show']); // Order detail
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']); // Cancel order
        
        // Order management (process-orders permission)
        Route::middleware('permission:process-orders')->group(function () {
            Route::post('/{id}/confirm', [OrderController::class, 'confirm']); // Confirm order
            Route::post('/{id}/reject', [OrderController::class, 'reject']); // Reject order
            Route::patch('/{id}/status', [OrderController::class, 'updateStatus']); // Update order status
            Route::patch('/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']); // Update payment status
            Route::post('/{id}/processing', [OrderController::class, 'markAsProcessing']); // Mark as processing
            Route::post('/{id}/shipped', [OrderController::class, 'markAsShipped']); // Mark as shipped
            Route::post('/{id}/delivered', [OrderController::class, 'markAsDelivered']); // Mark as delivered
        });
        
        // Shipment management (nested under orders)
        Route::middleware('permission:manage-shipments')->group(function () {
            Route::post('/{orderId}/shipment', [ShipmentController::class, 'store']); // Create shipment for order
            Route::get('/{orderId}/shipment', [ShipmentController::class, 'getByOrderId']); // Get shipment by order ID
        });
    });
});

// ============================================================
// SHIPMENT MANAGEMENT ROUTES (FASE 3: PEMASARAN)
// ============================================================
Route::prefix('shipments')->group(function () {
    // Public routes - NO authentication required
    Route::get('/track/{trackingNumber}', [ShipmentController::class, 'track']); // Track by tracking number (public)
    
    // Protected routes (gapoktan level - require authentication)
    Route::middleware(['auth:sanctum', 'permission:manage-shipments'])->group(function () {
        Route::get('/', [ShipmentController::class, 'index']); // All shipments with filters
        Route::get('/in-progress', [ShipmentController::class, 'inProgress']); // In progress shipments
        Route::get('/late', [ShipmentController::class, 'late']); // Late shipments
        Route::get('/statistics', [ShipmentController::class, 'statistics']); // Shipment statistics
        Route::get('/courier/{courier}', [ShipmentController::class, 'byCourier']); // By courier
        Route::get('/{id}', [ShipmentController::class, 'show']); // Shipment detail
        Route::put('/{id}', [ShipmentController::class, 'update']); // Update shipment
        Route::delete('/{id}', [ShipmentController::class, 'destroy']); // Delete shipment
        
        // Shipment status updates
        Route::post('/{id}/picked-up', [ShipmentController::class, 'markAsPickedUp']); // Mark as picked up
        Route::post('/{id}/in-transit', [ShipmentController::class, 'markAsInTransit']); // Mark as in transit
        Route::post('/{id}/delivered', [ShipmentController::class, 'markAsDelivered']); // Mark as delivered
        Route::post('/{id}/proof-photo', [ShipmentController::class, 'uploadProofPhoto']); // Upload proof photo
    });
});

// ============================================================
// SALES DISTRIBUTION ROUTES (FASE 3: PEMASARAN - PMR-005)
// ============================================================
Route::prefix('sales-distributions')->middleware(['auth:sanctum', 'permission:manage-distributions'])->group(function () {
    Route::get('/', [SalesDistributionController::class, 'index']); // All distributions with filters
    Route::get('/pending', [SalesDistributionController::class, 'getPending']); // Pending distributions
    Route::get('/paid', [SalesDistributionController::class, 'getPaid']); // Paid distributions
    Route::get('/statistics', [SalesDistributionController::class, 'getStatistics']); // Distribution statistics
    Route::get('/pending-summary', [SalesDistributionController::class, 'getPendingPaymentSummary']); // Pending summary by poktan
    Route::get('/{id}', [SalesDistributionController::class, 'show']); // Distribution detail
    
    // Distribution calculation
    Route::post('/calculate/{orderId}', [SalesDistributionController::class, 'calculateForOrder']); // Calculate for order
    Route::get('/order/{orderId}', [SalesDistributionController::class, 'getByOrderId']); // Get by order ID
    
    // Distribution by poktan
    Route::get('/poktan/{poktanId}', [SalesDistributionController::class, 'getByPoktanId']); // Get by poktan ID
    
    // Payment actions
    Route::post('/{id}/mark-paid', [SalesDistributionController::class, 'markAsPaid']); // Mark as paid
    Route::post('/batch-mark-paid', [SalesDistributionController::class, 'batchMarkAsPaid']); // Batch mark as paid
});

// ============================================================
// SALES REPORT ROUTES (FASE 3: PEMASARAN - PMR-007)
// ============================================================
Route::prefix('reports/sales')->middleware(['auth:sanctum', 'permission:view-gapoktan-reports'])->group(function () {
    Route::get('/summary', [SalesReportController::class, 'summary']); // Sales summary statistics
    Route::get('/by-product', [SalesReportController::class, 'byProduct']); // Sales by product
    Route::get('/by-poktan', [SalesReportController::class, 'byPoktan']); // Sales by poktan
    Route::get('/best-selling', [SalesReportController::class, 'bestSelling']); // Best selling products
    Route::get('/revenue-analysis', [SalesReportController::class, 'revenueAnalysis']); // Revenue trends
    Route::get('/top-customers', [SalesReportController::class, 'topCustomers']); // Top customers
    Route::get('/complete', [SalesReportController::class, 'complete']); // Complete report (all data)
});

// ============================================================
// MARKETING DASHBOARD ROUTES (FASE 3: PEMASARAN - PMR-008)
// ============================================================
Route::prefix('dashboard/marketing')->middleware(['auth:sanctum', 'permission:view-gapoktan-dashboard'])->group(function () {
    Route::get('/', [MarketingDashboardController::class, 'index']); // Complete dashboard
    Route::get('/summary', [MarketingDashboardController::class, 'summary']); // Summary cards
    Route::get('/quick-summary', [MarketingDashboardController::class, 'quickSummary']); // Quick summary (current month)
    Route::get('/revenue-trend', [MarketingDashboardController::class, 'revenueTrend']); // Revenue trend chart
    Route::get('/top-products', [MarketingDashboardController::class, 'topProducts']); // Top selling products
    Route::get('/recent-orders', [MarketingDashboardController::class, 'recentOrders']); // Recent orders list
    Route::get('/pending-payments', [MarketingDashboardController::class, 'pendingPayments']); // Pending payments alert
});

// ============================================================
// ACTIVITY LOG ROUTES (FASE 6: ADDITIONAL - ADD-003)
// ============================================================
Route::prefix('activity-logs')->middleware('auth:sanctum')->group(function () {
    // List & detail
    Route::get('/', [ActivityLogController::class, 'index']); // List all logs with pagination
    Route::get('/{id}', [ActivityLogController::class, 'show']); // Get log detail
    
    // Filtering & searching
    Route::get('/user/{userId}', [ActivityLogController::class, 'byUser']); // Logs by user
    Route::post('/by-model', [ActivityLogController::class, 'byModel']); // Logs by model type + ID
    Route::get('/model-type/{modelType}', [ActivityLogController::class, 'byModelType']); // Logs by model type
    Route::get('/event/{event}', [ActivityLogController::class, 'byEvent']); // Logs by event (created/updated/deleted)
    Route::post('/date-range', [ActivityLogController::class, 'byDateRange']); // Logs by date range
    Route::post('/filter', [ActivityLogController::class, 'filter']); // Advanced filtering
    Route::get('/search', [ActivityLogController::class, 'search']); // Search logs
    
    // Summary & analytics
    Route::get('/recent/list', [ActivityLogController::class, 'recent']); // Recent logs
    Route::get('/statistics/summary', [ActivityLogController::class, 'statistics']); // Activity statistics
    Route::get('/dashboard/data', [ActivityLogController::class, 'dashboard']); // Dashboard data
    Route::get('/user/{userId}/summary', [ActivityLogController::class, 'userSummary']); // User activity summary
    
    // Custom logging
    Route::post('/custom', [ActivityLogController::class, 'logCustom']); // Log custom activity
});
