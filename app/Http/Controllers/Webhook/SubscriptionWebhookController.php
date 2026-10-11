<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionWebhookController extends Controller
{
    /**
     * Webhook Midtrans untuk Pelunasan Langganan SaaS Platform
     */
    public function handleMidtrans(Request $request)
    {
        $payload = $request->all();
        $adapter = PaymentGatewayManager::getAdapter('midtrans');
        $credentials = PaymentGatewayManager::getPlatformCredentials();

        // Validasi keaslian signature Midtrans
        if (!$adapter->verifyWebhookSignature($payload, $credentials['server_key'])) {
            Log::warning('Midtrans Subscription Webhook: Invalid Signature', $payload);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $orderId = $payload['order_id'] ?? '';
        $status = $adapter->parseWebhookStatus($payload);

        if ($status === 'success') {
            SubscriptionInvoice::settleInvoice($orderId, $payload);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Webhook Xendit untuk Pelunasan Langganan SaaS Platform
     */
    public function handleXendit(Request $request)
    {
        $payload = $request->all();
        $headers = $request->headers->all();
        $adapter = PaymentGatewayManager::getAdapter('xendit');
        $credentials = PaymentGatewayManager::getPlatformCredentials();

        // Validasi webhook secret Xendit
        if (!$adapter->verifyWebhookSignature($payload, $credentials['webhook_secret'], $headers)) {
            Log::warning('Xendit Subscription Webhook: Invalid Token', $payload);
            return response()->json(['message' => 'Invalid callback token'], 400);
        }

        $externalId = $payload['external_id'] ?? '';
        $status = $adapter->parseWebhookStatus($payload);

        if ($status === 'success') {
            SubscriptionInvoice::settleInvoice($externalId, $payload);
        }

        return response()->json(['status' => 'ok']);
    }
}
