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
     * List all orders for the authenticated user with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->get('status'),
        ];

        $perPage = $request->get('per_page', 15);

        $orders = $this->orderService->getOrders($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * List ALL orders for admin (all users)
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
        ];

        $perPage = $request->get('per_page', 15);

        $orders = $this->orderService->getAllOrders($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Show a single order with items and product details
     */
    public function show($id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product')
        ]);
    }

    /**
     * Create a new order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
           'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'shipping_address' => 'required|array',
        'shipping_address.first_name' => 'required|string|max:255',
        'shipping_address.last_name' => 'required|string|max:255',
        'shipping_address.email' => 'required|email',
        'shipping_address.contact_number' => 'required|string|max:20',
        'shipping_address.address' => 'required|string',
        'shipping_address.district' => 'required|string|max:255',
        'shipping_address.landmark' => 'nullable|string|max:255',
        'shipping_address.delivery_instructions' => 'nullable|string',
        'billing_address' => 'nullable|array',
        'payment_method' => 'required|string',
        'notes' => 'nullable|string'
        ]);

        try {
            $order = $this->orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'data' => $order->load('items.product'),
                'message' => 'Order created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update order or payment status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,completed,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded'
        ]);

        $order = $this->orderService->updateStatus($id, $validated);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product'),
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Admin update order (status and other fields)
     */
    public function adminUpdate(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string'
        ]);

        $order = $this->orderService->adminUpdateOrder($id, $validated);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product'),
            'message' => 'Order updated successfully'
        ]);
    }
}