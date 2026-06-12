<?php

namespace App\Support;

use App\Models\User;

class DashboardRoute
{
    public static function forUser(User $user): string
    {
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }

        if ($user->hasRole('distributor')) {
            return route('distributor.dashboard');
        }

        return route('customer.dashboard');
    }
}
