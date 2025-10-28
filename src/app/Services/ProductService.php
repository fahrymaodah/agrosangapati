<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Services\Contracts\FileUploadServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;
    protected StockRepositoryInterface $stockRepository;
    protected FileUploadServiceInterface $fileUploadService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRepositoryInterface $stockRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->productRepository = $productRepository;
        $this->stockRepository = $stockRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all products.
     */
    public function getAllProducts(): Collection
    {
        return $this->productRepository->getAll(['commodity', 'grade', 'creator']);
    }

    /**
     * Get public catalog (for customers).
     */
    public function getPublicCatalog(): Collection
    {
        return $this->productRepository->getPublicProducts(['commodity', 'grade']);
    }

    /**
     * Get available products (in stock).
     */
    public function getAvailableProducts(): Collection
    {
        return $this->productRepository->getAvailableProducts(['commodity', 'grade']);
    }

    /**
     * Get product by ID.
     */
    public function getProductById(int $id, bool $incrementViews = false): ?Product
    {
        $product = $this->productRepository->findById($id, ['commodity', 'grade', 'creator']);
        
        if ($product && $incrementViews) {
            $this->productRepository->incrementViews($id);
        }

        return $product;
    }

    /**
     * Search products.
     */
    public function searchProducts(string $query): Collection
    {
        return $this->productRepository->search($query, ['commodity', 'grade']);
    }

    /**
     * Get products by commodity.
     */
    public function getProductsByCommodity(int $commodityId): Collection
    {
        return $this->productRepository->getByCommodity($commodityId, ['commodity', 'grade']);
    }

    /**
     * Get products by status.
     */
    public function getProductsByStatus(string $status): Collection
    {
        return $this->productRepository->getByStatus($status, ['commodity', 'grade']);
    }

    /**
     * Get popular products.
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        return $this->productRepository->getPopularProducts($limit, ['commodity', 'grade']);
    }

    /**
     * Create a new product from gapoktan stock.
     */
    public function createProduct(array $data): Product
    {
        // Validate if gapoktan has stock for this commodity+grade
        $gapoktanStock = $this->stockRepository->getByCommodityGrade(
            null, // gapoktan stock (poktan_id = null)
            $data['commodity_id'],
            $data['grade_id']
        );

        if (!$gapoktanStock || $gapoktanStock->quantity <= 0) {
            throw new \Exception('Stok tidak tersedia di gudang gapoktan untuk komoditas dan grade ini.');
        }

        // Set initial stock_quantity from gapoktan stock
        if (!isset($data['stock_quantity'])) {
            $data['stock_quantity'] = $gapoktanStock->quantity;
        }

        // Validate stock_quantity doesn't exceed available stock
        if ($data['stock_quantity'] > $gapoktanStock->quantity) {
            throw new \Exception("Stok yang tersedia hanya {$gapoktanStock->quantity} {$data['unit']}.");
        }

        // Handle product photos upload (if provided) using FileUploadService
        if (isset($data['product_photos']) && is_array($data['product_photos'])) {
            $photos = [];
            foreach ($data['product_photos'] as $photo) {
                if ($photo instanceof \Illuminate\Http\UploadedFile) {
                    $result = $this->fileUploadService->uploadImage($photo, 'products', [
                        'optimize' => true,
                        'max_image_width' => 1200,
                        'max_image_height' => 1200,
                        'image_quality' => 85,
                        'generate_thumbnail' => true,
                        'thumbnail_size' => 300,
                    ]);
                    $photos[] = $result['path'];
                }
            }
            $data['product_photos'] = $photos;
        }

        // Set status based on stock
        if (!isset($data['status'])) {
            $data['status'] = $data['stock_quantity'] > 0 ? 'available' : 'sold_out';
        }

        return $this->productRepository->create($data);
    }

    /**
     * Update a product.
     */
    public function updateProduct(int $id, array $data): bool
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Produk tidak ditemukan.');
        }

        // If updating stock_quantity, validate against gapoktan stock
        if (isset($data['stock_quantity'])) {
            $gapoktanStock = $this->stockRepository->getByCommodityGrade(
                null,
                $product->commodity_id,
                $product->grade_id
            );

            if ($gapoktanStock && $data['stock_quantity'] > $gapoktanStock->quantity) {
                throw new \Exception("Stok yang tersedia hanya {$gapoktanStock->quantity} {$product->unit}.");
            }

            // Auto-update status based on stock
            if ($data['stock_quantity'] <= 0) {
                $data['status'] = 'sold_out';
            } elseif ($product->status === 'sold_out') {
                $data['status'] = 'available';
            }
        }

        // Handle new photos upload using FileUploadService
        if (isset($data['product_photos']) && is_array($data['product_photos'])) {
            $newPhotos = [];
            $existingPhotos = $product->product_photos ?? [];

            foreach ($data['product_photos'] as $photo) {
                if ($photo instanceof \Illuminate\Http\UploadedFile) {
                    $result = $this->fileUploadService->uploadImage($photo, 'products', [
                        'optimize' => true,
                        'max_image_width' => 1200,
                        'max_image_height' => 1200,
                        'image_quality' => 85,
                        'generate_thumbnail' => true,
                        'thumbnail_size' => 300,
                    ]);
                    $newPhotos[] = $result['path'];
                } elseif (is_string($photo)) {
                    // Keep existing photo paths
                    $newPhotos[] = $photo;
                }
            }
            
            // Delete photos that were removed
            $photosToDelete = array_diff($existingPhotos, $newPhotos);
            if (!empty($photosToDelete)) {
                $this->fileUploadService->deleteMultiple($photosToDelete);
            }
            
            $data['product_photos'] = $newPhotos;
        }

        return $this->productRepository->update($id, $data);
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Produk tidak ditemukan.');
        }

        // Delete product photos from storage using FileUploadService
        if ($product->product_photos) {
            $this->fileUploadService->deleteMultiple($product->product_photos);
        }

        return $this->productRepository->delete($id);
    }

    /**
     * Update product status.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['available', 'pre_order', 'sold_out', 'inactive'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Status tidak valid.');
        }

        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Produk tidak ditemukan.');
        }

        // Can't set to 'available' if no stock
        if ($status === 'available' && $product->stock_quantity <= 0) {
            throw new \Exception('Produk tidak bisa diaktifkan karena stok kosong.');
        }

        return $this->productRepository->update($id, ['status' => $status]);
    }

    /**
     * Sync product stock with gapoktan stock.
     */
    public function syncStockWithGapoktan(int $productId): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new \Exception('Produk tidak ditemukan.');
        }

        $gapoktanStock = $this->stockRepository->getByCommodityGrade(
            null,
            $product->commodity_id,
            $product->grade_id
        );

        if (!$gapoktanStock) {
            throw new \Exception('Stok gapoktan tidak ditemukan.');
        }

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $gapoktanStock->quantity;

        $this->productRepository->updateStock($productId, $newQuantity);

        // Update status based on new quantity
        if ($newQuantity <= 0 && $product->status === 'available') {
            $this->productRepository->update($productId, ['status' => 'sold_out']);
        } elseif ($newQuantity > 0 && $product->status === 'sold_out') {
            $this->productRepository->update($productId, ['status' => 'available']);
        }

        return [
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $newQuantity - $oldQuantity,
        ];
    }

    /**
     * Get product statistics.
     */
    public function getProductStatistics(): array
    {
        $all = $this->productRepository->getAll();
        
        return [
            'total_products' => $all->count(),
            'available' => $all->where('status', 'available')->count(),
            'pre_order' => $all->where('status', 'pre_order')->count(),
            'sold_out' => $all->where('status', 'sold_out')->count(),
            'inactive' => $all->where('status', 'inactive')->count(),
            'total_stock_value' => $all->sum(function ($product) {
                return $product->stock_quantity * $product->price;
            }),
            'total_views' => $all->sum('views_count'),
        ];
    }
}
