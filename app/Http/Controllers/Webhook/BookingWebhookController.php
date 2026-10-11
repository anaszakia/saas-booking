<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\TenantPaymentSetting;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingWebhookController extends Controller
{
    /**
     * Webhook Midtrans untuk Pelunasan Booking Lapangan Milik Tenant
     */
    public function handleMidtrans(Request $request)
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? '';

        // Temukan payment untuk mengetahui tenant_id
        $payment = DB::table('payments')->where('payment_number', $orderId)->select('tenant_id')->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $credentials = PaymentGatewayManager::getTenantCredentials($payment->tenant_id, 'midtrans');
        if (!$credentials) {
            return response()->json(['message' => 'Tenant gateway not configured'], 400);
        }

        $adapter = PaymentGatewayManager::getAdapter('midtrans');

        // Validasi keaslian signature dengan secret key milik tenant terkait
        if (!$adapter->verifyWebhookSignature($payload, $credentials['server_key'])) {
            Log::warning('Midtrans Booking Webhook: Invalid Signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $status = $adapter->parseWebhookStatus($payload);
        Payment::processWebhookNotification($orderId, $status, $payload);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Webhook Xendit untuk Pelunasan Booking Lapangan Milik Tenant
     */
    public function handleXendit(Request $request)
    {
        $payload = $request->all();
        $externalId = $payload['external_id'] ?? '';

        $payment = DB::table('payments')->where('payment_number', $externalId)->select('tenant_id')->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $credentials = PaymentGatewayManager::getTenantCredentials($payment->tenant_id, 'xendit');
        if (!$credentials) {
            return response()->json(['message' => 'Tenant gateway not configured'], 400);
        }

        $adapter = PaymentGatewayManager::getAdapter('xendit');

        if (!$adapter->verifyWebhookSignature($payload, $credentials['webhook_secret'], $request->headers->all())) {
            Log::warning('Xendit Booking Webhook: Invalid Token', ['external_id' => $externalId]);
            return response()->json(['message' => 'Invalid callback token'], 400);
        }

        $status = $adapter->parseWebhookStatus($payload);
        Payment::processWebhookNotification($externalId, $status, $payload);

        return response()->json(['status' => 'ok']);
    }
}
