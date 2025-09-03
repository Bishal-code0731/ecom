<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    public function __construct()
    {
        // Initialize Stripe with the secret key from env
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe PaymentIntent.
     *
     * @param int $amount Amount in cents
     * @param string $currency Currency code (e.g., 'usd')
     * @param array $metadata Optional metadata
     * @return PaymentIntent|null
     * @throws ApiErrorException
     */
    public function createPaymentIntent(int $amount, string $currency = 'usd', array $metadata = []): ?PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    /**
     * Retrieve a PaymentIntent by ID.
     *
     * @param string $paymentIntentId
     * @return PaymentIntent|null
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): ?PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Confirm a PaymentIntent (if needed).
     *
     * @param string $paymentIntentId
     * @return PaymentIntent|null
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentIntentId): ?PaymentIntent
    {
        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);
        if ($paymentIntent && $paymentIntent->status === 'requires_confirmation') {
            return $paymentIntent->confirm();
        }
        return $paymentIntent;
    }

    /**
     * Handle Stripe webhook events.
     *
     * @param string $payload
     * @param string $sigHeader
     * @param string $endpointSecret
     * @return object|null
     * @throws \Exception
     */
    public function handleWebhook(string $payload, string $sigHeader, string $endpointSecret)
    {
        try {
            return \Stripe\Webhook::constructEvent(
                $payload, 
                $sigHeader, 
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            throw new \Exception('Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            throw new \Exception('Invalid signature');
        }
    }
}
