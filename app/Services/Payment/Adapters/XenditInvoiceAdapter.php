<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditInvoiceAdapter implements PaymentGatewayInterface
{
    /**
     * Buat Invoice Xendit
     */
    public function createTransaction(array $params, array $credentials): array
    {
        $secretKey = $credentials['server_key'] ?? ''; // Xendit Secret API Key
        $baseUrl = 'https://api.xendit.co/v2/invoices';

        $payload = [
            'external_id'      => $params['order_id'],
            'amount'           => (int) round($params['amount']),
            'description'      => $params['item_name'] ?? 'Pembayaran',
            'customer'         => [
                'given_names'   => $params['customer_name'] ?? 'Pelanggan',
                'email'         => $params['customer_email'] ?? 'customer@example.com',
                'mobile_number' => $params['customer_phone'] ?? '08123456789',
            ],
            'invoice_duration' => 86400, // 24 jam
        ];

        // Mode Mock / Simulasi Testing Lokal jika kredensial masih default dummy
        if (str_contains($secretKey, 'demoKey') || empty($secretKey)) {
            Log::info('Xendit: Using Simulation Invoice (Demo / Dummy Key detected)', ['order_id' => $params['order_id']]);
            return [
                'success'      => true,
                'invoice_id'   => 'mock-xendit-' . md5($params['order_id'] . time()),
                'redirect_url' => 'https://checkout-staging.xendit.co/web/' . md5($params['order_id']),
                'is_mock'      => true,
                'raw'          => ['status' => 'simulated'],
            ];
        }

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'      => true,
                    'invoice_id'   => $data['id'] ?? null,
                    'redirect_url' => $data['invoice_url'] ?? null,
                    'raw'          => $data,
                ];
            }

            Log::error('Xendit Invoice Error', ['status' => $response->status(), 'body' => $response->body()]);
            return [
                'success' => false,
                'error'   => $response->json('message') ?? 'Gagal membuat tagihan Xendit.',
            ];
        } catch (\Throwable $e) {
            Log::error('Xendit Exception', ['msg' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Validasi Header X-Callback-Token
     */
    public function verifyWebhookSignature(array $payload, string $secretKey, array $headers = []): bool
    {
        $incomingToken = $headers['x-callback-token'][0] ?? $headers['x-callback-token'] ?? '';
        return hash_equals($secretKey, $incomingToken);
    }

    /**
     * Terjemahkan status Xendit Invoice
     */
    public function parseWebhookStatus(array $payload): string
    {
        $status = strtoupper($payload['status'] ?? '');

        if ($status === 'PAID' || $status === 'SETTLED') {
            return 'success';
        }

        if ($status === 'EXPIRED') {
            return 'expired';
        }

        if ($status === 'PENDING') {
            return 'pending';
        }

        return 'pending';
    }
}
