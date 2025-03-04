<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentNotificationController extends Controller
{
    public function handle(Request $request)
    {
        $notification = $request->all();

        // Extract order ID
        $orderId = $notification['order_id'];
        $bookingId = str_replace('BOOKING-', '', $orderId);

        // Find booking
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Handle notification
        $booking->handlePaymentNotification($notification);

        return response()->json(['message' => 'Notification handled']);
    }
}
