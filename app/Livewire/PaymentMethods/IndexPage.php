<?php

declare(strict_types=1);

namespace App\Livewire\PaymentMethods;

use App\Actions\PaymentMethod\DeletePaymentMethodAction;
use App\Models\PaymentMethod;
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

    public function delete(int $paymentMethodId, DeletePaymentMethodAction $action): void
    {
        $paymentMethod = PaymentMethod::query()->findOrFail($paymentMethodId);

        abort_unless(auth()->user()?->can('delete', $paymentMethod), 403);

        try {
            $action->handle($paymentMethod);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        session()->flash('success', 'Payment method deleted successfully.');
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
