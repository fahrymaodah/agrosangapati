<?php

namespace App\Services;

use App\Repositories\Contracts\ProductionReportRepositoryInterface;
use Carbon\Carbon;

class ProductionReportService
{
    protected ProductionReportRepositoryInterface $repository;

    public function __construct(ProductionReportRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get member's production report.
     */
    public function getMemberReport(int $memberId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $history = $this->repository->getMemberHarvestHistory($memberId, $startDate, $endDate);
        $summary = $this->repository->getMemberProductionSummary($memberId, $startDate, $endDate);
        $byCommodity = $this->repository->getMemberProductionByCommodity($memberId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'by_commodity' => $byCommodity->map(function ($item) {
                return [
                    'commodity_id' => $item->commodity_id,
                    'commodity_name' => $item->commodity_name,
                    'unit' => $item->unit,
                    'total_quantity' => (float) $item->total_quantity,
                    'harvest_count' => $item->harvest_count,
                    'avg_quantity' => round((float) $item->avg_quantity, 2),
                    'first_harvest' => $item->first_harvest,
                    'last_harvest' => $item->last_harvest,
                ];
            })->toArray(),
            'harvest_history' => $history->map(function ($harvest) {
                return $this->formatHarvestData($harvest);
            })->toArray(),
        ];
    }

    /**
     * Get member's production summary only.
     */
    public function getMemberSummary(int $memberId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $this->repository->getMemberProductionSummary($memberId, $startDate, $endDate),
        ];
    }

    /**
     * Get member's production by commodity.
     */
    public function getMemberByCommodity(int $memberId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $byCommodity = $this->repository->getMemberProductionByCommodity($memberId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'commodities' => $byCommodity->map(function ($item) {
                return [
                    'commodity_id' => $item->commodity_id,
                    'commodity_name' => $item->commodity_name,
                    'unit' => $item->unit,
                    'total_quantity' => (float) $item->total_quantity,
                    'harvest_count' => $item->harvest_count,
                    'avg_quantity' => round((float) $item->avg_quantity, 2),
                    'first_harvest' => $item->first_harvest,
                    'last_harvest' => $item->last_harvest,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get member's production comparison.
     */
    public function getMemberComparison(int $memberId, ?string $period = 'month'): array
    {
        // Calculate period dates
        $periods = $this->calculateComparisonPeriods($period);

        $comparison = $this->repository->getMemberProductionComparison(
            $memberId,
            $periods['current']['start'],
            $periods['current']['end'],
            $periods['previous']['start'],
            $periods['previous']['end']
        );

        return [
            'period_type' => $period,
            ...$comparison,
        ];
    }

    /**
     * Get top producers in a poktan.
     */
    public function getTopProducers(int $poktanId, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $producers = $this->repository->getTopProducers($poktanId, $limit, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'top_producers' => $producers->map(function ($producer, $index) {
                return [
                    'rank' => $index + 1,
                    'member_id' => $producer->reported_by,
                    'member_name' => $producer->member_name,
                    'total_quantity' => (float) $producer->total_quantity,
                    'harvest_count' => $producer->harvest_count,
                    'commodity_count' => $producer->commodity_count,
                ];
            })->toArray(),
        ];
    }

    /**
     * Calculate comparison periods.
     */
    private function calculateComparisonPeriods(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'month':
                return [
                    'current' => [
                        'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                        'end' => $now->copy()->endOfMonth()->format('Y-m-d'),
                    ],
                    'previous' => [
                        'start' => $now->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                        'end' => $now->copy()->subMonth()->endOfMonth()->format('Y-m-d'),
                    ],
                ];

            case 'quarter':
                return [
                    'current' => [
                        'start' => $now->copy()->startOfQuarter()->format('Y-m-d'),
                        'end' => $now->copy()->endOfQuarter()->format('Y-m-d'),
                    ],
                    'previous' => [
                        'start' => $now->copy()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                        'end' => $now->copy()->subQuarter()->endOfQuarter()->format('Y-m-d'),
                    ],
                ];

            case 'year':
                return [
                    'current' => [
                        'start' => $now->copy()->startOfYear()->format('Y-m-d'),
                        'end' => $now->copy()->endOfYear()->format('Y-m-d'),
                    ],
                    'previous' => [
                        'start' => $now->copy()->subYear()->startOfYear()->format('Y-m-d'),
                        'end' => $now->copy()->subYear()->endOfYear()->format('Y-m-d'),
                    ],
                ];

            default: // week
                return [
                    'current' => [
                        'start' => $now->copy()->startOfWeek()->format('Y-m-d'),
                        'end' => $now->copy()->endOfWeek()->format('Y-m-d'),
                    ],
                    'previous' => [
                        'start' => $now->copy()->subWeek()->startOfWeek()->format('Y-m-d'),
                        'end' => $now->copy()->subWeek()->endOfWeek()->format('Y-m-d'),
                    ],
                ];
        }
    }

    /**
     * Format harvest data.
     */
    private function formatHarvestData($harvest): array
    {
        return [
            'id' => $harvest->id,
            'harvest_date' => $harvest->harvest_date,
            'commodity' => $harvest->commodity ? [
                'id' => $harvest->commodity->id,
                'name' => $harvest->commodity->name,
                'unit' => $harvest->commodity->unit,
            ] : null,
            'grade' => $harvest->grade ? [
                'id' => $harvest->grade->id,
                'name' => $harvest->grade->grade_name,
            ] : null,
            'quantity' => (float) $harvest->quantity,
            'notes' => $harvest->notes,
            'poktan' => $harvest->poktan ? [
                'id' => $harvest->poktan->id,
                'name' => $harvest->poktan->name,
            ] : null,
        ];
    }

    /**
     * Get poktan's complete production report.
     */
    public function getPoktanReport(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $summary = $this->repository->getPoktanProductionSummary($poktanId, $startDate, $endDate);
        $byCommodity = $this->repository->getPoktanProductionByCommodity($poktanId, $startDate, $endDate);
        $byMember = $this->repository->getPoktanProductionByMember($poktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'by_commodity' => $byCommodity,
            'by_member' => $byMember,
        ];
    }

    /**
     * Get poktan's production summary only.
     */
    public function getPoktanSummary(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $summary = $this->repository->getPoktanProductionSummary($poktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Get poktan's production by commodity.
     */
    public function getPoktanByCommodity(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $commodities = $this->repository->getPoktanProductionByCommodity($poktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'commodities' => $commodities,
        ];
    }

    /**
     * Get poktan's production by member.
     */
    public function getPoktanByMember(int $poktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $members = $this->repository->getPoktanProductionByMember($poktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'members' => $members,
        ];
    }

    /**
     * Get poktan's monthly production trend.
     */
    public function getPoktanMonthlyTrend(int $poktanId, int $months = 12): array
    {
        $trend = $this->repository->getPoktanMonthlyTrend($poktanId, $months);

        return [
            'months' => $months,
            'trend' => $trend,
        ];
    }

    /**
     * Get gapoktan's complete production report.
     */
    public function getGapoktanReport(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $summary = $this->repository->getGapoktanProductionSummary($gapoktanId, $startDate, $endDate);
        $byCommodity = $this->repository->getGapoktanProductionByCommodity($gapoktanId, $startDate, $endDate);
        $byPoktan = $this->repository->getGapoktanProductionByPoktan($gapoktanId, $startDate, $endDate);
        $poktanComparison = $this->repository->getGapoktanPoktanComparison($gapoktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'by_commodity' => $byCommodity,
            'by_poktan' => $byPoktan,
            'poktan_comparison' => $poktanComparison,
        ];
    }

    /**
     * Get gapoktan's production summary only.
     */
    public function getGapoktanSummary(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $summary = $this->repository->getGapoktanProductionSummary($gapoktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Get gapoktan's production by commodity.
     */
    public function getGapoktanByCommodity(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $commodities = $this->repository->getGapoktanProductionByCommodity($gapoktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'commodities' => $commodities,
        ];
    }

    /**
     * Get gapoktan's production by poktan.
     */
    public function getGapoktanByPoktan(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $poktans = $this->repository->getGapoktanProductionByPoktan($gapoktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'poktans' => $poktans,
        ];
    }

    /**
     * Get gapoktan's poktan comparison/ranking.
     */
    public function getGapoktanPoktanComparison(int $gapoktanId, ?string $startDate = null, ?string $endDate = null): array
    {
        // Default to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $comparison = $this->repository->getGapoktanPoktanComparison($gapoktanId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'comparison' => $comparison,
        ];
    }

    /**
     * Get gapoktan's monthly production trend.
     */
    public function getGapoktanMonthlyTrend(int $gapoktanId, int $months = 12): array
    {
        $trend = $this->repository->getGapoktanMonthlyTrend($gapoktanId, $months);

        return [
            'months' => $months,
            'trend' => $trend,
        ];
    }
}
