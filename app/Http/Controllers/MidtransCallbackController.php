<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $notification = $request->all();
        Log::info('Midtrans notification received:', $notification);

        try {
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'];
            $bookingId = str_replace('BOOKING-', '', $orderId);

            $booking = Booking::find($bookingId);
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $booking->update([
                        'payment_status' => 'pending',
                        'status' => 'pending',
                        'updated_at' => Carbon::now(),
                    ]);
                } else if ($fraudStatus == 'accept') {
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'updated_at' => Carbon::now(),
                    ]);
                }
            } else if ($transactionStatus == 'settlement') {
                $booking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'updated_at' => Carbon::now(),
                ]);
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $booking->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                    'updated_at' => Carbon::now(),
                ]);
            } else if ($transactionStatus == 'pending') {
                $booking->update([
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'updated_at' => Carbon::now(),
                ]);
            }

            return response()->json(['message' => 'Notification processed']);
        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }
}
