<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout Pembayaran - {{ config('app.name') }}</title>
    <script type="text/javascript"
            src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            '600': '#F59E0B',
                            '700': '#D97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Style untuk container payment frame */
        #snap-container {
            width: 100%;
            height: 100%;
            min-height: 700px;
        }

        /* Hide scrollbar untuk container utama */
        .payment-frame-container {
            overflow: hidden;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        {{-- Header with Back Button --}}
        <header class="bg-white shadow-sm mb-6">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Checkout Pembayaran</h1>
                <a href="{{ route('filament.panel.resources.bookings.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200
                          text-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Tunda Pembayaran
                </a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-8">
                {{-- Order Summary --}}
                <div class="space-y-6">
                    {{-- Bus Details Card --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold mb-4">Detail Bus</h2>
                            <div class="flex gap-4">
                                @if($booking->bus->images && count($booking->bus->images) > 0)
                                    <img src="{{ Storage::url($booking->bus->images[0]) }}"
                                         alt="{{ $booking->bus->name }}"
                                         class="w-24 h-24 rounded-lg object-cover">
                                @endif
                                <div>
                                    <h3 class="font-medium">{{ $booking->bus->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $booking->bus->number_plate }}</p>
                                    <p class="mt-2 text-sm">{{ $booking->total_seats }} Kursi ({{ $booking->seat_type }})</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Travel Details Card --}}
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold mb-4">Detail Perjalanan</h2>
                            <div class="grid gap-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-600">Tanggal Berangkat</label>
                                        <p class="font-medium">{{ $booking->booking_date->format('d M Y H:i') }}</p>
                                    </div>
                                    @if($booking->return_date)
                                        <div>
                                            <label class="text-sm text-gray-600">Tanggal Kembali</label>
                                            <p class="font-medium">{{ $booking->return_date->format('d M Y H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Lokasi Jemput</label>
                                    <p class="font-medium">{{ $booking->pickup_location }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-gray-600">Tujuan</label>
                                    <p class="font-medium">{{ $booking->destination }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Amount Card --}}
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold mb-4">Ringkasan Pembayaran</h2>
                            <div class="space-y-3 pb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total</span>
                                    <span class="text-xl font-bold text-primary-600">
                                        Rp {{ number_format($booking->total_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Frame --}}
                <div class="payment-frame-container bg-white rounded-xl shadow-sm">
                    <div id="snap-container"></div>
                </div>
            </div>

            {{-- Add Back Button at the Bottom for Mobile --}}
            <div class="mt-6 lg:hidden">
                <a href="{{ route('filament.panel.resources.bookings.index') }}"
                   class="block w-full text-center px-4 py-3 bg-gray-100 hover:bg-gray-200
                          text-gray-700 rounded-lg transition-colors">
                    Tunda Pembayaran & Kembali ke Daftar Booking
                </a>
            </div>
        </main>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const snapToken = '{{ $booking->snap_token }}';

            if (!snapToken) {
                alert('Token pembayaran tidak valid. Silakan coba lagi.');
                window.location.href = '{{ route("filament.panel.resources.bookings.index") }}';
                return;
            }

            // Initialize embedded snap
            window.snap.embed(snapToken, {
                embedId: 'snap-container',
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
                    console.error('Payment Error:', result);
                    window.location.href = '{{ route("payment.error") }}';
                },
                onClose: function() {
                    window.location.href = '{{ route("filament.panel.resources.bookings.index") }}';
                }
            });
        });
    </script>
</body>
</html>
