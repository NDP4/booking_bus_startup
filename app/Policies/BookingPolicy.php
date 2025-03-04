<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->getRoleAttribute(), ['admin', 'customer']);
    }

    public function view(User $user, Booking $booking): bool
    {
        return match ($user->getRoleAttribute()) {
            'admin' => true,
            'customer' => $booking->customer_id === $user->id,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return in_array($user->getRoleAttribute(), ['admin', 'customer']);
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->getRoleAttribute() === 'admin') return true;

        return $user->getRoleAttribute() === 'customer' &&
            $booking->customer_id === $user->id &&
            $booking->status === 'pending';
    }

    public function delete(User $user, Booking $booking): bool
    {
        if ($user->getRoleAttribute() === 'admin') return true;

        return $user->getRoleAttribute() === 'customer' &&
            $booking->customer_id === $user->id &&
            $booking->status === 'pending';
    }

    public function deleteAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }
}
