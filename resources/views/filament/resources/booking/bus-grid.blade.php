<div x-data="{ selectedBusId: null }" class="space-y-6">
    {{-- Search & Filter Section --}}
    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Pilih Bus yang Tersedia</h2>
        <span class="text-sm text-gray-500">{{ \App\Models\Bus::where('status', 'available')->count() }} bus tersedia</span>
    </div>

    {{-- Grid Container --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach(\App\Models\Bus::where('status', 'available')->get() as $bus)
        <div class="group">
            {{-- Card Container with consistent height --}}
            <div class="relative h-full bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden border-2"
                 :class="{ 'border-primary-500': selectedBusId === '{{ $bus->id }}', 'border-transparent': selectedBusId !== '{{ $bus->id }}' }">

                {{-- Image Container with Fixed Aspect Ratio --}}
                <div class="relative aspect-video w-full overflow-hidden bg-gray-100">
                    @if($bus->images && count($bus->images) > 0)
                        <img src="{{ Storage::url($bus->images[0]) }}"
                             alt="{{ $bus->name }}"
                             class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-300"
                             onerror="this.src='{{ asset('images/bus-placeholder.jpg') }}'">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-50">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            </svg>
                        </div>
                    @endif

                    {{-- Status & Price Badges --}}
                    <div class="absolute top-2 left-2 right-2 flex justify-between items-start">
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            Tersedia
                        </span>
                        <span class="px-2 py-1 text-xs font-medium {{ $bus->pricing_type === 'daily' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }} rounded-full">
                            @if($bus->pricing_type === 'daily')
                                Rp {{ number_format($bus->price_per_day) }}/hari
                            @else
                                Rp {{ number_format($bus->price_per_km) }}/km
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Content Section --}}
                <div class="p-4 space-y-4">
                    {{-- Bus Info --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $bus->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $bus->number_plate }}</p>
                    </div>

                    {{-- Features Grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            {{ $bus->default_seat_capacity }} Kursi
                        </div>
                        @if($bus->legrest_price_per_seat > 0)
                            <div class="flex items-center text-sm text-yellow-700">
                                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                                Legrest +{{ number_format($bus->legrest_price_per_seat) }}
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $bus->description }}</p>

                    {{-- Select Button --}}
                    <button type="button"
                            @click="selectedBusId = '{{ $bus->id }}'; $wire.set('data.bus_id', '{{ $bus->id }}')"
                            class="w-full mt-2 py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2"
                            :class="{
                                'bg-primary-600 hover:bg-primary-700 text-white ring-2 ring-primary-600 ring-offset-2': selectedBusId === '{{ $bus->id }}',
                                'bg-gray-100 hover:bg-gray-200 text-gray-700': selectedBusId !== '{{ $bus->id }}'
                            }">
                        <span x-show="selectedBusId === '{{ $bus->id }}'" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </span>
                        <span x-text="selectedBusId === '{{ $bus->id }}' ? 'Bus Terpilih' : 'Pilih Bus Ini'"></span>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
