<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class ProductCategoryController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.create', only: ['create', 'store']),
            new Middleware('permission:products.update', only: ['edit', 'update', 'destroy']),
        ];
    }

    public function index(): View
    {
        return view('product-categories.index');
    }

    public function create(): View
    {
        return view('product-categories.create');
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        ProductCategory::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return to_route('product-categories.index')->with('success', 'Product Category created successfully.');
    }

    public function show(ProductCategory $productCategory)
    {
        return to_route('product-categories.edit', $productCategory);
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $productCategory->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('product-categories.index')->with('success', 'Product Category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Cannot delete this category because it is currently assigned to one or more products. Consider marking it as inactive instead.');
        }

        $productCategory->delete();

        return to_route('product-categories.index')->with('success', 'Product Category deleted successfully.');
    }
}
