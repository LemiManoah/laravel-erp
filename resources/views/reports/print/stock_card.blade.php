<div>
    <div class="header">
        <div>
            <h1>Inventory Item Stock Card</h1>
            <p class="meta">{{ $start_date }} to {{ $end_date }}</p>
        </div>
    </div>

    @if($selected_product)
        <div class="cards">
            <div class="card"><div class="label">Inventory Item</div><div class="value">{{ $selected_product->name }}</div></div>
            <div class="card"><div class="label">Current Qty</div><div class="value">{{ number_format((float) $summary['current_quantity'], 2) }}</div></div>
            <div class="card"><div class="label">Qty In</div><div class="value">{{ number_format((float) $summary['quantity_in'], 2) }}</div></div>
            <div class="card"><div class="label">Qty Out</div><div class="value">{{ number_format((float) $summary['quantity_out'], 2) }}</div></div>
        </div>

        <h2>Current Stock Rows</h2>
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Stock Row</th>
                    <th>Expiry</th>
                    <th class="text-right">On Hand</th>
                    <th class="text-right">Unit Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock_rows as $stock)
                    <tr>
                        <td>{{ $stock->location?->name ?? 'Unassigned' }}</td>
                        <td>{{ $stock->batch_number ?? 'Standard stock row' }}</td>
                        <td>{{ $stock->expiry_date?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="text-right">{{ number_format((float) $stock->quantity_on_hand, 2) }}</td>
                        <td class="text-right">{{ $stock->unit_cost === null ? 'N/A' : $currencyFormatter->formatValue($stock->unit_cost, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No stock rows found for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Movement History</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Stock Row</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Balance</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>{{ $movement->movement_date->format('M d, Y H:i') }}</td>
                        <td>{{ $movement->movement_type->label() }} ({{ $movement->direction->label() }})</td>
                        <td>{{ $movement->location?->name ?? 'N/A' }}</td>
                        <td>{{ $movement->inventoryStock?->batch_number ?? 'Standard stock row' }}</td>
                        <td class="text-right">{{ number_format((float) $movement->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $movement->balance_after, 2) }}</td>
                        <td>{{ $movement->notes ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No stock card entries found for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <tbody>
                <tr><td class="muted">Choose a stock-tracked inventory item to print its stock card.</td></tr>
            </tbody>
        </table>
    @endif
</div>
