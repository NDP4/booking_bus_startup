<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function view(User $user, User $model): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function update(User $user, User $model): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function delete(User $user, User $model): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }
}
