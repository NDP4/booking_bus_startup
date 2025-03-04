<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran Berhasil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-2">Pembayaran Berhasil!</h1>
            <p class="text-gray-600 mb-6">Terima kasih telah melakukan pembayaran.</p>
            <a href="{{ route('filament.pages.dashboard') }}"
               class="inline-block bg-primary-600 text-white py-2 px-4 rounded hover:bg-primary-700">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>
