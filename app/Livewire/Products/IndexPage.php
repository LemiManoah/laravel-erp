<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $category = '';

    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $deletingProductId = null;

    public string $deletingProductName = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('products.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->category = '';
        $this->resetPage();
    }

    public function confirmDelete(int $productId): void
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $product = Product::query()->findOrFail($productId);

        $this->deletingProductId = $product->id;
        $this->deletingProductName = $product->name;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingProductId = null;
        $this->deletingProductName = '';
    }

    public function deleteProduct(): void
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $product = Product::query()->findOrFail($this->deletingProductId);
        $product->delete();

        $this->cancelDelete();
        session()->flash('success', 'Product deleted successfully.');
    }

    public function render(): View
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['baseUnit', 'category', 'defaultPrice', 'inventoryStocks'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($productQuery): void {
                    $search = trim($this->search);

                    $productQuery->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('description', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('sku', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('barcode', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->when($this->status !== '', function ($query): void {
                $query->where('is_active', $this->status === 'active');
            })
            ->when($this->category !== '', function ($query): void {
                $query->where('product_category_id', $this->category);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.products.index-page', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
