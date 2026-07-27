<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class DokuCheckout
{
    public function notificationIsValid(string $rawBody, array $headers, string $target): bool
    {
        $clientId = $headers['client-id'][0] ?? '';
        $requestId = $headers['request-id'][0] ?? '';
        $timestamp = $headers['request-timestamp'][0] ?? '';
        $signature = $headers['signature'][0] ?? '';
        $secret = config('services.doku.secret_key');
        if (blank($clientId) || blank($requestId) || blank($timestamp) || blank($signature) || blank($secret)) return false;
        $digest = base64_encode(hash('sha256', $rawBody, true));
        $component = "Client-Id:$clientId\nRequest-Id:$requestId\nRequest-Timestamp:$timestamp\nRequest-Target:$target\nDigest:$digest";
        $expected = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $component, $secret, true));
        return hash_equals($expected, $signature);
    }

    public function create(Order $order): array
    {
        $clientId = config('services.doku.client_id');
        $secret = config('services.doku.secret_key');
        if (blank($clientId) || blank($secret)) {
            throw new RuntimeException('Konfigurasi DOKU sandbox belum lengkap.');
        }

        $requestId = (string) Str::uuid();
        $timestamp = now('UTC')->format('Y-m-d\\TH:i:s\\Z');
        $body = [
            'order' => [
                'amount' => $order->total_amount,
                'invoice_number' => $order->number,
                'currency' => 'IDR',
                'callback_url' => route('orders.show', $order),
                'callback_url_result' => route('orders.show', $order),
                'auto_redirect' => true,
            ],
            'payment' => ['payment_due_date' => config('services.doku.payment_due_date')],
            'customer' => ['name' => $order->customer_name, 'email' => $order->customer_email],
        ];
        $json = json_encode($body, JSON_UNESCAPED_SLASHES);
        $digest = base64_encode(hash('sha256', $json, true));
        $target = '/checkout/v1/payment';
        $component = "Client-Id:$clientId\nRequest-Id:$requestId\nRequest-Timestamp:$timestamp\nRequest-Target:$target\nDigest:$digest";
        $signature = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $component, $secret, true));
        $baseUrl = config('services.doku.sandbox') ? 'https://api-sandbox.doku.com' : 'https://api.doku.com';
        $response = Http::withBody($json, 'application/json')->withHeaders([
            'Client-Id' => $clientId, 'Request-Id' => $requestId, 'Request-Timestamp' => $timestamp, 'Signature' => $signature,
        ])->post($baseUrl.$target);
        if ($response->failed()) throw new RuntimeException('DOKU Checkout gagal dibuat.');
        return ['request_id' => $requestId, 'response' => $response->json(), 'url' => data_get($response->json(), 'response.payment.url')];
    }
}
