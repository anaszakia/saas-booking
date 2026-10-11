<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MinioService;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PaymentController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function __construct(protected MinioService $minio) {}

    public function confirm(Request $request, Payment $payment)
    {
        $userId = auth()->id() ?? session('user_id');

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = minio_upload($request->file('payment_proof'), 'payment_proofs');
            $payment->update(['payment_proof' => $proofPath]);
        }

        $success = Payment::confirmManualPayment($payment->id, $userId);

        if ($success) {
            return back()->with('success', 'Pembayaran berhasil dikonfirmasi lunas!');
        }

        return back()->with('error', 'Gagal mengonfirmasi pembayaran.');
    }
}
