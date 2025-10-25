<?php

namespace App\Http\Controllers;

use App\Services\SalesDistributionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class SalesDistributionController extends Controller
{
    protected $distributionService;

    public function __construct(SalesDistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    /**
     * Display a listing of sales distributions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'poktan_id' => $request->query('poktan_id'),
                'payment_status' => $request->query('payment_status'),
                'start_date' => $request->query('start_date'),
                'end_date' => $request->query('end_date'),
                'paid_start_date' => $request->query('paid_start_date'),
                'paid_end_date' => $request->query('paid_end_date'),
            ];

            $distributions = $this->distributionService->getAllDistributions($filters);

            return response()->json([
                'success' => true,
                'message' => 'Sales distributions retrieved successfully.',
                'data' => $distributions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales distributions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified sales distribution
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $distribution = $this->distributionService->getDistributionById($id);

            return response()->json([
                'success' => true,
                'message' => 'Sales distribution retrieved successfully.',
                'data' => $distribution,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sales distribution not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Calculate and create sales distribution for an order
     * 
     * @param int $orderId
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateForOrder(int $orderId, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'margin_percentage' => 'nullable|numeric|min:0|max:100',
            ]);

            $marginPercentage = $request->input('margin_percentage', 10.0);

            $result = $this->distributionService->calculateDistribution($orderId, $marginPercentage);

            return response()->json([
                'success' => true,
                'message' => 'Sales distribution calculated successfully.',
                'data' => $result,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate sales distribution.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get distributions by order ID
     * 
     * @param int $orderId
     * @return JsonResponse
     */
    public function getByOrderId(int $orderId): JsonResponse
    {
        try {
            $distributions = $this->distributionService->getDistributionsByOrderId($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Order distributions retrieved successfully.',
                'data' => $distributions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order distributions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get distributions by poktan ID
     * 
     * @param int $poktanId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByPoktanId(int $poktanId, Request $request): JsonResponse
    {
        try {
            $filters = [
                'payment_status' => $request->query('payment_status'),
                'start_date' => $request->query('start_date'),
                'end_date' => $request->query('end_date'),
            ];

            $distributions = $this->distributionService->getDistributionsByPoktanId($poktanId, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Poktan distributions retrieved successfully.',
                'data' => $distributions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve poktan distributions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all pending distributions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPending(Request $request): JsonResponse
    {
        try {
            $filters = [
                'poktan_id' => $request->query('poktan_id'),
            ];

            $distributions = $this->distributionService->getPendingDistributions($filters);

            return response()->json([
                'success' => true,
                'message' => 'Pending distributions retrieved successfully.',
                'data' => $distributions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending distributions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all paid distributions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaid(Request $request): JsonResponse
    {
        try {
            $filters = [
                'poktan_id' => $request->query('poktan_id'),
                'paid_start_date' => $request->query('paid_start_date'),
                'paid_end_date' => $request->query('paid_end_date'),
            ];

            $distributions = $this->distributionService->getPaidDistributions($filters);

            return response()->json([
                'success' => true,
                'message' => 'Paid distributions retrieved successfully.',
                'data' => $distributions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve paid distributions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark distribution as paid
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsPaid(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $notes = $request->input('notes');
            $result = $this->distributionService->markAsPaid($id, $notes);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'distribution' => $result['distribution'],
                    'transaction' => $result['transaction'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark distribution as paid.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Batch mark distributions as paid
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchMarkAsPaid(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'distribution_ids' => 'required|array|min:1',
                'distribution_ids.*' => 'required|integer|exists:sales_distributions,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $distributionIds = $request->input('distribution_ids');
            $notes = $request->input('notes');

            $result = $this->distributionService->batchMarkAsPaid($distributionIds, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Distributions marked as paid successfully.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark distributions as paid.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get statistics
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $poktanId = $request->query('poktan_id');
            $stats = $this->distributionService->getStatistics($poktanId);

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully.',
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending payment summary grouped by poktan
     * 
     * @return JsonResponse
     */
    public function getPendingPaymentSummary(): JsonResponse
    {
        try {
            $summary = $this->distributionService->getPendingPaymentSummary();

            return response()->json([
                'success' => true,
                'message' => 'Pending payment summary retrieved successfully.',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending payment summary.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
