<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $categories = Category::query()
            ->withCount('products')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category added successfully.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()
            ->route('admin.categories.index', $request->only('search'))
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index', $request->only('search'))
                ->with('error', 'Cannot delete a category that has products.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index', $request->only('search'))
            ->with('success', 'Category deleted successfully.');
    }
}
