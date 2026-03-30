<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Livewire\Products\Concerns\InteractsWithProductForm;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    use InteractsWithProductForm;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('products.create'), 403);
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('products.create'), 403);

        $this->validate($this->productRules());

        Product::create($this->productPayload());

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function render(): View
    {
        return view('livewire.products.create-page', [
            'categories' => ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
