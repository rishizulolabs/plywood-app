<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $users = User::query()
            ->with('roles')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,customer,distributor'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'password' => Hash::make('admin@123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($validated['role']);

        if ($validated['role'] === 'distributor') {
            DistributorProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['name'],
                'service_cities' => [$validated['city']],
                'is_approved' => false,
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User added successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,customer,distributor'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
        ]);

        $previousRole = $user->roles->first()?->name;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
        ]);
        $user->syncRoles([$validated['role']]);

        if ($previousRole === 'distributor' && $validated['role'] !== 'distributor') {
            $user->distributorProfile?->delete();
        }

        if ($validated['role'] === 'distributor' && ! $user->distributorProfile) {
            DistributorProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['name'],
                'service_cities' => [$validated['city']],
                'is_approved' => false,
            ]);
        } elseif ($validated['role'] === 'distributor' && $user->distributorProfile) {
            $user->distributorProfile->update([
                'business_name' => $validated['name'],
                'service_cities' => [$validated['city']],
            ]);
        }

        return redirect()
            ->route('admin.users.index', $request->only('search'))
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('admin.users.index', $request->only('search'))
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index', $request->only('search'))
            ->with('success', 'User deleted successfully.');
    }
}
