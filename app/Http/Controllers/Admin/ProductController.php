<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
            ->with(['category', 'media'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('grade', 'like', "%{$search}%")
                        ->orWhere('thickness', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
            'specs' => ProductSpecs::class,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product = Product::create($this->applyDefaults($validated));
        $this->syncProductMedia($product, $request);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product->update($validated);
        $this->syncProductMedia($product, $request);

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
            'description' => ['nullable', 'string'],
            'thickness' => ['required', Rule::in(ProductSpecs::THICKNESSES)],
            'size' => ['required', Rule::in(ProductSpecs::SIZES)],
            'grade' => ['required', Rule::in(ProductSpecs::GRADES)],
            'product_image' => ['nullable', 'image', 'max:2048'],
            'thumbnails' => ['nullable', 'array'],
            'thumbnails.*' => ['image', 'max:2048'],
        ]);

        return $validated;
    }

    private function syncProductMedia(Product $product, Request $request): void
    {
        if ($request->hasFile('product_image')) {
            $product->addMediaFromRequest('product_image')
                ->toMediaCollection('product_image');
        }

        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $file) {
                if ($file) {
                    $product->addMedia($file)->toMediaCollection('thumbnails');
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyDefaults(array $validated): array
    {
        $validated['core_type'] = ProductSpecs::CORE_TYPES[0];
        $validated['number_of_plies'] = ProductSpecs::NUMBER_OF_PLIES[0];
        $validated['is_standard'] = ProductSpecs::IS_STANDARDS[0];
        $validated['is_isi_marked'] = false;
        $validated['brand'] = ProductSpecs::BRANDS[0];
        $validated['warranty'] = ProductSpecs::WARRANTIES[0];
        $validated['min_order_qty'] = 1;
        $validated['unit'] = 'sheet';
        $validated['in_stock'] = true;
        $validated['is_featured'] = false;

        return $validated;
    }
}
