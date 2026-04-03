<div>
    <x-ui.page-header title="Payment Methods" description="Manage the allowed methods used in payments and expenses.">
        <x-slot:actions>
            @can('create', \App\Models\PaymentMethod::class)
                <x-ui.action-link href="{{ route('payment-methods.create') }}" variant="primary">
                    Add Payment Method
                </x-ui.action-link>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col gap-3 md:flex-row">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search payment method"
                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            <x-ui.action-link tag="button" type="button" wire:click="clearSearch" variant="secondary">
                Clear
            </x-ui.action-link>
        </div>
    </div>

    @error('payment_method')
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Payments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Expenses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($paymentMethods as $paymentMethod)
                        <tr wire:key="payment-method-row-{{ $paymentMethod->id }}">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $paymentMethod->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $paymentMethod->slug }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $paymentMethod->is_active,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => ! $paymentMethod->is_active,
                                ])>
                                    {{ $paymentMethod->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{{ $paymentMethod->payments_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{{ $paymentMethod->expenses_count }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    @can('update', $paymentMethod)
                                        <a href="{{ route('payment-methods.edit', $paymentMethod) }}"
                                            class="inline-flex items-center rounded-md border border-amber-200 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-900/30">
                                            Edit
                                        </a>
                                    @endcan
                                    @can('delete', $paymentMethod)
                                        <button type="button" wire:click="delete({{ $paymentMethod->id }})" wire:confirm="Delete payment method '{{ addslashes($paymentMethod->name) }}'? This cannot be undone."
                                            class="inline-flex items-center rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/30">
                                            Delete
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No payment methods found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paymentMethods->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                {{ $paymentMethods->links() }}
            </div>
        @endif
    </div>
</div>
