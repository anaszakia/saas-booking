<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Buat transaksi Snap token (Midtrans) atau Invoice URL (Xendit)
     */
    public function createTransaction(array $params, array $credentials): array;

    /**
     * Validasi keaslian signature webhook
     */
    public function verifyWebhookSignature(array $payload, string $secretKey, array $headers = []): bool;

    /**
     * Terjemahkan status callback gateway ke format sistem: success, pending, failed, expired
     */
    public function parseWebhookStatus(array $payload): string;
}
