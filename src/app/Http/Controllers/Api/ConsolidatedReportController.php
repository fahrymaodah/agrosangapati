<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsolidatedReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsolidatedReportController extends Controller
{
    protected $service;

    public function __construct(ConsolidatedReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Get consolidated income statement.
     */
    public function consolidatedIncomeStatement(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->generateConsolidatedIncomeStatement(
            $request->gapoktan_id,
            $request->start_date,
            $request->end_date
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get consolidated cash flow.
     */
    public function consolidatedCashFlow(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->generateConsolidatedCashFlow(
            $request->gapoktan_id,
            $request->start_date,
            $request->end_date
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get consolidated balance sheet.
     */
    public function consolidatedBalanceSheet(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
            'as_of_date' => 'required|date',
        ]);

        $result = $this->service->generateConsolidatedBalanceSheet(
            $request->gapoktan_id,
            $request->as_of_date
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get consolidated transaction summary.
     */
    public function consolidatedTransactionSummary(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|in:income,expense',
            'status' => 'nullable|in:pending,approved,rejected',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'poktan_id' => 'nullable|integer|exists:poktans,id',
        ]);

        $filters = $request->only(['type', 'status', 'category_id', 'poktan_id']);

        $result = $this->service->generateConsolidatedTransactionSummary(
            $request->gapoktan_id,
            $request->start_date,
            $request->end_date,
            $filters
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get Poktan comparison report.
     */
    public function poktanComparison(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->generatePoktanComparison(
            $request->gapoktan_id,
            $request->start_date,
            $request->end_date
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get Gapoktan summary dashboard.
     */
    public function gapoktanSummary(Request $request): JsonResponse
    {
        $request->validate([
            'gapoktan_id' => 'required|integer|exists:gapoktans,id',
        ]);

        $result = $this->service->generateGapoktanSummary($request->gapoktan_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
