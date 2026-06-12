<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Support\ProductSpecs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $products = Product::query()
            ->with(['category', 'distributorProfile.user'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('grade', 'like', "%{$search}%")
                        ->orWhere('thickness', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('distributorProfile', function ($distributorQuery) use ($search) {
                            $distributorQuery->where('business_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
            'distributors' => DistributorProfile::with('user')->where('is_approved', true)->orderBy('business_name')->get(),
            'specs' => ProductSpecs::class,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product->update($validated);

        return redirect()
            ->route('admin.products.index', $request->only('search'))
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index', $request->only('search'))
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'distributor_profile_id' => ['required', 'exists:distributor_profiles,id'],
            'description' => ['nullable', 'string'],
            'thickness' => ['required', Rule::in(ProductSpecs::THICKNESSES)],
            'size' => ['required', Rule::in(ProductSpecs::SIZES)],
            'grade' => ['required', Rule::in(ProductSpecs::GRADES)],
            'core_type' => ['required', Rule::in(ProductSpecs::CORE_TYPES)],
            'number_of_plies' => ['required', Rule::in(ProductSpecs::NUMBER_OF_PLIES)],
            'is_standard' => ['required', Rule::in(ProductSpecs::IS_STANDARDS)],
            'is_isi_marked' => ['required', 'boolean'],
            'brand' => ['required', Rule::in(ProductSpecs::BRANDS)],
            'warranty' => ['required', Rule::in(ProductSpecs::WARRANTIES)],
            'finish_surface' => ['nullable', Rule::in(ProductSpecs::FINISH_SURFACES)],
            'density' => ['nullable', 'string', 'max:50'],
            'termite_borer_treatment' => ['nullable', 'boolean'],
            'weight_per_sheet' => ['nullable', 'string', 'max:50'],
            'application' => ['nullable', 'string', 'max:500'],
            'glue_type' => ['nullable', Rule::in(ProductSpecs::GLUE_TYPES)],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'min_order_qty' => ['required', 'integer', 'min:1'],
            'unit' => ['required', Rule::in(ProductSpecs::UNITS)],
            'in_stock' => ['required', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['is_isi_marked'] = $request->boolean('is_isi_marked');
        $validated['termite_borer_treatment'] = $request->boolean('termite_borer_treatment');
        $validated['in_stock'] = $request->boolean('in_stock');
        $validated['is_featured'] = $request->boolean('is_featured');

        return $validated;
    }
}
