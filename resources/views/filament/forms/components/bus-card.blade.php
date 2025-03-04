@php
    $state = $getState();
    $bus = \App\Models\Bus::find($state);
@endphp

<div class="grid gap-4">
    @if($bus)
        <div class="rounded-xl border border-gray-200 p-4 shadow-sm">
            @if($bus->images && count($bus->images) > 0)
                <img
                    src="{{ Storage::url($bus->images[0]) }}"
                    alt="{{ $bus->name }}"
                    class="h-48 w-full rounded-lg object-cover"
                >
            @endif

            <div class="mt-4">
                <h3 class="text-lg font-semibold">{{ $bus->name }}</h3>
                <p class="text-sm text-gray-500">{{ $bus->number_plate }}</p>

                <div class="mt-2 space-y-2">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-user-group class="h-5 w-5 text-gray-400"/>
                        <span>{{ $bus->default_seat_capacity }} Kursi</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-heroicon-s-currency-dollar class="h-5 w-5 text-gray-400"/>
                        <span>
                            @if($bus->pricing_type === 'daily')
                                Rp {{ number_format($bus->price_per_day) }}/hari
                            @else
                                Rp {{ number_format($bus->price_per_km) }}/km
                            @endif
                        </span>
                    </div>

                    @if($bus->legrest_price_per_seat > 0)
                        <div class="flex items-center gap-2">
                            <x-heroicon-s-star class="h-5 w-5 text-yellow-400"/>
                            <span>Legrest +Rp {{ number_format($bus->legrest_price_per_seat) }}/kursi</span>
                        </div>
                    @endif
                </div>

                <p class="mt-2 text-sm text-gray-600">{{ $bus->description }}</p>
            </div>
        </div>
    @endif

    {{ $getChildComponentContainer() }}
</div>
