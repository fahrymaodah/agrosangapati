<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository
{
    /**
     * Get all transactions with filters and pagination.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::with(['poktan', 'category', 'creator', 'approver']);

        // Filter by poktan
        if (isset($filters['poktan_id'])) {
            $query->forPoktan($filters['poktan_id']);
        }

        // Filter by type (income/expense)
        if (isset($filters['type'])) {
            if ($filters['type'] === 'income') {
                $query->income();
            } elseif ($filters['type'] === 'expense') {
                $query->expense();
            }
        }

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        // Filter by approval status
        if (isset($filters['status'])) {
            if ($filters['status'] === 'approved') {
                $query->approved();
            } elseif ($filters['status'] === 'pending') {
                $query->pending();
            }
        }

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        // Filter by created_by
        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Order by transaction date descending
        $query->orderBy('transaction_date', 'desc')
              ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Find a transaction by ID.
     */
    public function find(int $id): ?Transaction
    {
        return Transaction::with(['poktan', 'category', 'creator', 'approver'])->find($id);
    }

    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update a transaction.
     */
    public function update(int $id, array $data): bool
    {
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    /**
     * Delete a transaction.
     */
    public function delete(int $id): bool
    {
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->delete();
    }

    /**
     * Get pending approval transactions.
     */
    public function getPendingApproval(int $poktanId): Collection
    {
        return Transaction::with(['poktan', 'category', 'creator'])
            ->forPoktan($poktanId)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent transactions for a poktan.
     */
    public function getRecent(int $poktanId, int $limit = 10): Collection
    {
        return Transaction::with(['poktan', 'category', 'creator', 'approver'])
            ->forPoktan($poktanId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total income for a poktan.
     */
    public function getTotalIncome(int $poktanId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = Transaction::forPoktan($poktanId)
            ->income()
            ->approved();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    /**
     * Get total expense for a poktan.
     */
    public function getTotalExpense(int $poktanId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = Transaction::forPoktan($poktanId)
            ->expense()
            ->approved();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    /**
     * Get transactions by category.
     */
    public function getByCategory(int $categoryId, int $poktanId = null): Collection
    {
        $query = Transaction::with(['poktan', 'category', 'creator', 'approver'])
            ->byCategory($categoryId);

        if ($poktanId) {
            $query->forPoktan($poktanId);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Approve a transaction.
     */
    public function approve(int $id, int $approvedBy): bool
    {
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->update([
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Get monthly summary for a poktan.
     */
    public function getMonthlySummary(int $poktanId, int $year, int $month): array
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        $income = $this->getTotalIncome($poktanId, $startDate, $endDate);
        $expense = $this->getTotalExpense($poktanId, $startDate, $endDate);

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }
}
