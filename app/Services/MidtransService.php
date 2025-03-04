<?php

namespace App\Services;

use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction(Booking $booking)
    {
        // Ensure relationships are loaded
        $booking->loadMissing(['customer', 'bus']);

        // Get related data safely
        $customer = $booking->getRelation('customer');
        $bus = $booking->getRelation('bus');

        $params = [
            'transaction_details' => [
                'order_id' => 'BOOKING-' . $booking->getKey(),
                'gross_amount' => (int) $booking->getAttribute('total_amount'),
            ],
            'customer_details' => [
                'first_name' => $customer?->getAttribute('name') ?? 'Unknown',
                'email' => $customer?->getAttribute('email') ?? 'unknown@example.com',
                'phone' => $customer?->getAttribute('phone') ?? '-',
            ],
            'item_details' => [
                [
                    'id' => $booking->getAttribute('bus_id'),
                    'price' => (int) $booking->getAttribute('total_amount'),
                    'quantity' => 1,
                    'name' => "Bus Booking - " . ($bus?->getAttribute('name') ?? 'Unknown Bus'),
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return [
                'success' => true,
                'token' => $snapToken,
                'message' => 'Success generate snap token',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
