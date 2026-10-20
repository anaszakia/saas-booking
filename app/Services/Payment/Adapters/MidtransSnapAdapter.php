<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransSnapAdapter implements PaymentGatewayInterface
{
    /**
     * Buat Snap Token via HTTP Client
     */
    public function createTransaction(array $params, array $credentials): array
    {
        $serverKey = $credentials['server_key'] ?? '';
        $isSandbox = $credentials['is_sandbox'] ?? true;

        $baseUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id'     => $params['order_id'],
                'gross_amount' => (int) round($params['amount']),
            ],
            'customer_details' => [
                'first_name' => $params['customer_name'] ?? 'Pelanggan',
                'email'      => $params['customer_email'] ?? 'customer@example.com',
                'phone'      => $params['customer_phone'] ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id'       => $params['item_id'] ?? $params['order_id'],
                    'price'    => (int) round($params['amount']),
                    'quantity' => 1,
                    'name'     => substr($params['item_name'] ?? 'Pembayaran', 0, 50),
                ]
            ],
        ];

        // Mode Mock / Simulasi Testing Lokal jika kredensial masih default dummy
        if (str_contains($serverKey, 'demoKey') || empty($serverKey)) {
            Log::info('Midtrans: Using Sandbox Simulation Token (Demo / Dummy Key detected)', ['order_id' => $params['order_id']]);
            return [
                'success'      => true,
                'snap_token'   => 'mock-snap-' . md5($params['order_id'] . time()),
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-token',
                'is_mock'      => true,
                'raw'          => ['token' => 'mock-token', 'status' => 'simulated'],
            ];
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'      => true,
                    'snap_token'   => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                    'raw'          => $data,
                ];
            }

            Log::error('Midtrans Snap Error', ['status' => $response->status(), 'body' => $response->body()]);
            
            $errorMessage = $response->json('error_messages')[0] 
                ?? $response->json('error') 
                ?? ('Midtrans API Error (' . $response->status() . ')');

            if ($response->status() === 401) {
                $errorMessage = 'Server Key Midtrans tidak valid atau belum diotorisasi. Silakan periksa kredensial di Pengaturan Gateway Super Admin.';
            }

            return [
                'success' => false,
                'error'   => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Exception', ['msg' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Validasi Signature Hash: SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function verifyWebhookSignature(array $payload, string $secretKey, array $headers = []): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $incomingSignature = $payload['signature_key'] ?? '';

        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $secretKey);

        return hash_equals($calculatedSignature, $incomingSignature);
    }

    /**
     * Terjemahkan transaction_status Midtrans
     */
    public function parseWebhookStatus(array $payload): string
    {
        $status = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? '';

        if ($status === 'capture') {
            return ($fraud === 'challenge') ? 'pending' : 'success';
        }

        if ($status === 'settlement') {
            return 'success';
        }

        if (in_array($status, ['cancel', 'deny', 'expire'])) {
            return 'failed';
        }

        if ($status === 'pending') {
            return 'pending';
        }

        return 'pending';
    }
}
