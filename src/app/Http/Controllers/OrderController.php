<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get all orders (admin)
     * GET /api/orders?order_status=pending&payment_status=unpaid
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orderStatus = $request->query('order_status');
            $paymentStatus = $request->query('payment_status');

            $orders = $this->orderService->getAllOrders($orderStatus, $paymentStatus);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending orders (admin)
     * GET /api/orders/pending
     */
    public function pending(): JsonResponse
    {
        try {
            $orders = $this->orderService->getPendingOrders();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active orders (admin)
     * GET /api/orders/active
     */
    public function active(): JsonResponse
    {
        try {
            $orders = $this->orderService->getActiveOrders();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get completed orders (admin)
     * GET /api/orders/completed
     */
    public function completed(): JsonResponse
    {
        try {
            $orders = $this->orderService->getCompletedOrders();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order statistics (admin)
     * GET /api/orders/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->orderService->getOrderStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track order by order number (public)
     * GET /api/orders/track/{orderNumber}
     */
    public function track(string $orderNumber): JsonResponse
    {
        try {
            $order = $this->orderService->trackOrder($orderNumber);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get orders by buyer phone (public)
     * GET /api/orders/by-phone/{phone}
     */
    public function byPhone(string $phone): JsonResponse
    {
        try {
            $orders = $this->orderService->getOrdersByPhone($phone);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show order detail
     * GET /api/orders/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderById($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create new order (public)
     * POST /api/orders
     * 
     * Body:
     * {
     *   "buyer_name": "John Doe",
     *   "buyer_phone": "081234567890",
     *   "buyer_email": "john@example.com",
     *   "buyer_address": "Jl. Example No. 123",
     *   "shipping_cost": 50000,
     *   "notes": "Please pack carefully",
     *   "items": [
     *     {"product_id": 1, "quantity": 10},
     *     {"product_id": 2, "quantity": 5}
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation
            $validated = $request->validate([
                'buyer_name' => 'required|string|max:255',
                'buyer_phone' => 'required|string|max:20',
                'buyer_email' => 'nullable|email|max:255',
                'buyer_address' => 'required|string',
                'shipping_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
            ]);

            $order = $this->orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => $order,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Calculate order price (without creating order)
     * POST /api/orders/calculate
     * 
     * Body:
     * {
     *   "items": [
     *     {"product_id": 1, "quantity": 10},
     *     {"product_id": 2, "quantity": 5}
     *   ],
     *   "shipping_cost": 50000
     * }
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'shipping_cost' => 'nullable|numeric|min:0',
            ]);

            $calculation = $this->orderService->calculateOrderPrice(
                $validated['items'],
                $validated['shipping_cost'] ?? 0
            );

            return response()->json([
                'success' => true,
                'data' => $calculation,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel order
     * POST /api/orders/{id}/cancel
     * 
     * Body (optional):
     * {
     *   "reason": "Customer request"
     * }
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $reason = $request->input('reason');
            $order = $this->orderService->cancelOrder($id, $reason);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm order (Gapoktan)
     * POST /api/orders/{id}/confirm
     * 
     * Body (optional):
     * {
     *   "notes": "Pesanan dikonfirmasi, akan diproses segera"
     * }
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $order = $this->orderService->confirmOrder($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed successfully.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject order (Gapoktan)
     * POST /api/orders/{id}/reject
     * 
     * Body:
     * {
     *   "reason": "Stok tidak tersedia"
     * }
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $order = $this->orderService->rejectOrder($id, $validated['reason']);

            return response()->json([
                'success' => true,
                'message' => 'Order rejected successfully.',
                'data' => $order,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update order status (Gapoktan)
     * PATCH /api/orders/{id}/status
     * 
     * Body:
     * {
     *   "status": "processing|shipped|delivered",
     *   "notes": "Optional notes"
     * }
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled',
                'notes' => 'nullable|string|max:500',
            ]);

            $order = $this->orderService->updateOrderStatus(
                $id,
                $validated['status'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'data' => $order,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update payment status (Gapoktan)
     * PATCH /api/orders/{id}/payment-status
     * 
     * Body:
     * {
     *   "payment_status": "paid|partial|unpaid|refunded",
     *   "notes": "Optional notes"
     * }
     */
    public function updatePaymentStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'payment_status' => 'required|string|in:unpaid,partial,paid,refunded',
                'notes' => 'nullable|string|max:500',
            ]);

            $order = $this->orderService->updatePaymentStatus(
                $id,
                $validated['payment_status'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.',
                'data' => $order,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark order as processing (Gapoktan)
     * POST /api/orders/{id}/processing
     */
    public function markAsProcessing(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $order = $this->orderService->markAsProcessing($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as processing.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark order as shipped (Gapoktan)
     * POST /api/orders/{id}/shipped
     */
    public function markAsShipped(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $order = $this->orderService->markAsShipped($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as shipped.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark order as delivered (Gapoktan)
     * POST /api/orders/{id}/delivered
     */
    public function markAsDelivered(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $order = $this->orderService->markAsDelivered($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as delivered.',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
