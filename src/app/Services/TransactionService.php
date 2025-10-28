<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Repositories\TransactionCategoryRepository;
use App\Services\Contracts\FileUploadServiceInterface;
use App\Models\CashBalance;
use App\Models\CashBalanceHistory;
use App\Models\Transaction;
use App\Models\TransactionApprovalLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TransactionService
{
    protected TransactionRepository $repository;
    protected TransactionCategoryRepository $categoryRepository;
    protected FileUploadServiceInterface $fileUploadService;

    public function __construct(
        TransactionRepository $repository,
        TransactionCategoryRepository $categoryRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all transactions with filters.
     */
    public function getAllTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get a transaction by ID.
     */
    public function getTransactionById(int $id): ?Transaction
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new transaction.
     */
    public function createTransaction(array $data): array
    {
        try {
            DB::beginTransaction();

            // Convert 'type' to 'transaction_type' for database
            if (isset($data['type'])) {
                $data['transaction_type'] = $data['type'];
                unset($data['type']);
            }

            // Validate category exists and matches type
            $category = $this->categoryRepository->find($data['category_id']);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                ];
            }

            if ($category->type !== $data['transaction_type']) {
                return [
                    'success' => false,
                    'message' => 'Category type does not match transaction type',
                ];
            }

            // Validate poktan has access to this category
            if ($category->poktan_id && $category->poktan_id !== $data['poktan_id']) {
                return [
                    'success' => false,
                    'message' => 'Category does not belong to this poktan',
                ];
            }

            // For expense, validate sufficient balance
            if ($data['transaction_type'] === 'expense') {
                $balance = $this->getCurrentBalance($data['poktan_id']);
                if ($balance < $data['amount']) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance. Current balance: Rp ' . number_format($balance, 0, ',', '.'),
                    ];
                }
            }

            // Handle receipt photo upload
            if (isset($data['receipt_photo']) && $data['receipt_photo'] instanceof UploadedFile) {
                $data['receipt_photo'] = $this->uploadReceiptPhoto($data['receipt_photo'], $data['poktan_id']);
            }

            // Set transaction date to today if not provided
            if (!isset($data['transaction_date'])) {
                $data['transaction_date'] = now()->format('Y-m-d');
            }

            // Set default status to approved for auto-approval logic
            if (!isset($data['status'])) {
                // Auto-approve if below threshold (e.g., below Rp 1,000,000)
                $autoApprovalThreshold = 1000000;
                if ($data['amount'] < $autoApprovalThreshold) {
                    $data['status'] = 'approved';
                    $data['approved_by'] = $data['created_by'];
                    $data['approved_at'] = now();
                } else {
                    $data['status'] = 'pending';
                }
            }

            // Create transaction
            $transaction = $this->repository->create($data);

            // Update cash balance if approved
            if ($transaction->isApproved()) {
                $this->updateCashBalance($transaction);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['poktan', 'category', 'creator', 'approver']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update a transaction.
     */
    public function updateTransaction(int $id, array $data): array
    {
        try {
            DB::beginTransaction();

            $transaction = $this->repository->find($id);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Prevent updating approved transactions
            if ($transaction->isApproved()) {
                return [
                    'success' => false,
                    'message' => 'Cannot update approved transaction',
                ];
            }

            // Convert 'type' to 'transaction_type' if present
            if (isset($data['type'])) {
                $data['transaction_type'] = $data['type'];
                unset($data['type']);
            }

            // Validate category if provided
            if (isset($data['category_id'])) {
                $category = $this->categoryRepository->find($data['category_id']);
                if (!$category) {
                    return [
                        'success' => false,
                        'message' => 'Category not found',
                    ];
                }

                $transactionType = $data['transaction_type'] ?? $transaction->transaction_type;
                if ($category->type !== $transactionType) {
                    return [
                        'success' => false,
                        'message' => 'Category type does not match transaction type',
                    ];
                }
            }

            // Handle receipt photo upload
            if (isset($data['receipt_photo']) && $data['receipt_photo'] instanceof UploadedFile) {
                // Delete old photo using FileUploadService
                if ($transaction->receipt_photo) {
                    $this->fileUploadService->deleteFile($transaction->receipt_photo);
                }
                $data['receipt_photo'] = $this->uploadReceiptPhoto($data['receipt_photo'], $transaction->poktan_id);
            }

            // Update transaction
            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Failed to update transaction');
            }

            $transaction->refresh();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load(['poktan', 'category', 'creator', 'approver']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to update transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a transaction.
     */
    public function deleteTransaction(int $id): array
    {
        try {
            DB::beginTransaction();

            $transaction = $this->repository->find($id);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Prevent deleting approved transactions
            if ($transaction->isApproved()) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete approved transaction. Please contact administrator.',
                ];
            }

            // Delete receipt photo if exists
            if ($transaction->receipt_photo) {
                Storage::disk('public')->delete($transaction->receipt_photo);
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Failed to delete transaction');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to delete transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Approve a transaction.
     */
    public function approveTransaction(int $id, int $approvedBy, ?string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $transaction = $this->repository->find($id);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            if ($transaction->isApproved()) {
                return [
                    'success' => false,
                    'message' => 'Transaction already approved',
                ];
            }

            if ($transaction->status === 'rejected') {
                return [
                    'success' => false,
                    'message' => 'Cannot approve rejected transaction',
                ];
            }

            // Validate balance for expense transactions
            if ($transaction->transaction_type === 'expense') {
                $balance = $this->getCurrentBalance($transaction->poktan_id);
                if ($balance < $transaction->amount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance. Current balance: Rp ' . number_format($balance, 0, ',', '.'),
                    ];
                }
            }

            $previousStatus = $transaction->status;

            // Approve transaction - update status and approval fields
            $approved = $this->repository->update($id, [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            if (!$approved) {
                throw new \Exception('Failed to approve transaction');
            }

            $transaction->refresh();

            // Log approval action
            $this->logApprovalAction($transaction, 'approved', $previousStatus, 'approved', $approvedBy, $notes);

            // Update cash balance
            $this->updateCashBalance($transaction);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction approved successfully',
                'data' => $transaction->load(['poktan', 'category', 'creator', 'approver']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to approve transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get pending approval transactions.
     */
    public function getPendingApproval(int $poktanId): Collection
    {
        return $this->repository->getPendingApproval($poktanId);
    }

    /**
     * Get recent transactions.
     */
    public function getRecentTransactions(int $poktanId, int $limit = 10): Collection
    {
        return $this->repository->getRecent($poktanId, $limit);
    }

    /**
     * Get monthly summary.
     */
    public function getMonthlySummary(int $poktanId, int $year, int $month): array
    {
        return $this->repository->getMonthlySummary($poktanId, $year, $month);
    }

    /**
     * Upload receipt photo using FileUploadService.
     */
    protected function uploadReceiptPhoto(UploadedFile $file, int $poktanId): string
    {
        $directory = 'receipts/poktan_' . $poktanId;
        
        $result = $this->fileUploadService->uploadImage($file, $directory, [
            'optimize' => true,
            'max_image_width' => 1200,
            'max_image_height' => 1200,
            'image_quality' => 80,
            'generate_thumbnail' => false,
        ]);
        
        return $result['path'];
    }

    /**
     * Get current cash balance for poktan.
     */
    protected function getCurrentBalance(int $poktanId): float
    {
        $cashBalance = CashBalance::where('poktan_id', $poktanId)->first();
        
        return $cashBalance ? $cashBalance->balance : 0;
    }

    /**
     * Update cash balance after transaction.
     */
    protected function updateCashBalance(Transaction $transaction): void
    {
        $cashBalance = CashBalance::firstOrCreate(
            ['poktan_id' => $transaction->poktan_id],
            ['balance' => 0, 'last_updated' => now()]
        );

        $oldBalance = $cashBalance->balance;

        if ($transaction->transaction_type === 'income') {
            $newBalance = $oldBalance + $transaction->amount;
        } else {
            $newBalance = $oldBalance - $transaction->amount;
        }

        $cashBalance->update([
            'balance' => $newBalance,
            'last_updated' => now(),
        ]);

        // Record balance history
        CashBalanceHistory::create([
            'poktan_id' => $transaction->poktan_id,
            'transaction_id' => $transaction->id,
            'previous_balance' => $oldBalance,
            'amount' => $transaction->amount,
            'new_balance' => $newBalance,
            'type' => $transaction->transaction_type,
            'description' => $transaction->description,
            'created_by' => $transaction->created_by,
        ]);

        Log::info("Cash balance updated for poktan {$transaction->poktan_id}: {$oldBalance} -> {$newBalance}");
    }

    /**
     * Reject a transaction.
     */
    public function rejectTransaction(int $id, int $rejectedBy, string $notes): array
    {
        try {
            DB::beginTransaction();

            $transaction = $this->repository->find($id);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            if ($transaction->isApproved()) {
                return [
                    'success' => false,
                    'message' => 'Cannot reject approved transaction',
                ];
            }

            if ($transaction->status === 'rejected') {
                return [
                    'success' => false,
                    'message' => 'Transaction already rejected',
                ];
            }

            $previousStatus = $transaction->status;

            // Reject transaction
            $rejected = $this->repository->update($id, [
                'status' => 'rejected',
                'approved_by' => $rejectedBy,
                'approved_at' => now(),
            ]);

            if (!$rejected) {
                throw new \Exception('Failed to reject transaction');
            }

            $transaction->refresh();

            // Log rejection action
            $this->logApprovalAction($transaction, 'rejected', $previousStatus, 'rejected', $rejectedBy, $notes);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction rejected successfully',
                'data' => $transaction->load(['poktan', 'category', 'creator', 'approver']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject transaction: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to reject transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk approve transactions.
     */
    public function bulkApproveTransactions(array $transactionIds, int $approvedBy, ?string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $results = [
                'approved' => [],
                'failed' => [],
            ];

            foreach ($transactionIds as $transactionId) {
                $result = $this->approveTransaction($transactionId, $approvedBy, $notes);
                
                if ($result['success']) {
                    $results['approved'][] = $transactionId;
                } else {
                    $results['failed'][] = [
                        'id' => $transactionId,
                        'reason' => $result['message'],
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => sprintf(
                    'Bulk approval completed. Approved: %d, Failed: %d',
                    count($results['approved']),
                    count($results['failed'])
                ),
                'data' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk approve transactions: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to bulk approve transactions: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get approval history for a transaction.
     */
    public function getApprovalHistory(int $transactionId): array
    {
        try {
            $transaction = $this->repository->find($transactionId);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            $logs = TransactionApprovalLog::with('performer')
                ->where('transaction_id', $transactionId)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedLogs = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_summary' => $log->action_summary,
                    'previous_status' => $log->previous_status,
                    'new_status' => $log->new_status,
                    'performed_by' => $log->performer->name ?? 'Unknown',
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transactionId,
                    'logs' => $formattedLogs,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get approval history: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to retrieve approval history',
            ];
        }
    }

    /**
     * Log approval action.
     */
    protected function logApprovalAction(
        Transaction $transaction,
        string $action,
        ?string $previousStatus,
        string $newStatus,
        int $performedBy,
        ?string $notes = null
    ): void {
        TransactionApprovalLog::create([
            'transaction_id' => $transaction->id,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'performed_by' => $performedBy,
            'notes' => $notes,
            'metadata' => [
                'transaction_type' => $transaction->transaction_type,
                'amount' => $transaction->amount,
                'poktan_id' => $transaction->poktan_id,
            ],
        ]);
    }
}
