<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CrewAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'crew_id',
        'status',
        'notes',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('access', function (Builder $query) {
            if (!Auth::check()) return;

            $user = Auth::user();

            switch ($user->role) {
                case 'admin':
                    // Admin can see all
                    break;

                case 'crew':
                    // Crew can only see their assignments
                    $query->where('crew_id', $user->id);
                    break;

                case 'customer':
                    // Customer can only see crews assigned to their bookings
                    $query->whereHas('booking', function ($q) use ($user) {
                        $q->where('customer_id', $user->id);
                    });
                    break;

                default:
                    // Other roles see nothing
                    $query->where('id', 0);
                    break;
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_id');
    }
}
