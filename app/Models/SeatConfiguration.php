<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'seat_type', // enum: standard, legrest
        'number_of_seats',
        'price_per_seat',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }
}
