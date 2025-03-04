<?php

namespace App\Policies;

use App\Models\CrewAssignment;
use App\Models\User;

class CrewAssignmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return match ($user->getRoleAttribute()) {
            'admin' => true,
            'crew' => true,
            'customer' => true, // Customer can view list but will be filtered
            default => false,
        };
    }

    public function view(User $user, CrewAssignment $crewAssignment): bool
    {
        return match ($user->getRoleAttribute()) {
            'admin' => true,
            'crew' => $crewAssignment->crew_id === $user->id,
            'customer' => $this->checkCustomerBookingAccess($user, $crewAssignment),
            default => false,
        };
    }

    protected function checkCustomerBookingAccess(User $user, CrewAssignment $crewAssignment): bool
    {
        // Load booking relationship if not loaded
        if (!$crewAssignment->relationLoaded('booking')) {
            $crewAssignment->load('booking');
        }

        // Check if crew assignment belongs to customer's booking
        return $crewAssignment->booking &&
            $crewAssignment->booking->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function update(User $user, CrewAssignment $crewAssignment): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function delete(User $user, CrewAssignment $crewAssignment): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->getRoleAttribute() === 'admin';
    }
}
