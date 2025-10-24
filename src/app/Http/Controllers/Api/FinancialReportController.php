<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    protected FinancialReportService $service;

    public function __construct(FinancialReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Get income statement report.
     * 
     * GET /api/financial-reports/income-statement?poktan_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function incomeStatement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->generateIncomeStatement(
            $validated['poktan_id'],
            $validated['start_date'],
            $validated['end_date']
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get cash flow report.
     * 
     * GET /api/financial-reports/cash-flow?poktan_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function cashFlow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->generateCashFlowReport(
            $validated['poktan_id'],
            $validated['start_date'],
            $validated['end_date']
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get balance sheet.
     * 
     * GET /api/financial-reports/balance-sheet?poktan_id=1&as_of_date=2024-12-31
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'as_of_date' => 'required|date',
        ]);

        $result = $this->service->generateBalanceSheet(
            $validated['poktan_id'],
            $validated['as_of_date']
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get transaction summary report.
     * 
     * GET /api/financial-reports/transaction-summary?poktan_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function transactionSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'sometimes|in:income,expense',
            'status' => 'sometimes|in:pending,approved,rejected',
            'category_id' => 'sometimes|integer|exists:transaction_categories,id',
        ]);

        $filters = [];
        if (isset($validated['type'])) {
            $filters['type'] = $validated['type'];
        }
        if (isset($validated['status'])) {
            $filters['status'] = $validated['status'];
        }
        if (isset($validated['category_id'])) {
            $filters['category_id'] = $validated['category_id'];
        }

        $result = $this->service->generateTransactionSummary(
            $validated['poktan_id'],
            $validated['start_date'],
            $validated['end_date'],
            $filters
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get monthly detailed report.
     * 
     * GET /api/financial-reports/monthly-detailed?poktan_id=1&year=2024&month=10
     */
    public function monthlyDetailed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $result = $this->service->generateMonthlyDetailedReport(
            $validated['poktan_id'],
            $validated['year'],
            $validated['month']
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get comparative report.
     * 
     * GET /api/financial-reports/comparative?poktan_id=1&period1_start=...&period1_end=...&period2_start=...&period2_end=...
     */
    public function comparative(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
        ]);

        $result = $this->service->generateComparativeReport(
            $validated['poktan_id'],
            $validated['period1_start'],
            $validated['period1_end'],
            $validated['period2_start'],
            $validated['period2_end']
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }
}
