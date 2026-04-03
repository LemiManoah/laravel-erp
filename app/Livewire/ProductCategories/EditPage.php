<?php

declare(strict_types=1);

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $productCategoryId;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(ProductCategory $productCategory): void
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $this->productCategoryId = $productCategory->id;
        $this->name = $productCategory->name;
        $this->description = $productCategory->description ?? '';
        $this->is_active = $productCategory->is_active;
    }

    protected function rules(): array
    {
        $productCategory = ProductCategory::query()->find($this->productCategoryId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('product_categories', 'name')->ignore($productCategory)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('products.update'), 403);

        $this->validate();

        ProductCategory::query()->findOrFail($this->productCategoryId)->update([
            'name' => trim($this->name),
            'description' => $this->description === '' ? null : trim($this->description),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Product Category updated successfully.');

        return $this->redirectRoute('product-categories.index');
    }

    public function render(): View
    {
        return view('livewire.product_categories.edit-page');
    }
}
