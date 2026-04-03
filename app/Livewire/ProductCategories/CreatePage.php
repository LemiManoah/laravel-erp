<?php

declare(strict_types=1);

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('products.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('product_categories', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): mixed
    {
        abort_unless(auth()->user()?->can('products.create'), 403);

        $this->validate();

        ProductCategory::query()->create([
            'name' => trim($this->name),
            'description' => $this->description === '' ? null : trim($this->description),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Item category created successfully.');

        return $this->redirectRoute('product-categories.index');
    }

    public function render(): View
    {
        return view('livewire.product_categories.create-page');
    }
}
