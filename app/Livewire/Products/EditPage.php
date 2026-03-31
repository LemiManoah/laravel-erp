<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Enums\ProductItemType;
use App\Livewire\Products\Concerns\InteractsWithProductForm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    use InteractsWithProductForm;

    #[Locked]
    public int $productId;

    public function mount(Product $product): void
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $this->productId = $product->id;
        $this->fillFromProduct($product);
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $this->validate($this->productRules());

        $product = Product::query()->findOrFail($this->productId);
        $product->update($this->productPayload());
        $product->defaultPrice()->updateOrCreate(
            ['tenant_id' => tenant('id'), 'product_id' => $product->id],
            $this->pricePayload(),
        );

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.products.edit-page', [
            'categories' => ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'units' => UnitOfMeasure::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'parentProducts' => Product::query()
                ->whereKeyNot($this->productId)
                ->orderBy('name')
                ->get(),
            'itemTypes' => ProductItemType::cases(),
        ]);
    }
}
