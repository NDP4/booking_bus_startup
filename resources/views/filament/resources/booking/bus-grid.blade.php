@php
    $buses = \App\Models\Bus::where('status', 'available')->get();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($buses as $bus)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
            @if($bus->images && count($bus->images) > 0)
                <img src="{{ Storage::url($bus->images[0]) }}"
                     alt="{{ $bus->name }}"
                     class="h-48 w-full object-cover"
                >
            @endif

            <div class="p-4">
                <h3 class="text-lg font-semibold">{{ $bus->name }}</h3>
                <p class="text-sm text-gray-500">{{ $bus->number_plate }}</p>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Kapasitas</span>
                        <span class="font-medium">{{ $bus->default_seat_capacity }} Kursi</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Harga</span>
                        <span class="font-medium">
                            @if($bus->pricing_type === 'daily')
                                Rp {{ number_format($bus->price_per_day) }}/hari
                            @else
                                Rp {{ number_format($bus->price_per_km) }}/km
                            @endif
                        </span>
                    </div>

                    @if($bus->legrest_price_per_seat > 0)
                        <div class="flex items-center justify-between text-yellow-600">
                            <span class="text-sm">Legrest</span>
                            <span class="font-medium">+Rp {{ number_format($bus->legrest_price_per_seat) }}/kursi</span>
                        </div>
                    @endif
                </div>

                <p class="mt-3 text-sm text-gray-600 line-clamp-2">{{ $bus->description }}</p>

                <button type="button"
                        x-on:click="$wire.set('data.bus_id', '{{ $bus->id }}')"
                        class="mt-4 w-full bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                    Pilih Bus Ini
                </button>
            </div>
        </div>
    @endforeach
</div>
