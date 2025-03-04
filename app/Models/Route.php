<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_location',
        'destination',
        'distance',
        'base_price',
        'status', // enum: active, inactive
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
