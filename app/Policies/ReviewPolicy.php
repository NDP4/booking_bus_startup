<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->getRoleAttribute(), ['admin', 'customer']);
    }

    public function view(User $user, Review $review): bool
    {
        return match ($user->getRoleAttribute()) {
            'admin' => true,
            'customer' => $review->customer_id === $user->id,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return in_array($user->getRoleAttribute(), ['admin', 'customer']);
    }

    public function update(User $user, Review $review): bool
    {
        if ($user->getRoleAttribute() === 'admin') return true;

        return $user->getRoleAttribute() === 'customer' &&
            $review->customer_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->getRoleAttribute() === 'admin') return true;

        return $user->getRoleAttribute() === 'customer' &&
            $review->customer_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }
}
