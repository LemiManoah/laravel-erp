<?php

declare(strict_types=1);

namespace App\Livewire\PaymentMethods;

use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        abort_unless(auth()->user()?->can('viewAny', PaymentMethod::class), 403);
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

    public function render(): View
    {
        $search = trim($this->search);

        $paymentMethods = PaymentMethod::query()
            ->withCount(['payments', 'expenses'])
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where(function (Builder $paymentMethodQuery) use ($search): void {
                    $paymentMethodQuery
                        ->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('slug', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->ordered()
            ->paginate(15);

        return view('livewire.payment_methods.index-page', [
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
