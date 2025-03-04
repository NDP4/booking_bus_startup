<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Models\Booking' => 'App\Policies\BookingPolicy',
        'App\Models\CrewAssignment' => 'App\Policies\CrewAssignmentPolicy',
        'App\Models\Review' => 'App\Policies\ReviewPolicy',
        'App\Models\Bus' => 'App\Policies\BusPolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Define global gates here if needed
    }
}
