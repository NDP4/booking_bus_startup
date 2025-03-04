<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

trait HasMidtransPayment
{
    public function createMidtransPayment(): bool
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Check if snap token is still valid (less than 24 hours old)
        if ($this->snap_token && $this->snap_token_created_at?->diffInHours(now()) < 24) {
            return true;
        }

        // Load relationships
        $this->load(['customer', 'bus']);

        $params = [
            'transaction_details' => [
                'order_id' => 'BOOKING-' . $this->id,
                'gross_amount' => (int) $this->total_amount,
            ],
            'customer_details' => [
                'first_name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ],
            'item_details' => [
                [
                    'id' => $this->bus_id,
                    'price' => (int) $this->total_amount,
                    'quantity' => 1,
                    'name' => "Bus Booking - {$this->bus->name}",
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $this->update([
                'snap_token' => $snapToken,
                'snap_token_created_at' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }

    public function handlePaymentNotification(array $notification): void
    {
        $transactionStatus = $notification['transaction_status'];
        $paymentType = $notification['payment_type'];
        $orderId = $notification['order_id'];

        // Update booking status based on payment status
        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $this->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
                break;

            case 'pending':
                $this->update([
                    'payment_status' => 'pending',
                    'status' => 'pending'
                ]);
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                $this->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);
                break;
        }
    }
}
