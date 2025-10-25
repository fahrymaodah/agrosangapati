<?php

namespace App\Services;

use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    protected StockRepositoryInterface $stockRepository;
    protected StockMovementRepositoryInterface $movementRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockMovementRepositoryInterface $movementRepository
    ) {
        $this->stockRepository = $stockRepository;
        $this->movementRepository = $movementRepository;
    }

    /**
     * Get all stocks for a poktan.
     */
    public function getAllByPoktan(int $poktanId): array
    {
        $stocks = $this->stockRepository->getByPoktan($poktanId, ['commodity', 'grade']);
        
        return [
            'stocks' => $stocks->map(fn($stock) => $this->formatStockResponse($stock)),
            'total_items' => $stocks->count(),
        ];
    }

    /**
     * Get stock by ID.
     */
    public function getById(int $id): ?array
    {
        $stock = $this->stockRepository->findById($id, ['*'], ['poktan', 'commodity', 'grade']);
        
        if (!$stock) {
            return null;
        }

        return $this->formatStockResponse($stock);
    }

    /**
     * Get low stock items.
     */
    public function getLowStock(int $poktanId, float $minimumQuantity = 100): array
    {
        $stocks = $this->stockRepository->getLowStock($poktanId, $minimumQuantity);
        
        return $stocks->map(fn($stock) => $this->formatStockResponse($stock))->toArray();
    }

    /**
     * Get stock by location.
     */
    public function getByLocation(int $poktanId, ?string $location): array
    {
        $stocks = $this->stockRepository->getByLocation($poktanId, $location);
        
        return $stocks->map(fn($stock) => $this->formatStockResponse($stock))->toArray();
    }

    /**
     * Get stock summary.
     */
    public function getSummary(int $poktanId): array
    {
        return $this->stockRepository->getSummaryByPoktan($poktanId);
    }

    /**
     * Add stock (incoming).
     */
    public function addStock(array $data, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            // Find or create stock record
            $stock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['location'] ?? null
            );

            if (!$stock) {
                // Create new stock
                $stock = $this->stockRepository->create([
                    'poktan_id' => $data['poktan_id'],
                    'commodity_id' => $data['commodity_id'],
                    'grade_id' => $data['grade_id'],
                    'quantity' => $data['quantity'],
                    'unit' => $data['unit'],
                    'location' => $data['location'] ?? null,
                    'last_updated' => now(),
                ]);
            } else {
                // Update existing stock
                $newQuantity = $stock->quantity + $data['quantity'];
                $this->stockRepository->update($stock->id, [
                    'quantity' => $newQuantity,
                    'last_updated' => now(),
                ]);
                $stock->refresh();
            }

            // Record stock movement
            $this->movementRepository->create([
                'stock_id' => $stock->id,
                'movement_type' => 'in',
                'quantity' => $data['quantity'],
                'to_location' => $data['location'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            DB::commit();

            return $this->formatStockResponse($stock->load(['commodity', 'grade']));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add stock: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove stock (outgoing).
     */
    public function removeStock(array $data, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            // Find stock record
            $stock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['location'] ?? null
            );

            if (!$stock) {
                throw new Exception('Stock not found');
            }

            if ($stock->quantity < $data['quantity']) {
                throw new Exception('Insufficient stock quantity');
            }

            // Update stock quantity
            $newQuantity = $stock->quantity - $data['quantity'];
            $this->stockRepository->update($stock->id, [
                'quantity' => $newQuantity,
                'last_updated' => now(),
            ]);

            // Record stock movement
            $this->movementRepository->create([
                'stock_id' => $stock->id,
                'movement_type' => 'out',
                'quantity' => $data['quantity'],
                'from_location' => $data['location'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            DB::commit();

            $stock->refresh();
            return $this->formatStockResponse($stock->load(['commodity', 'grade']));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove stock: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transfer stock between locations.
     */
    public function transferStock(array $data, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            // Remove from source
            $sourceStock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['from_location']
            );

            if (!$sourceStock) {
                throw new Exception('Source stock not found');
            }

            if ($sourceStock->quantity < $data['quantity']) {
                throw new Exception('Insufficient stock quantity at source location');
            }

            // Update source stock
            $this->stockRepository->update($sourceStock->id, [
                'quantity' => $sourceStock->quantity - $data['quantity'],
                'last_updated' => now(),
            ]);

            // Add to destination
            $destStock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['to_location']
            );

            if (!$destStock) {
                // Create destination stock
                $destStock = $this->stockRepository->create([
                    'poktan_id' => $data['poktan_id'],
                    'commodity_id' => $data['commodity_id'],
                    'grade_id' => $data['grade_id'],
                    'quantity' => $data['quantity'],
                    'unit' => $sourceStock->unit,
                    'location' => $data['to_location'],
                    'last_updated' => now(),
                ]);
            } else {
                // Update destination stock
                $this->stockRepository->update($destStock->id, [
                    'quantity' => $destStock->quantity + $data['quantity'],
                    'last_updated' => now(),
                ]);
            }

            // Record transfer movement for source
            $this->movementRepository->create([
                'stock_id' => $sourceStock->id,
                'movement_type' => 'transfer',
                'quantity' => $data['quantity'],
                'from_location' => $data['from_location'],
                'to_location' => $data['to_location'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Record transfer movement for destination
            $this->movementRepository->create([
                'stock_id' => $destStock->id,
                'movement_type' => 'transfer',
                'quantity' => $data['quantity'],
                'from_location' => $data['from_location'],
                'to_location' => $data['to_location'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            DB::commit();

            return [
                'source' => $this->formatStockResponse($sourceStock->load(['commodity', 'grade'])),
                'destination' => $this->formatStockResponse($destStock->load(['commodity', 'grade'])),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to transfer stock: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Record damaged stock.
     */
    public function recordDamage(array $data, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            $stock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['location'] ?? null
            );

            if (!$stock) {
                throw new Exception('Stock not found');
            }

            if ($stock->quantity < $data['quantity']) {
                throw new Exception('Insufficient stock quantity');
            }

            // Update stock quantity
            $newQuantity = $stock->quantity - $data['quantity'];
            $this->stockRepository->update($stock->id, [
                'quantity' => $newQuantity,
                'last_updated' => now(),
            ]);

            // Record damage movement
            $this->movementRepository->create([
                'stock_id' => $stock->id,
                'movement_type' => 'damaged',
                'quantity' => $data['quantity'],
                'from_location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? 'Barang rusak/hilang',
                'created_by' => $userId,
            ]);

            DB::commit();

            $stock->refresh();
            return $this->formatStockResponse($stock->load(['commodity', 'grade']));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to record damage: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get stock movements.
     */
    public function getMovements(int $stockId, int $limit = 50): array
    {
        $movements = $this->movementRepository->getByStock($stockId, $limit);
        
        return $movements->map(function ($movement) {
            return [
                'id' => $movement->id,
                'type' => $movement->movement_type,
                'quantity' => (float) $movement->quantity,
                'from_location' => $movement->from_location,
                'to_location' => $movement->to_location,
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'notes' => $movement->notes,
                'created_by' => $movement->creator ? [
                    'id' => $movement->creator->id,
                    'name' => $movement->creator->name,
                ] : null,
                'created_at' => $movement->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get recent movements for poktan.
     */
    public function getRecentMovements(int $poktanId, int $limit = 20): array
    {
        $movements = $this->movementRepository->getRecentByPoktan($poktanId, $limit);
        
        return $movements->map(function ($movement) {
            return [
                'id' => $movement->id,
                'stock_id' => $movement->stock_id,
                'commodity' => $movement->stock->commodity ? [
                    'id' => $movement->stock->commodity->id,
                    'name' => $movement->stock->commodity->name,
                ] : null,
                'grade' => $movement->stock->grade ? [
                    'id' => $movement->stock->grade->id,
                    'name' => $movement->stock->grade->name,
                ] : null,
                'type' => $movement->movement_type,
                'quantity' => (float) $movement->quantity,
                'from_location' => $movement->from_location,
                'to_location' => $movement->to_location,
                'notes' => $movement->notes,
                'created_by' => $movement->creator ? [
                    'id' => $movement->creator->id,
                    'name' => $movement->creator->name,
                ] : null,
                'created_at' => $movement->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Transfer stock from poktan to gapoktan.
     */
    public function transferToGapoktan(array $data, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            // Get source stock (poktan)
            $sourceStock = $this->stockRepository->getByCommodityGrade(
                $data['poktan_id'],
                $data['commodity_id'],
                $data['grade_id'],
                $data['from_location'] ?? null
            );

            if (!$sourceStock) {
                throw new Exception('Source stock not found');
            }

            if ($sourceStock->quantity < $data['quantity']) {
                throw new Exception('Insufficient stock quantity. Available: ' . $sourceStock->quantity);
            }

            // Reduce source stock (poktan)
            $newSourceQuantity = $sourceStock->quantity - $data['quantity'];
            $this->stockRepository->update($sourceStock->id, [
                'quantity' => $newSourceQuantity,
                'last_updated' => now(),
            ]);

            // Find or create destination stock (gapoktan - poktan_id = NULL)
            $destStock = $this->stockRepository->getByCommodityGrade(
                null, // poktan_id = null for gapoktan
                $data['commodity_id'],
                $data['grade_id'],
                $data['to_location'] ?? 'Gudang Pusat Gapoktan'
            );

            if (!$destStock) {
                // Create gapoktan stock
                $destStock = $this->stockRepository->create([
                    'poktan_id' => null,
                    'commodity_id' => $data['commodity_id'],
                    'grade_id' => $data['grade_id'],
                    'quantity' => $data['quantity'],
                    'unit' => $data['unit'],
                    'location' => $data['to_location'] ?? 'Gudang Pusat Gapoktan',
                    'last_updated' => now(),
                ]);
            } else {
                // Update gapoktan stock
                $newDestQuantity = $destStock->quantity + $data['quantity'];
                $this->stockRepository->update($destStock->id, [
                    'quantity' => $newDestQuantity,
                    'last_updated' => now(),
                ]);
            }

            // Record movement for source (transfer out from poktan)
            $this->movementRepository->create([
                'stock_id' => $sourceStock->id,
                'movement_type' => 'transfer',
                'quantity' => $data['quantity'],
                'from_location' => $data['from_location'] ?? $sourceStock->location,
                'to_location' => $data['to_location'] ?? 'Gudang Pusat Gapoktan',
                'reference_type' => 'transfer_to_gapoktan',
                'reference_id' => $destStock->id,
                'notes' => $data['notes'] ?? 'Transfer to Gapoktan',
                'created_by' => $userId,
            ]);

            // Record movement for destination (transfer in to gapoktan)
            $this->movementRepository->create([
                'stock_id' => $destStock->id,
                'movement_type' => 'in',
                'quantity' => $data['quantity'],
                'from_location' => $data['from_location'] ?? $sourceStock->location,
                'to_location' => $data['to_location'] ?? 'Gudang Pusat Gapoktan',
                'reference_type' => 'transfer_from_poktan',
                'reference_id' => $sourceStock->id,
                'notes' => $data['notes'] ?? 'Received from Poktan: ' . ($sourceStock->poktan->name ?? 'Unknown'),
                'created_by' => $userId,
            ]);

            DB::commit();

            // Reload stocks with relations
            $sourceStock->refresh();
            $destStock->refresh();

            return [
                'source' => $this->formatStockResponse($sourceStock->load(['poktan', 'commodity', 'grade'])),
                'destination' => $this->formatStockResponse($destStock->load(['poktan', 'commodity', 'grade'])),
                'transferred_quantity' => (float) $data['quantity'],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to transfer stock to gapoktan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all gapoktan stocks.
     */
    public function getGapoktanStocks(): array
    {
        $stocks = $this->stockRepository->getByPoktan(null, ['commodity', 'grade']);
        
        return [
            'stocks' => $stocks->map(fn($stock) => $this->formatStockResponse($stock)),
            'total_items' => $stocks->count(),
        ];
    }

    /**
     * Get gapoktan stock summary.
     */
    public function getGapoktanSummary(): array
    {
        return $this->stockRepository->getSummaryByPoktan(null);
    }

    /**
     * Format stock response.
     */
    private function formatStockResponse($stock): array
    {
        return [
            'id' => $stock->id,
            'poktan' => $stock->poktan ? [
                'id' => $stock->poktan->id,
                'name' => $stock->poktan->name,
            ] : null,
            'commodity' => $stock->commodity ? [
                'id' => $stock->commodity->id,
                'name' => $stock->commodity->name,
                'unit' => $stock->commodity->unit,
            ] : null,
            'grade' => $stock->grade ? [
                'id' => $stock->grade->id,
                'name' => $stock->grade->name,
            ] : null,
            'quantity' => (float) $stock->quantity,
            'unit' => $stock->unit,
            'location' => $stock->location,
            'last_updated' => $stock->last_updated->format('Y-m-d H:i:s'),
            'created_at' => $stock->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $stock->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
