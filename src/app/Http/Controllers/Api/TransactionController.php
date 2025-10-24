<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of transactions.
     * 
     * GET /api/transactions
     * Filters: poktan_id, type, category_id, status, start_date, end_date, created_by, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'poktan_id',
            'type',
            'category_id',
            'status',
            'start_date',
            'end_date',
            'created_by',
        ]);

        $perPage = $request->input('per_page', 15);

        $transactions = $this->service->getAllTransactions($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Store a newly created transaction.
     * 
     * POST /api/transactions
     * Body: poktan_id, category_id, type, amount, description, receipt_photo (file), transaction_date
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'category_id' => 'required|integer|exists:transaction_categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'receipt_photo' => 'nullable|image|max:5120', // max 5MB
            'transaction_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Add created_by from authenticated user (for now use poktan_id as placeholder)
        $validated['created_by'] = $request->input('poktan_id'); // TODO: Replace with auth()->id()

        $result = $this->service->createTransaction($validated);

        $statusCode = $result['success'] ? 201 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Display the specified transaction.
     * 
     * GET /api/transactions/{id}
     */
    public function show(int $id): JsonResponse
    {
        $transaction = $this->service->getTransactionById($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified transaction.
     * 
     * PUT/PATCH /api/transactions/{id}
     * Body: category_id, type, amount, description, receipt_photo (file), transaction_date
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|integer|exists:transaction_categories,id',
            'type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string|max:500',
            'receipt_photo' => 'nullable|image|max:5120',
            'transaction_date' => 'sometimes|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->service->updateTransaction($id, $validated);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Remove the specified transaction.
     * 
     * DELETE /api/transactions/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->deleteTransaction($id);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Approve a transaction.
     * 
     * POST /api/transactions/{id}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'approved_by' => 'required|integer|exists:users,id',
        ]);

        $result = $this->service->approveTransaction($id, $validated['approved_by']);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get pending approval transactions for a poktan.
     * 
     * GET /api/transactions/pending?poktan_id=X
     */
    public function pending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
        ]);

        $transactions = $this->service->getPendingApproval($validated['poktan_id']);

        return response()->json([
            'success' => true,
            'message' => 'Pending transactions retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Get recent transactions for a poktan.
     * 
     * GET /api/transactions/recent?poktan_id=X&limit=10
     */
    public function recent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;
        $transactions = $this->service->getRecentTransactions($validated['poktan_id'], $limit);

        return response()->json([
            'success' => true,
            'message' => 'Recent transactions retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Get monthly summary for a poktan.
     * 
     * GET /api/transactions/summary?poktan_id=X&year=2024&month=10
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poktan_id' => 'required|integer|exists:poktans,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $summary = $this->service->getMonthlySummary(
            $validated['poktan_id'],
            $validated['year'],
            $validated['month']
        );

        return response()->json([
            'success' => true,
            'message' => 'Monthly summary retrieved successfully',
            'data' => $summary,
        ]);
    }

    /**
     * Reject a transaction.
     * 
     * POST /api/transactions/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        // TODO: Replace with auth()->id()
        $rejectedBy = $request->input('rejected_by', 1);

        $result = $this->service->rejectTransaction($id, $rejectedBy, $validated['notes']);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Bulk approve transactions.
     * 
     * POST /api/transactions/bulk-approve
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'required|integer|exists:transactions,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // TODO: Replace with auth()->id()
        $approvedBy = $request->input('approved_by', 1);

        $result = $this->service->bulkApproveTransactions(
            $validated['transaction_ids'],
            $approvedBy,
            $validated['notes'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Get approval history for a transaction.
     * 
     * GET /api/transactions/{id}/approval-history
     */
    public function approvalHistory(int $id): JsonResponse
    {
        $result = $this->service->getApprovalHistory($id);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }
}

