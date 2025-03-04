<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Berhasil - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
            {{-- Success Icon --}}
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            {{-- Success Message --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h1>
            <p class="text-gray-600 mb-6">Terima kasih telah melakukan pembayaran. Booking Anda telah dikonfirmasi.</p>

            {{-- Booking Details --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-medium text-gray-900 mb-2">Detail Booking</h3>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600">Order ID: <span class="font-medium text-gray-900">{{ request('order_id') }}</span></p>
                    <p class="text-gray-600">Status: <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Terkonfirmasi</span></p>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="space-y-3">
                <a href="{{ route('filament.panel.resources.bookings.index') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Lihat Daftar Booking
                </a>
                <a href="{{ route('filament.panel.pages.dashboard') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
