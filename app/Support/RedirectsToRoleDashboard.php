<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

trait RedirectsToRoleDashboard
{
    protected function redirectToRoleDashboard(User $user): RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->hasRole('distributor')) {
            return redirect()->intended(route('distributor.dashboard'));
        }

        return redirect()->intended(route('customer.dashboard'));
    }
}
