<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $notification = new Notification();

            $orderId = explode('-', $notification->order_id)[1];
            $booking = Booking::findOrFail($orderId);

            $transactionStatus = $notification->transaction_status;
            $type = $notification->payment_type;
            $fraudStatus = $notification->fraud_status;

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_id' => $notification->transaction_id,
                'amount' => $notification->gross_amount,
                'payment_type' => $type,
                'status' => $this->mapPaymentStatus($transactionStatus),
                'payment_details' => json_encode($notification),
                'paid_at' => now(),
            ]);

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $booking->payment_status = 'pending';
                } else if ($fraudStatus == 'accept') {
                    $booking->payment_status = 'paid';
                    $booking->status = 'confirmed';
                }
            } else if ($transactionStatus == 'settlement') {
                $booking->payment_status = 'paid';
                $booking->status = 'confirmed';
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $booking->payment_status = 'failed';
            } else if ($transactionStatus == 'pending') {
                $booking->payment_status = 'pending';
            }

            $booking->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment notification handled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function mapPaymentStatus($status)
    {
        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'failed',
            'cancel' => 'failed'
        ];

        return $statusMap[$status] ?? 'pending';
    }
}
