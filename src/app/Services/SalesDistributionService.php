<?php

namespace App\Services;

use App\Repositories\Contracts\SalesDistributionRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionCategoryRepository;
use App\Repositories\CashBalanceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SalesDistributionService
{
    protected $distributionRepository;
    protected $orderRepository;
    protected $productRepository;
    protected $stockRepository;
    protected $transactionRepository;
    protected $categoryRepository;
    protected $cashBalanceRepository;

    public function __construct(
        SalesDistributionRepositoryInterface $distributionRepository,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        StockRepositoryInterface $stockRepository,
        TransactionRepository $transactionRepository,
        TransactionCategoryRepository $categoryRepository,
        CashBalanceRepository $cashBalanceRepository
    ) {
        $this->distributionRepository = $distributionRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->stockRepository = $stockRepository;
        $this->transactionRepository = $transactionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->cashBalanceRepository = $cashBalanceRepository;
    }

    /**
     * Calculate and create sales distribution for an order
     * Called automatically when order is delivered & paid
     */
    public function calculateDistribution(int $orderId, float $marginPercentage = 10.0)
    {
        try {
            DB::beginTransaction();

            // Get order with items
            $order = $this->orderRepository->findById($orderId);

            if (!$order) {
                throw new Exception("Order not found");
            }

            // Check if order is eligible for distribution
            if ($order->order_status !== 'delivered') {
                throw new Exception("Order must be delivered before distribution calculation");
            }

            if ($order->payment_status !== 'paid') {
                throw new Exception("Order must be paid before distribution calculation");
            }

            $distributions = [];

            foreach ($order->items as $item) {
                // Get product with commodity and grade
                $product = $this->productRepository->findById($item->product_id);
                
                if (!$product) {
                    Log::warning("Product #{$item->product_id} not found, skipping distribution");
                    continue;
                }

                // Find stock record to get poktan_id
                // Try to find stock that matches commodity and grade
                $stock = \App\Models\Stock::where('commodity_id', $product->commodity_id)
                    ->where('grade_id', $product->grade_id)
                    ->whereNotNull('poktan_id') // Only poktan stocks
                    ->first();

                // If no poktan stock found, try gapoktan stock
                if (!$stock) {
                    $stock = \App\Models\Stock::where('commodity_id', $product->commodity_id)
                        ->where('grade_id', $product->grade_id)
                        ->whereNull('poktan_id')
                        ->first();
                }

                if (!$stock || !$stock->poktan_id) {
                    Log::warning("No poktan found for product #{$item->product_id} (commodity: {$product->commodity_id}, grade: {$product->grade_id}), skipping distribution");
                    continue;
                }

                $poktanId = $stock->poktan_id;

                // Check if distribution already exists for this order item and poktan
                if ($this->distributionRepository->checkExistingDistribution($item->id, $poktanId)) {
                    Log::info("Distribution already exists for order item #{$item->id} and poktan #{$poktanId}");
                    continue;
                }

                // Calculate amounts
                $quantitySold = $item->quantity;
                $totalRevenue = $item->subtotal;
                
                // Calculate sale_price from subtotal if unit_price is null
                $salePrice = $item->unit_price ?? ($totalRevenue / $quantitySold);
                
                $gapoktanMargin = $totalRevenue * ($marginPercentage / 100);
                $poktanPayment = $totalRevenue - $gapoktanMargin;

                // Create distribution record
                $distribution = $this->distributionRepository->createDistribution([
                    'order_item_id' => $item->id,
                    'poktan_id' => $poktanId,
                    'commodity_id' => $product->commodity_id,
                    'quantity_sold' => $quantitySold,
                    'sale_price' => $salePrice,
                    'total_revenue' => $totalRevenue,
                    'gapoktan_margin' => $gapoktanMargin,
                    'poktan_payment' => $poktanPayment,
                    'payment_status' => 'pending',
                ]);

                $distributions[] = $distribution;
            }

            DB::commit();

            return [
                'order_id' => $orderId,
                'total_distributions' => count($distributions),
                'distributions' => $distributions,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error calculating sales distribution: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark distribution as paid and create transaction in poktan
     */
    public function markAsPaid(int $distributionId, ?string $notes = null)
    {
        try {
            DB::beginTransaction();

            $distribution = $this->distributionRepository->getDistributionById($distributionId);

            if ($distribution->payment_status === 'paid') {
                throw new Exception("Distribution has already been paid");
            }

            // Mark as paid
            $paidAt = now();
            $distribution = $this->distributionRepository->markAsPaid($distributionId, $paidAt);

            // Get or create transaction category for sales revenue
            $category = $this->categoryRepository->findByName('Hasil Penjualan Produk', $distribution->poktan_id);
            
            if (!$category) {
                $category = $this->categoryRepository->create([
                    'name' => 'Hasil Penjualan Produk',
                    'description' => 'Pendapatan dari penjualan produk melalui marketplace',
                    'type' => 'income',
                    'poktan_id' => null, // Default category for all poktans
                ]);
            }

            // Create transaction record in poktan
            $transactionData = [
                'poktan_id' => $distribution->poktan_id,
                'category_id' => $category->id,
                'type' => 'income',
                'amount' => $distribution->poktan_payment,
                'description' => $notes ?? "Pembayaran hasil penjualan produk #{$distribution->orderItem->product->name} (Order #{$distribution->orderItem->order_id})",
                'transaction_date' => $paidAt,
                'created_by' => auth()->id() ?? 1, // Default to admin if no auth
            ];

            $transaction = $this->transactionRepository->create($transactionData);

            // Update poktan cash balance
            $this->cashBalanceRepository->updateBalance(
                $distribution->poktan_id,
                $distribution->poktan_payment,
                'increase'
            );

            DB::commit();

            return [
                'distribution' => $distribution->fresh(),
                'transaction' => $transaction,
                'message' => 'Distribution marked as paid and transaction recorded successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error marking distribution as paid: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all distributions with filters
     */
    public function getAllDistributions(array $filters = [])
    {
        return $this->distributionRepository->getAllDistributions($filters);
    }

    /**
     * Get distribution by ID
     */
    public function getDistributionById(int $id)
    {
        return $this->distributionRepository->getDistributionById($id);
    }

    /**
     * Get distributions by order ID
     */
    public function getDistributionsByOrderId(int $orderId)
    {
        return $this->distributionRepository->getDistributionsByOrderId($orderId);
    }

    /**
     * Get distributions by poktan ID
     */
    public function getDistributionsByPoktanId(int $poktanId, array $filters = [])
    {
        return $this->distributionRepository->getDistributionsByPoktanId($poktanId, $filters);
    }

    /**
     * Get all pending distributions
     */
    public function getPendingDistributions(array $filters = [])
    {
        return $this->distributionRepository->getPendingDistributions($filters);
    }

    /**
     * Get all paid distributions
     */
    public function getPaidDistributions(array $filters = [])
    {
        return $this->distributionRepository->getPaidDistributions($filters);
    }

    /**
     * Get statistics
     */
    public function getStatistics(?int $poktanId = null)
    {
        $stats = $this->distributionRepository->getStatistics($poktanId);

        // Calculate average margin percentage
        if ($stats['total_revenue'] > 0) {
            $stats['average_margin_percentage'] = ($stats['total_margin'] / $stats['total_revenue']) * 100;
        } else {
            $stats['average_margin_percentage'] = 0;
        }

        return $stats;
    }

    /**
     * Get pending payment summary grouped by poktan
     */
    public function getPendingPaymentSummary()
    {
        $pendingDistributions = $this->distributionRepository->getPendingDistributions([]);

        // Group by poktan
        $summary = [];
        foreach ($pendingDistributions as $distribution) {
            $poktanId = $distribution->poktan_id;
            
            if (!isset($summary[$poktanId])) {
                $summary[$poktanId] = [
                    'poktan_id' => $poktanId,
                    'poktan_name' => $distribution->poktan->name,
                    'total_pending_amount' => 0,
                    'pending_count' => 0,
                    'distributions' => [],
                ];
            }

            $summary[$poktanId]['total_pending_amount'] += $distribution->poktan_payment;
            $summary[$poktanId]['pending_count']++;
            $summary[$poktanId]['distributions'][] = $distribution;
        }

        return array_values($summary);
    }

    /**
     * Batch mark multiple distributions as paid (for same poktan)
     */
    public function batchMarkAsPaid(array $distributionIds, ?string $notes = null)
    {
        try {
            DB::beginTransaction();

            $results = [];
            $totalAmount = 0;
            $poktanId = null;

            foreach ($distributionIds as $id) {
                $distribution = $this->distributionRepository->getDistributionById($id);

                // Validate all distributions belong to same poktan
                if ($poktanId === null) {
                    $poktanId = $distribution->poktan_id;
                } elseif ($poktanId !== $distribution->poktan_id) {
                    throw new Exception("All distributions must belong to the same poktan for batch payment");
                }

                if ($distribution->payment_status === 'paid') {
                    continue; // Skip already paid
                }

                $totalAmount += $distribution->poktan_payment;
                $results[] = $this->markAsPaid($id, $notes);
            }

            DB::commit();

            return [
                'processed_count' => count($results),
                'total_amount' => $totalAmount,
                'poktan_id' => $poktanId,
                'results' => $results,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in batch mark as paid: " . $e->getMessage());
            throw $e;
        }
    }
}
