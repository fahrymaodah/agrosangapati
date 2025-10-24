<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CashBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashBalanceController extends Controller
{
    protected CashBalanceService $service;

    public function __construct(CashBalanceService $service)
    {
        $this->service = $service;
    }

    /**
     * Get current balance for a specific poktan.
     * 
     * @param int $poktanId
     * @return JsonResponse
     */
    public function show(int $poktanId): JsonResponse
    {
        $result = $this->service->getCurrentBalance($poktanId);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Get all poktan balances.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->service->getAllBalances();

        return response()->json($result);
    }

    /**
     * Get balance history for a specific poktan.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function history(int $poktanId, Request $request): JsonResponse
    {
        $filters = [
            'type' => $request->query('type'), // income or expense
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Remove null filters
        $filters = array_filter($filters, fn($value) => !is_null($value));

        $result = $this->service->getBalanceHistory($poktanId, $filters);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get recent balance changes for a poktan.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function recent(int $poktanId, Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        
        $result = $this->service->getRecentChanges($poktanId, $limit);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get low balance alerts.
     * 
     * @return JsonResponse
     */
    public function alerts(): JsonResponse
    {
        $result = $this->service->getLowBalanceAlerts();

        return response()->json($result);
    }

    /**
     * Get balance statistics for a poktan.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(int $poktanId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $result = $this->service->getBalanceStatistics(
            $poktanId,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get monthly balance summary for a poktan.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlySummary(int $poktanId, Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $result = $this->service->getMonthlyBalanceSummary(
            $poktanId,
            $request->input('year')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get balance trend for charts.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function trend(int $poktanId, Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        
        $result = $this->service->getBalanceTrend($poktanId, $days);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Check if a transaction is allowed based on balance.
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function canTransact(int $poktanId, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->service->canTransact($poktanId, $request->input('amount'));

        return response()->json($result);
    }

    /**
     * Get total balance across all poktans.
     * 
     * @return JsonResponse
     */
    public function total(): JsonResponse
    {
        $result = $this->service->getTotalBalance();

        return response()->json($result);
    }
}
