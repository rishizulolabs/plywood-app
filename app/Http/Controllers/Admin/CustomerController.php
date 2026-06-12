<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $customers = User::role('customer')
            ->with(['assignedDistributor.user'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhereHas('assignedDistributor', function ($distributorQuery) use ($search) {
                            $distributorQuery->where('business_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $distributors = DistributorProfile::query()
            ->with('user')
            ->where('is_approved', true)
            ->orderBy('business_name')
            ->get();

        return view('admin.customers.index', compact('customers', 'distributors', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'distributor_profile_id' => ['required', 'exists:distributor_profiles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'distributor_profile_id' => $validated['distributor_profile_id'],
            'password' => Hash::make('admin@123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->hasRole('customer'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'distributor_profile_id' => ['required', 'exists:distributor_profiles,id'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'distributor_profile_id' => $validated['distributor_profile_id'],
        ]);

        return redirect()
            ->route('admin.customers.index', $request->only('search'))
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->hasRole('customer'), 404);

        $customer->delete();

        return redirect()
            ->route('admin.customers.index', $request->only('search'))
            ->with('success', 'Customer deleted successfully.');
    }
}
