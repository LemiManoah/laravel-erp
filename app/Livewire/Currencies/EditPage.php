<?php

declare(strict_types=1);

namespace App\Livewire\Currencies;

use App\Actions\Currency\UpdateCurrencyAction;
use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $currencyId;

    public string $name = '';
    public string $code = '';
    public string $symbol = '';
    public string $decimal_places = '2';
    public string $exchange_rate = '1';
    public bool $is_active = true;
    public bool $is_default = false;
    public string $sort_order = '0';

    public function mount(Currency $currency): void
    {
        abort_unless(auth()->user()?->can('currencies.update'), 403);

        $this->currencyId = $currency->id;
        $this->name = $currency->name;
        $this->code = $currency->code;
        $this->symbol = $currency->symbol;
        $this->decimal_places = (string) $currency->decimal_places;
        $this->exchange_rate = (string) $currency->exchange_rate;
        $this->is_active = $currency->is_active;
        $this->is_default = $currency->is_default;
        $this->sort_order = (string) $currency->sort_order;
    }

    protected function rules(): array
    {
        $currency = Currency::query()->find($this->currencyId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('currencies', 'name')->ignore($currency)],
            'code' => ['required', 'string', 'size:3', tenant()->unique('currencies', 'code')->ignore($currency)],
            'symbol' => ['required', 'string', 'max:20'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function update(UpdateCurrencyAction $action): mixed
    {
        abort_unless(auth()->user()?->can('currencies.update'), 403);

        $this->validate();

        $currency = Currency::query()->findOrFail($this->currencyId);
        $action->handle($currency, [
            'name' => trim($this->name),
            'code' => strtoupper(trim($this->code)),
            'symbol' => trim($this->symbol),
            'decimal_places' => (int) $this->decimal_places,
            'exchange_rate' => (float) $this->exchange_rate,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'sort_order' => (int) $this->sort_order,
        ]);

        session()->flash('success', 'Currency updated successfully.');

        return $this->redirectRoute('currencies.index');
    }

    public function render(): View
    {
        return view('livewire.currencies.edit-page');
    }
}
