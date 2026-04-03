<?php

declare(strict_types=1);

namespace App\Livewire\Currencies;

use App\Actions\Currency\DeleteCurrencyAction;
use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', Currency::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function delete(int $currencyId, DeleteCurrencyAction $action): void
    {
        $currency = Currency::query()->findOrFail($currencyId);

        abort_unless(auth()->user()?->can('delete', $currency), 403);

        try {
            $action->handle($currency);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        session()->flash('success', 'Currency deleted successfully.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $currencies = Currency::query()
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $currencyQuery) use ($search): void {
                    $currencyQuery
                        ->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('code', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('symbol', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->ordered()
            ->paginate(15);

        return view('livewire.currencies.index-page', [
            'currencies' => $currencies,
        ]);
    }
}
