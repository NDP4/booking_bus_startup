<?php

namespace App\Policies;

use App\Models\Bus;
use App\Models\User;

class BusPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function view(User $user, Bus $bus): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function update(User $user, Bus $bus): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function delete(User $user, Bus $bus): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }
}
