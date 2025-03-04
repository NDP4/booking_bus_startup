<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function checkout(Booking $booking)
    {
        // Check if user is authorized using the raw customer_id
        if (
            !Auth::check() ||
            (Auth::user()->role !== 'admin' &&
                Auth::id() !== $booking->getAttribute('customer_id'))
        ) {
            abort(403, 'Unauthorized access');
        }

        // Load relationships for the view after authorization
        $booking->load(['customer', 'bus']);

        if (empty($booking->snap_token)) {
            try {
                $booking->createMidtransPayment();
                $booking->refresh();
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
            }
        }

        return view('payment.checkout', compact('booking'));
    }

    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        $status = $request->get('status');

        // Extract booking ID from order_id (BOOKING-{id})
        $bookingId = str_replace('BOOKING-', '', $orderId);
        $booking = Booking::find($bookingId);

        if ($booking && $status === 'settlement') {
            $booking->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);
        }

        // Redirect ke halaman booking dengan notifikasi
        return redirect()->route('filament.panel.resources.bookings.index')
            ->with('success', 'Pembayaran berhasil dikonfirmasi');
    }

    public function pending(Request $request)
    {
        return redirect()->route('filament.panel.resources.bookings.index')
            ->with('info', 'Menunggu pembayaran');
    }

    public function error()
    {
        return redirect()->route('filament.panel.resources.bookings.index')
            ->with('error', 'Pembayaran gagal');
    }

    public function cancelled()
    {
        return redirect()->route('filament.panel.resources.bookings.index')
            ->with('info', 'Pembayaran dibatalkan');
    }
}
