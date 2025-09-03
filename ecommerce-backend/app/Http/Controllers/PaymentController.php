<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Exception;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    // Create Stripe Payment Intent for an order
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::find($validated['order_id']);
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already paid',
            ], 400);
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => intval($order->total * 100), // Stripe uses cents
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ],
                'description' => "Payment for Order #" . $order->order_number,
                'payment_method_types' => ['card'], // Enable card (Apple/Google Pay automatically supported)
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment Intent creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Confirm payment
    public function confirmPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);
            $orderId = $paymentIntent->metadata->order_id ?? null;
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($paymentIntent->status === 'succeeded') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed and order updated',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not successful',
                    'payment_status' => $paymentIntent->status,
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Stripe webhook
    public function webhook(Request $request)
    {
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        $paymentIntent = $event->data->object;
        $orderId = $paymentIntent->metadata->order_id ?? null;
        $order = Order::find($orderId);

        if ($event->type === 'payment_intent.succeeded' && $order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        } elseif ($event->type === 'payment_intent.payment_failed' && $order) {
            $order->update([
                'payment_status' => 'failed',
            ]);
        }

        return response('Webhook handled', 200);
    }

    // Only Stripe as payment method
    public function getPaymentMethods(): JsonResponse
    {
        $methods = [
            ['id' => 'stripe', 'name' => 'Credit Card / Apple Pay / Google Pay']
        ];

        return response()->json([
            'success' => true,
            'data' => $methods
        ]);
    }
}
