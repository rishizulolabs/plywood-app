<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DistributorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $distributors = DistributorProfile::query()
            ->with(['user', 'offeredProducts.category'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $term = strtolower($search);

                $query->where(function ($builder) use ($search, $term) {
                    $builder->where('business_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });

                    if ($term === 'approved' || $term === 'approve') {
                        $builder->orWhere('is_approved', true);
                    }

                    if (
                        in_array($term, ['not approved', 'not_approved', 'not-approved', 'pending', 'unapproved'], true)
                        || (str_contains($term, 'not') && str_contains($term, 'approv'))
                    ) {
                        $builder->orWhere('is_approved', false);
                    }
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.distributors.index', [
            'distributors' => $distributors,
            'filters' => $filters,
            'products' => Product::query()->with('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDistributor($request, creating: true);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['location'],
            'password' => Hash::make('admin@123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('distributor');

        $distributor = DistributorProfile::create([
            'user_id' => $user->id,
            'business_name' => $validated['name'],
            'service_cities' => [$validated['location']],
            'is_approved' => $validated['status'] === 'approved',
        ]);

        $this->syncOfferedProducts($distributor, $request);

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distributor added successfully.');
    }

    public function update(Request $request, DistributorProfile $distributor): RedirectResponse
    {
        $validated = $this->validateDistributor($request);

        $user = $distributor->user;
        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'city' => $validated['location'],
            ]);
        }

        $distributor->update([
            'business_name' => $validated['name'],
            'service_cities' => [$validated['location']],
            'is_approved' => $validated['status'] === 'approved',
        ]);

        $this->syncOfferedProducts($distributor, $request);

        return redirect()
            ->route('admin.distributors.index', $request->only('search'))
            ->with('success', 'Distributor updated successfully.');
    }

    public function updateStatus(Request $request, DistributorProfile $distributor): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,not_approved'],
        ]);

        $distributor->update([
            'is_approved' => $validated['status'] === 'approved',
        ]);

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distributor status updated.');
    }

    public function destroy(DistributorProfile $distributor): RedirectResponse
    {
        if ($distributor->image_path) {
            Storage::disk('public')->delete($distributor->image_path);
        }

        $user = $distributor->user;
        if ($user) {
            $user->delete();
        } else {
            $distributor->delete();
        }

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distributor deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDistributor(Request $request, bool $creating = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:approved,not_approved'],
            'location' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'products' => ['nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($creating) {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->input('products', []) as $productId) {
                $price = $request->input("prices.{$productId}");

                if ($price === null || $price === '') {
                    $validator->errors()->add(
                        "prices.{$productId}",
                        'Enter a price for each selected product.'
                    );
                }
            }
        });

        return $validator->validate();
    }

    private function syncOfferedProducts(DistributorProfile $distributor, Request $request): void
    {
        $productIds = $request->input('products', []);
        $prices = $request->input('prices', []);
        $sync = [];

        foreach ($productIds as $productId) {
            $productId = (int) $productId;

            if (! isset($prices[$productId]) || $prices[$productId] === '') {
                continue;
            }

            $sync[$productId] = ['price' => $prices[$productId]];
        }

        $distributor->offeredProducts()->sync($sync);
    }
}
