<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number_plate',
        'description',
        'default_seat_capacity',
        'status', // enum: available, maintenance, booked
        'images',
        'pricing_type', // daily atau distance
        'price_per_day', // harga per hari
        'price_per_km', // harga per kilometer
        'legrest_price_per_seat',
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_day' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'legrest_price_per_seat' => 'decimal:2',
    ];

    public function seatConfigurations(): HasMany
    {
        return $this->hasMany(SeatConfiguration::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function calculateTotalPrice(int $totalSeats, string $seatType, int $days = 1, float $distance = 0): float
    {
        $basePrice = match ($this->pricing_type) {
            'daily' => $this->price_per_day * $days,
            'distance' => $this->price_per_km * $distance,
            default => 0,
        };

        $seatPrice = match ($seatType) {
            'legrest' => $this->legrest_price_per_seat * $totalSeats,
            default => 0,
        };

        return $basePrice + $seatPrice;
    }
}
