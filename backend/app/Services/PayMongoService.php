<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PayMongoService
{
    private string $baseUrl = 'https://api.paymongo.com/v1';

    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
    }

    public function createPaymentIntent(Order $order): array
    {
        $response = $this->post('/payment_intents', [
            'data' => [
                'attributes' => [
                    'amount' => (int) ($order->total * 100),
                    'currency' => 'PHP',
                    'payment_method_allowed' => ['card', 'gcash', 'paymaya'],
                    'description' => "Order {$order->order_number}",
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ]);

        $data = $response->json('data');
        $order->update(['paymongo_payment_intent_id' => $data['id']]);

        return $data;
    }

    public function createPaymentMethod(string $type, array $billingDetails): array
    {
        $response = $this->post('/payment_methods', [
            'data' => [
                'attributes' => [
                    'type' => $type,
                    'billing' => $billingDetails,
                ],
            ],
        ]);

        return $response->json('data');
    }

    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId, string $returnUrl): array
    {
        $response = $this->post("/payment_intents/{$paymentIntentId}/attach", [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'return_url' => $returnUrl,
                ],
            ],
        ]);

        return $response->json('data');
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['data']['attributes']['type'] ?? null;
        $paymentIntent = $payload['data']['attributes']['data'] ?? null;

        if ($event === 'payment.paid' && $paymentIntent) {
            $orderId = $paymentIntent['attributes']['metadata']['order_id'] ?? null;
            if ($orderId) {
                Order::find($orderId)?->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                ]);
            }
        }
    }

    private function post(string $endpoint, array $data): Response
    {
        return Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl.$endpoint, $data)
            ->throw();
    }
}
