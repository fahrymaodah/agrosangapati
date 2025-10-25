<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Get all orders (admin view)
     */
    public function getAllOrders(?string $orderStatus = null, ?string $paymentStatus = null)
    {
        return $this->orderRepository->getAll($orderStatus, $paymentStatus);
    }

    /**
     * Get pending orders
     */
    public function getPendingOrders()
    {
        return $this->orderRepository->getPendingOrders();
    }

    /**
     * Get active orders
     */
    public function getActiveOrders()
    {
        return $this->orderRepository->getActiveOrders();
    }

    /**
     * Get completed orders
     */
    public function getCompletedOrders()
    {
        return $this->orderRepository->getCompletedOrders();
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $id)
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new \Exception('Order not found.');
        }

        return $order;
    }

    /**
     * Track order by order number (public)
     */
    public function trackOrder(string $orderNumber)
    {
        $order = $this->orderRepository->findByOrderNumber($orderNumber);

        if (!$order) {
            throw new \Exception('Order not found. Please check your order number.');
        }

        return $order;
    }

    /**
     * Get orders by buyer phone (for customer to see their orders)
     */
    public function getOrdersByPhone(string $phone)
    {
        return $this->orderRepository->getByBuyerPhone($phone);
    }

    /**
     * Create new order from cart
     * 
     * @param array $data [buyer_name, buyer_phone, buyer_email, buyer_address, items[], shipping_cost, notes]
     * @return Order
     */
    public function createOrder(array $data)
    {
        // Validate items exist and not empty
        if (empty($data['items'])) {
            throw new \Exception('Order must contain at least one item.');
        }

        DB::beginTransaction();

        try {
            // Validate all products exist and have sufficient stock
            $items = [];
            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found.");
                }

                // Check if product is available for order
                if (!$product->canBeOrdered()) {
                    throw new \Exception("Product '{$product->name}' is not available for order.");
                }

                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$item['quantity']}");
                }

                // Check minimum order
                if ($product->minimum_order && $item['quantity'] < $product->minimum_order) {
                    throw new \Exception("Product '{$product->name}' has minimum order of {$product->minimum_order} {$product->unit}.");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Calculate grand total
            $shippingCost = $data['shipping_cost'] ?? 0;
            $grandTotal = $totalAmount + $shippingCost;

            // Generate order number
            $orderNumber = Order::generateOrderNumber();

            // Create order
            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'buyer_name' => $data['buyer_name'],
                'buyer_phone' => $data['buyer_phone'],
                'buyer_email' => $data['buyer_email'] ?? null,
                'buyer_address' => $data['buyer_address'],
                'total_amount' => $totalAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create order items and reduce stock
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'poktan_id' => null, // Will be set when order is confirmed
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Reserve stock (reduce from product)
                $newStock = $item['product']->stock_quantity - $item['quantity'];
                $this->productRepository->updateStock($item['product']->id, $newStock);

                // Auto-update product status if stock becomes 0
                if ($newStock <= 0) {
                    $this->productRepository->update($item['product']->id, [
                        'status' => 'sold_out',
                    ]);
                }
            }

            DB::commit();

            // Reload order with items
            return $this->orderRepository->findById($order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate order price (without creating order)
     * Useful for cart preview
     */
    public function calculateOrderPrice(array $items, float $shippingCost = 0): array
    {
        $totalAmount = 0;
        $itemDetails = [];

        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);

            if (!$product) {
                throw new \Exception("Product with ID {$item['product_id']} not found.");
            }

            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            $itemDetails[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        return [
            'items' => $itemDetails,
            'total_amount' => $totalAmount,
            'shipping_cost' => $shippingCost,
            'grand_total' => $totalAmount + $shippingCost,
        ];
    }

    /**
     * Cancel order (by customer or admin)
     */
    public function cancelOrder(int $id, ?string $reason = null)
    {
        $order = $this->getOrderById($id);

        if (!$order->canBeCancelled()) {
            throw new \Exception("Order with status '{$order->order_status}' cannot be cancelled.");
        }

        DB::beginTransaction();

        try {
            // Restore stock for all items
            foreach ($order->items as $item) {
                $product = $item->product;
                $newStock = $product->stock_quantity + $item->quantity;
                $this->productRepository->updateStock($product->id, $newStock);

                // Update status back to available if it was sold_out
                if ($product->status === 'sold_out' && $newStock > 0) {
                    $this->productRepository->update($product->id, [
                        'status' => 'available',
                    ]);
                }
            }

            // Cancel order
            $this->orderRepository->cancel($id, $reason);

            DB::commit();

            return $this->orderRepository->findById($id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics()
    {
        return $this->orderRepository->getStatistics();
    }

    /**
     * Confirm order (by gapoktan)
     * Changes status from pending to confirmed
     */
    public function confirmOrder(int $id, ?string $notes = null)
    {
        $order = $this->getOrderById($id);

        if (!$order->canBeConfirmed()) {
            throw new \Exception("Order with status '{$order->order_status}' cannot be confirmed.");
        }

        DB::beginTransaction();

        try {
            // Verify stock is still available for all items
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception(
                        "Insufficient stock for product '{$product->name}'. " .
                        "Available: {$product->stock_quantity}, Required: {$item->quantity}"
                    );
                }
            }

            // Update order status to confirmed
            $updateData = [
                'order_status' => 'confirmed',
                'updated_at' => now(),
            ];

            if ($notes) {
                $updateData['notes'] = $order->notes ? $order->notes . "\n\n[Konfirmasi] " . $notes : "[Konfirmasi] " . $notes;
            }

            $this->orderRepository->update($id, $updateData);

            DB::commit();

            return $this->orderRepository->findById($id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject order (by gapoktan)
     * Changes status to cancelled and restores stock
     */
    public function rejectOrder(int $id, string $reason)
    {
        $order = $this->getOrderById($id);

        if (!$order->canBeConfirmed()) {
            throw new \Exception("Order with status '{$order->order_status}' cannot be rejected.");
        }

        // Use cancelOrder which already handles stock restoration
        return $this->cancelOrder($id, "[Ditolak oleh Gapoktan] " . $reason);
    }

    /**
     * Update order status
     * For order workflow: confirmed → processing → shipped → delivered
     */
    public function updateOrderStatus(int $id, string $status, ?string $notes = null)
    {
        $order = $this->getOrderById($id);

        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid order status: {$status}");
        }

        // Prevent invalid status transitions
        if ($order->order_status === 'cancelled') {
            throw new \Exception("Cannot update status of a cancelled order.");
        }

        if ($order->order_status === 'delivered' && $status !== 'delivered') {
            throw new \Exception("Cannot change status of a delivered order.");
        }

        $updateData = [
            'order_status' => $status,
            'updated_at' => now(),
        ];

        if ($notes) {
            $updateData['notes'] = $order->notes ? $order->notes . "\n\n[Update Status] " . $notes : "[Update Status] " . $notes;
        }

        $this->orderRepository->update($id, $updateData);

        return $this->orderRepository->findById($id);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $id, string $paymentStatus, ?string $notes = null)
    {
        $order = $this->getOrderById($id);

        $validStatuses = ['unpaid', 'partial', 'paid', 'refunded'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \Exception("Invalid payment status: {$paymentStatus}");
        }

        $updateData = [
            'payment_status' => $paymentStatus,
            'updated_at' => now(),
        ];

        if ($notes) {
            $updateData['notes'] = $order->notes ? $order->notes . "\n\n[Payment Update] " . $notes : "[Payment Update] " . $notes;
        }

        $this->orderRepository->update($id, $updateData);

        return $this->orderRepository->findById($id);
    }

    /**
     * Mark order as processing
     * Usually done after payment confirmed
     */
    public function markAsProcessing(int $id, ?string $notes = null)
    {
        return $this->updateOrderStatus($id, 'processing', $notes ?: 'Order sedang diproses');
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(int $id, ?string $notes = null)
    {
        return $this->updateOrderStatus($id, 'shipped', $notes ?: 'Pesanan telah dikirim');
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(int $id, ?string $notes = null)
    {
        return $this->updateOrderStatus($id, 'delivered', $notes ?: 'Pesanan telah diterima');
    }
}
