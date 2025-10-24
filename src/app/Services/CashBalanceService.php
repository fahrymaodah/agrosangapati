<?php

namespace App\Services;

use App\Repositories\CashBalanceRepository;
use App\Models\CashBalance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CashBalanceService
{
    protected CashBalanceRepository $repository;

    // Alert thresholds
    protected float $lowBalanceThreshold = 1000000; // Rp 1 juta
    protected float $criticalBalanceThreshold = 500000; // Rp 500 ribu

    public function __construct(CashBalanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get current balance for a poktan.
     */
    public function getCurrentBalance(int $poktanId): array
    {
        $balance = $this->repository->getBalanceByPoktan($poktanId);

        if (!$balance) {
            return [
                'success' => false,
                'message' => 'Balance not found for this poktan',
            ];
        }

        $alert = $this->checkBalanceAlert($balance->balance);

        return [
            'success' => true,
            'data' => [
                'poktan_id' => $balance->poktan_id,
                'poktan_name' => $balance->poktan->name,
                'balance' => $balance->balance,
                'formatted_balance' => 'Rp ' . number_format($balance->balance, 0, ',', '.'),
                'last_updated' => $balance->last_updated,
                'alert' => $alert,
            ],
        ];
    }

    /**
     * Get all balances with alerts.
     */
    public function getAllBalances(): array
    {
        $balances = $this->repository->getAllBalances();

        $data = $balances->map(function ($balance) {
            $alert = $this->checkBalanceAlert($balance->balance);
            
            return [
                'poktan_id' => $balance->poktan_id,
                'poktan_name' => $balance->poktan->name,
                'balance' => $balance->balance,
                'formatted_balance' => 'Rp ' . number_format($balance->balance, 0, ',', '.'),
                'last_updated' => $balance->last_updated,
                'alert' => $alert,
            ];
        });

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    /**
     * Get balance history for a poktan.
     */
    public function getBalanceHistory(int $poktanId, array $filters = []): array
    {
        try {
            $histories = $this->repository->getBalanceHistory($poktanId, $filters);

            $data = $histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'transaction_id' => $history->transaction_id,
                    'previous_balance' => $history->previous_balance,
                    'amount' => $history->amount,
                    'new_balance' => $history->new_balance,
                    'type' => $history->type,
                    'description' => $history->description,
                    'formatted_amount' => $history->formatted_amount,
                    'balance_change' => $history->balance_change,
                    'created_by' => $history->creator ? $history->creator->name : null,
                    'created_at' => $history->created_at,
                    'date' => $history->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get balance history: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve balance history',
            ];
        }
    }

    /**
     * Get recent balance changes.
     */
    public function getRecentChanges(int $poktanId, int $limit = 10): array
    {
        try {
            $changes = $this->repository->getRecentChanges($poktanId, $limit);

            $data = $changes->map(function ($change) {
                return [
                    'id' => $change->id,
                    'amount' => $change->amount,
                    'type' => $change->type,
                    'description' => $change->description,
                    'formatted_amount' => $change->formatted_amount,
                    'balance_change' => $change->balance_change,
                    'created_at' => $change->created_at->diffForHumans(),
                ];
            });

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get recent changes: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve recent changes',
            ];
        }
    }

    /**
     * Get low balance alerts.
     */
    public function getLowBalanceAlerts(): array
    {
        $lowBalances = $this->repository->getLowBalancePoktans($this->lowBalanceThreshold);
        $zeroBalances = $this->repository->getZeroBalancePoktans();

        $alerts = [];

        // Critical alerts (zero or negative balance)
        foreach ($zeroBalances as $balance) {
            $alerts[] = [
                'level' => 'critical',
                'poktan_id' => $balance->poktan_id,
                'poktan_name' => $balance->poktan->name,
                'balance' => $balance->balance,
                'formatted_balance' => 'Rp ' . number_format($balance->balance, 0, ',', '.'),
                'message' => 'Balance is zero or negative. Transactions are locked.',
            ];
        }

        // Low balance alerts
        foreach ($lowBalances as $balance) {
            $level = $balance->balance <= $this->criticalBalanceThreshold ? 'critical' : 'warning';
            
            $alerts[] = [
                'level' => $level,
                'poktan_id' => $balance->poktan_id,
                'poktan_name' => $balance->poktan->name,
                'balance' => $balance->balance,
                'formatted_balance' => 'Rp ' . number_format($balance->balance, 0, ',', '.'),
                'message' => $level === 'critical' 
                    ? 'Balance is critically low. Please add funds immediately.'
                    : 'Balance is running low. Consider adding funds soon.',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'alerts' => $alerts,
                'total_alerts' => count($alerts),
                'critical_count' => count(array_filter($alerts, fn($a) => $a['level'] === 'critical')),
                'warning_count' => count(array_filter($alerts, fn($a) => $a['level'] === 'warning')),
            ],
        ];
    }

    /**
     * Get balance statistics for a poktan.
     */
    public function getBalanceStatistics(int $poktanId, string $startDate, string $endDate): array
    {
        try {
            $stats = $this->repository->getBalanceStatistics($poktanId, $startDate, $endDate);

            return [
                'success' => true,
                'data' => [
                    'current_balance' => $stats['current_balance'],
                    'formatted_balance' => 'Rp ' . number_format($stats['current_balance'], 0, ',', '.'),
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate,
                    ],
                    'total_income' => $stats['total_income'],
                    'formatted_income' => 'Rp ' . number_format($stats['total_income'], 0, ',', '.'),
                    'total_expense' => $stats['total_expense'],
                    'formatted_expense' => 'Rp ' . number_format($stats['total_expense'], 0, ',', '.'),
                    'net_change' => $stats['net_change'],
                    'formatted_net_change' => 'Rp ' . number_format($stats['net_change'], 0, ',', '.'),
                    'transaction_count' => $stats['transaction_count'],
                    'income_count' => $stats['income_count'],
                    'expense_count' => $stats['expense_count'],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get balance statistics: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve balance statistics',
            ];
        }
    }

    /**
     * Get monthly balance summary.
     */
    public function getMonthlyBalanceSummary(int $poktanId, int $year): array
    {
        try {
            $summary = $this->repository->getMonthlyBalanceSummary($poktanId, $year);

            return [
                'success' => true,
                'data' => [
                    'year' => $year,
                    'poktan_id' => $poktanId,
                    'monthly_data' => $summary,
                    'yearly_totals' => [
                        'total_income' => array_sum(array_column($summary, 'income')),
                        'total_expense' => array_sum(array_column($summary, 'expense')),
                        'net_change' => array_sum(array_column($summary, 'net')),
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get monthly summary: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve monthly summary',
            ];
        }
    }

    /**
     * Get balance trend for charts.
     */
    public function getBalanceTrend(int $poktanId, int $days = 30): array
    {
        try {
            $trend = $this->repository->getBalanceTrend($poktanId, $days);

            return [
                'success' => true,
                'data' => [
                    'period_days' => $days,
                    'trend_data' => $trend,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get balance trend: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve balance trend',
            ];
        }
    }

    /**
     * Check if transaction is allowed based on balance.
     */
    public function canTransact(int $poktanId, float $amount): array
    {
        $hasBalance = $this->repository->hasSufficientBalance($poktanId, $amount);

        if (!$hasBalance) {
            $balance = $this->repository->getBalanceByPoktan($poktanId);
            $currentBalance = $balance ? $balance->balance : 0;

            return [
                'allowed' => false,
                'message' => 'Insufficient balance. Current balance: Rp ' . number_format($currentBalance, 0, ',', '.'),
                'current_balance' => $currentBalance,
                'required_amount' => $amount,
                'shortage' => $amount - $currentBalance,
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Transaction can proceed',
        ];
    }

    /**
     * Check balance alert level.
     */
    protected function checkBalanceAlert(float $balance): ?array
    {
        if ($balance <= 0) {
            return [
                'level' => 'critical',
                'message' => 'Balance is zero or negative. Transactions are locked.',
            ];
        }

        if ($balance <= $this->criticalBalanceThreshold) {
            return [
                'level' => 'critical',
                'message' => 'Balance is critically low. Please add funds immediately.',
            ];
        }

        if ($balance <= $this->lowBalanceThreshold) {
            return [
                'level' => 'warning',
                'message' => 'Balance is running low. Consider adding funds soon.',
            ];
        }

        return null; // No alert
    }

    /**
     * Get total balance across all poktans.
     */
    public function getTotalBalance(): array
    {
        $total = $this->repository->getTotalBalance();

        return [
            'success' => true,
            'data' => [
                'total_balance' => $total,
                'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.'),
            ],
        ];
    }
}
