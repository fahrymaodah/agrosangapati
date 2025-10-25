<?php

namespace App\Http\Controllers;

use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Get all shipments with filters
     * GET /api/shipments
     * 
     * Query params:
     * - status: preparing|picked_up|in_transit|delivered
     * - courier: courier name
     * - from_date: YYYY-MM-DD
     * - to_date: YYYY-MM-DD
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'courier', 'from_date', 'to_date']);
            $shipments = $this->shipmentService->getAllShipments($filters);

            return response()->json([
                'success' => true,
                'data' => $shipments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get shipment by ID
     * GET /api/shipments/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $shipment = $this->shipmentService->getShipmentById($id);

            return response()->json([
                'success' => true,
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get shipment by order ID
     * GET /api/orders/{orderId}/shipment
     */
    public function getByOrderId(int $orderId): JsonResponse
    {
        try {
            $shipment = $this->shipmentService->getShipmentByOrderId($orderId);

            if (!$shipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not found for this order.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track shipment by tracking number (public)
     * GET /api/shipments/track/{trackingNumber}
     */
    public function track(string $trackingNumber): JsonResponse
    {
        try {
            $shipment = $this->shipmentService->trackShipment($trackingNumber);

            return response()->json([
                'success' => true,
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking number not found.',
            ], 404);
        }
    }

    /**
     * Create shipment for an order
     * POST /api/orders/{orderId}/shipment
     * 
     * Body:
     * {
     *   "courier_name": "JNE",
     *   "tracking_number": "JNE123456789",
     *   "shipping_date": "2025-10-26",
     *   "estimated_arrival": "2025-10-30",
     *   "notes": "Fragile items"
     * }
     */
    public function store(Request $request, int $orderId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'courier_name' => 'required|string|max:255',
                'tracking_number' => 'nullable|string|max:255',
                'shipping_date' => 'required|date',
                'estimated_arrival' => 'nullable|date|after_or_equal:shipping_date',
                'notes' => 'nullable|string|max:1000',
            ]);

            $shipment = $this->shipmentService->createShipment($orderId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipment created successfully.',
                'data' => $shipment,
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
     * Update shipment
     * PUT /api/shipments/{id}
     * 
     * Body:
     * {
     *   "courier_name": "JNE Express",
     *   "tracking_number": "JNE987654321",
     *   "estimated_arrival": "2025-10-29",
     *   "notes": "Updated delivery schedule"
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'courier_name' => 'sometimes|string|max:255',
                'tracking_number' => 'sometimes|nullable|string|max:255',
                'shipping_date' => 'sometimes|date',
                'estimated_arrival' => 'sometimes|nullable|date',
                'shipment_status' => 'sometimes|in:preparing,picked_up,in_transit,delivered',
                'notes' => 'sometimes|nullable|string|max:1000',
            ]);

            $shipment = $this->shipmentService->updateShipment($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipment updated successfully.',
                'data' => $shipment,
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
     * Mark shipment as picked up
     * POST /api/shipments/{id}/picked-up
     */
    public function markAsPickedUp(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $shipment = $this->shipmentService->markAsPickedUp($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Shipment marked as picked up.',
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark shipment as in transit
     * POST /api/shipments/{id}/in-transit
     */
    public function markAsInTransit(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $shipment = $this->shipmentService->markAsInTransit($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Shipment marked as in transit.',
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark shipment as delivered
     * POST /api/shipments/{id}/delivered
     */
    public function markAsDelivered(Request $request, int $id): JsonResponse
    {
        try {
            $notes = $request->input('notes');
            $shipment = $this->shipmentService->markAsDelivered($id, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Shipment marked as delivered.',
                'data' => $shipment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload proof of delivery photo
     * POST /api/shipments/{id}/proof-photo
     * 
     * Body (multipart/form-data):
     * - photo: image file
     */
    public function uploadProofPhoto(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'photo' => 'required|image|max:5120', // Max 5MB
            ]);

            // Store photo
            $path = $request->file('photo')->store('shipments/proof', 'public');

            $shipment = $this->shipmentService->uploadProofPhoto($id, $path);

            return response()->json([
                'success' => true,
                'message' => 'Proof photo uploaded successfully.',
                'data' => $shipment,
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
     * Get shipments in progress
     * GET /api/shipments/in-progress
     */
    public function inProgress(): JsonResponse
    {
        try {
            $shipments = $this->shipmentService->getShipmentsInProgress();

            return response()->json([
                'success' => true,
                'data' => $shipments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get late shipments
     * GET /api/shipments/late
     */
    public function late(): JsonResponse
    {
        try {
            $shipments = $this->shipmentService->getLateShipments();

            return response()->json([
                'success' => true,
                'data' => $shipments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get shipment statistics
     * GET /api/shipments/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->shipmentService->getShipmentStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get shipments by courier
     * GET /api/shipments/courier/{courier}
     */
    public function byCourier(string $courier): JsonResponse
    {
        try {
            $shipments = $this->shipmentService->getShipmentsByCourier($courier);

            return response()->json([
                'success' => true,
                'data' => $shipments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete shipment
     * DELETE /api/shipments/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->shipmentService->deleteShipment($id);

            return response()->json([
                'success' => true,
                'message' => 'Shipment deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
