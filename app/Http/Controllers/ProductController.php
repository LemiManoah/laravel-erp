<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class ProductController implements HasMiddleware
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
        return view('products.index');
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create([
            'product_category_id' => $request->validated('product_category_id'),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'base_price' => $request->validated('base_price'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return to_route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return to_route('products.edit', $product);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update([
            'product_category_id' => $request->validated('product_category_id'),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'base_price' => $request->validated('base_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return to_route('products.index')->with('success', 'Product deleted successfully.');
    }
}
