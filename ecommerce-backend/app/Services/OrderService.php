<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderService
{
    /**
     * Get orders with optional filtering and pagination.
     */
    public function getOrders(array $filters = [], int $perPage = 15)
    {
        $user = Auth::user();
        $query = Order::with('items.product');

        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get ALL orders for admin (all users) with filtering
     */
    public function getAllOrders(array $filters = [], int $perPage = 15)
    {
        $query = Order::with(['items.product', 'user']); // Include user relationship

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get single order by ID if accessible by user.
     */
    public function getOrderById(int $id): ?Order
    {
        $user = Auth::user();
        $order = Order::with('items.product')->find($id);

        if (!$order || ($order->user_id !== $user->id && !$user->is_admin)) {
            return null;
        }

        return $order;
    }

    /**
     * Create a new order and calculate totals.
     */
    public function createOrder(array $data): Order
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('User not authenticated.');
        }

        if (empty($data['items']) || !is_array($data['items'])) {
            throw new Exception('Order items are required.');
        }

        DB::beginTransaction();

        try {
            // Create order first with basic info - let model booted() handle totals
            $order = Order::create([
                'order_number' => 'ORD' . time() . rand(1000, 9999),
                'user_id' => $user->id,
                'subtotal' => 0, // Will be recalculated by booted()
                'tax' => 0.1, // 10% tax rate
                'shipping' => 10, // Default shipping
                'total' => 0, // Will be recalculated by booted()
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Loop through items and create them
            foreach ($data['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new Exception('Product not found: ID ' . $itemData['product_id']);
                }

                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new Exception('Insufficient stock for product: ' . $product->name);
                }

                $price = (float) ($product->sale_price ?? $product->price);

                // Create the order item - this will trigger Order model booted() to recalculate totals
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $price,
                    'total' => $itemData['quantity'] * $price, // Set total directly
                ]);

                // Decrement product stock
                $product->decrement('stock_quantity', $itemData['quantity']);
            }

            // Refresh to get calculated totals from booted()
            $order->refresh();
            $order->load('items.product');

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status or payment status.
     */
    public function updateStatus(int $id, array $data): ?Order
    {
        $order = Order::find($id);
        if (!$order) {
            return null;
        }

        $order->update($data);

        return $order;
    }

    /**
     * Admin update order (status and other fields)
     */
    public function adminUpdateOrder(int $id, array $data): ?Order
    {
        $order = Order::find($id);
        if (!$order) {
            return null;
        }

        $order->update($data);

        return $order;
    }
}