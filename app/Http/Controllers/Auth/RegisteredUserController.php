<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\User;
use App\Support\RedirectsToRoleDashboard;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use RedirectsToRoleDashboard;

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['required', 'in:customer,distributor'],
            'business_name' => ['required_if:account_type,distributor', 'nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->account_type);

            if ($request->account_type === 'distributor') {
                DistributorProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $request->business_name ?? $request->name,
                    'is_approved' => false,
                ]);
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return $this->redirectToRoleDashboard($user);
    }
}
