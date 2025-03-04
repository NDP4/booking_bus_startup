<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout Pembayaran - {{ config('app.name') }}</title>

    {{-- Load Midtrans JS SDK --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}">
    </script>

    {{-- Load TailwindCSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">Checkout Pembayaran</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Detail Booking</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Bus</p>
                    <p class="font-medium">{{ $booking->bus->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Tanggal Booking</p>
                    <p class="font-medium">{{ $booking->booking_date->format('d M Y H:i') }}</p>
                </div>
                @if($booking->return_date)
                <div>
                    <p class="text-gray-600">Tanggal Kembali</p>
                    <p class="font-medium">{{ $booking->return_date->format('d M Y H:i') }}</p>
                </div>
                @endif
                <div>
                    <p class="text-gray-600">Jumlah Kursi</p>
                    <p class="font-medium">{{ $booking->total_seats }} ({{ $booking->seat_type }})</p>
                </div>
                <div>
                    <p class="text-gray-600">Total Pembayaran</p>
                    <p class="text-xl font-bold text-primary-600">Rp {{ number_format($booking->total_amount) }}</p>
                </div>
            </div>
        </div>

        <button id="pay-button" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg hover:bg-primary-700 font-medium">
            Bayar Sekarang
        </button>
    </div>

    <script type="text/javascript">
        // Tampilkan popup pembayaran saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const snapToken = '{{ $booking->snap_token }}';

            if (!snapToken) {
                alert('Token pembayaran tidak valid. Silakan coba lagi.');
                window.close();
                return;
            }

            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    window.location.href = '{{ route("payment.success") }}' +
                        '?order_id=' + result.order_id +
                        '&status=' + result.transaction_status;
                },
                onPending: function(result) {
                    window.location.href = '{{ route("payment.pending") }}' +
                        '?order_id=' + result.order_id;
                },
                onError: function(result) {
                    window.location.href = '{{ route("payment.error") }}';
                },
                onClose: function() {
                    window.location.href = '{{ route("filament.panel.resources.bookings.index") }}';
                }
            });
        });

        // Manual trigger melalui tombol
        document.getElementById('pay-button').addEventListener('click', function() {
            const snapToken = '{{ $booking->snap_token }}';
            window.snap.pay(snapToken);
        });
    </script>
</body>
</html>
