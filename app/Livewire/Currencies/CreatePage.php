<?php

declare(strict_types=1);

namespace App\Livewire\Currencies;

use App\Actions\Currency\CreateCurrencyAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public string $code = '';
    public string $symbol = '';
    public string $decimal_places = '2';
    public string $exchange_rate = '1';
    public bool $is_active = true;
    public bool $is_default = false;
    public string $sort_order = '0';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('currencies.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('currencies', 'name')],
            'code' => ['required', 'string', 'size:3', tenant()->unique('currencies', 'code')],
            'symbol' => ['required', 'string', 'max:20'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(CreateCurrencyAction $action): mixed
    {
        abort_unless(auth()->user()?->can('currencies.create'), 403);

        $this->validate();

        $action->handle([
            'name' => trim($this->name),
            'code' => strtoupper(trim($this->code)),
            'symbol' => trim($this->symbol),
            'decimal_places' => (int) $this->decimal_places,
            'exchange_rate' => (float) $this->exchange_rate,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'sort_order' => (int) $this->sort_order,
        ]);

        session()->flash('success', 'Currency created successfully.');

        return $this->redirectRoute('currencies.index');
    }

    public function render(): View
    {
        return view('livewire.currencies.create-page');
    }
}
