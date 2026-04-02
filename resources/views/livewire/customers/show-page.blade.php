<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="{{ route('customers.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                <i class="fas fa-arrow-left mr-1"></i> Back to Customers
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $customer->full_name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">Customer Code: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $customer->customer_code }}</span></p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('orders.create')
                <a href="{{ route('orders.create', ['customer_id' => $customer->id]) }}" class="rounded-md bg-green-600 px-4 py-2 text-white transition hover:bg-green-700">
                    <i class="fas fa-shopping-bag mr-2"></i> New Order
                </a>
            @endcan
            @can('invoices.create')
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> New Invoice
                </a>
            @endcan
            @can('customers.update')
                <a href="{{ route('customers.edit', $customer) }}" class="rounded-md bg-yellow-600 px-4 py-2 text-white transition hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </a>
            @endcan
            @can('customers.delete')
                <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this customer? This action cannot be undone."
                    class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Sidebar --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-user-circle mr-2 text-blue-500"></i> Contact Information
                </h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone Number</p>
                        <p class="text-gray-900 dark:text-white">{{ $customer->phone }}</p>
                    </div>
                    @if($customer->alternative_phone)
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Alternative Phone</p>
                            <p class="text-gray-900 dark:text-white">{{ $customer->alternative_phone }}</p>
                        </div>
                    @endif
                    @if($customer->email)
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                            <p class="text-gray-900 dark:text-white">{{ $customer->email }}</p>
                        </div>
                    @endif
                    @if($customer->gender)
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Gender</p>
                            <p class="text-gray-900 dark:text-white">{{ $customer->gender }}</p>
                        </div>
                    @endif
                    @if($customer->date_of_birth)
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date of Birth</p>
                            <p class="text-gray-900 dark:text-white">{{ $customer->date_of_birth->format('M d, Y') }}</p>
                        </div>
                    @endif
                    @if($customer->address)
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Address</p>
                            <p class="leading-relaxed text-gray-900 dark:text-white">{{ $customer->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($customer->notes)
                <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-6 shadow-sm dark:border-yellow-900/30 dark:bg-yellow-900/20">
                    <h2 class="mb-2 flex items-center text-lg font-semibold text-yellow-800 dark:text-yellow-400">
                        <i class="fas fa-sticky-note mr-2"></i> Notes
                    </h2>
                    <p class="whitespace-pre-line text-yellow-700 dark:text-yellow-300">{{ $customer->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Main Content --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Lifetime Orders</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customer->orders->count() }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Invoiced</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currencyFormatter->formatValue($customer->invoices->sum('total_amount'), 2) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-500">Outstanding Balance</p>
                    <p class="text-2xl font-bold text-red-600">{{ $currencyFormatter->formatValue($customer->invoices->sum('balance_due'), 2) }}</p>
                </div>
            </div>

            <div x-data="{ activeTab: 'orders' }">
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        @foreach(['orders' => 'Recent Orders', 'invoices' => 'Invoices', 'measurements' => 'Measurements', 'payments' => 'Payments'] as $tab => $label)
                            <button type="button" @click="activeTab = '{{ $tab }}'"
                                :class="activeTab === '{{ $tab }}' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition">
                                {{ $label }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                {{-- Orders Tab --}}
                <div x-show="activeTab === 'orders'">
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Order #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($customer->orders->take(5) as $order)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                                            <a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $order->order_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <a href="{{ route('orders.show', $order) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-white"><i class="fas fa-chevron-right"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No orders found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Invoices Tab --}}
                <div x-show="activeTab === 'invoices'" x-cloak>
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($customer->invoices->take(5) as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $currencyFormatter->formatValue($invoice->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span @class([
                                                'rounded-full px-2 py-1 text-xs font-medium',
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $invoice->status === 'paid',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => $invoice->status === 'partially_paid',
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $invoice->status === 'overdue',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => in_array($invoice->status, ['draft', 'cancelled']),
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => $invoice->status === 'issued',
                                            ])>
                                                {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-white"><i class="fas fa-chevron-right"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No invoices found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Measurements Tab --}}
                <div x-show="activeTab === 'measurements'" x-cloak>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Measurement History</h3>
                        @can('create', \App\Models\Measurement::class)
                            <a href="{{ route('customers.measurements.create', $customer) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                <i class="fas fa-plus mr-1"></i> New Measurements
                            </a>
                        @endcan
                    </div>
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Neck</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Chest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Waist</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($customer->measurements as $measurement)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $measurement->measurement_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $measurement->neck ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $measurement->chest ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $measurement->waist ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            @if($measurement->is_current)
                                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900/30 dark:text-green-300">Current</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            @can('update', $measurement)
                                                <a href="{{ route('measurements.edit', $measurement) }}" class="text-yellow-600 hover:text-yellow-900">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No measurements found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payments Tab --}}
                <div x-show="activeTab === 'payments'" x-cloak>
                    <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Receipt</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($customer->payments->sortByDesc('payment_date')->take(10) as $payment)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                            <a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">
                                            @if($payment->receipt)
                                                <a href="{{ route('receipts.show', $payment->receipt) }}">{{ $payment->receipt->receipt_number }}</a>
                                            @else -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono text-sm {{ $payment->status === 'valid' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $currencyFormatter->formatValue($payment->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No payments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
