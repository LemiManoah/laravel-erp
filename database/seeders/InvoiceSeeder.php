<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

final class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $customerIds = Customer::query()->pluck('id', 'customer_code');
        $currencyIds = Currency::query()->pluck('id', 'code');
        $orderIds = Order::query()->pluck('id', 'order_number');
        $userIds = User::query()->pluck('id', 'email');

        foreach ($this->invoices() as $record) {
            $items = $record['items'];
            $customerCode = $record['customer_code'];
            $currencyCode = $record['currency_code'];
            $orderNumber = $record['order_number'];
            $createdByEmail = $record['created_by_email'];
            unset($record['items']);
            unset($record['customer_code']);
            unset($record['currency_code']);
            unset($record['order_number']);
            unset($record['created_by_email']);

            $invoice = Invoice::query()->updateOrCreate(
                ['invoice_number' => $record['invoice_number']],
                [
                    ...$record,
                    'customer_id' => $customerIds[$customerCode] ?? null,
                    'currency_id' => $currencyIds[$currencyCode] ?? null,
                    'order_id' => $orderNumber === null ? null : ($orderIds[$orderNumber] ?? null),
                    'created_by' => $userIds[$createdByEmail] ?? null,
                ],
            );

            foreach ($items as $item) {
                InvoiceItem::query()->updateOrCreate(
                    [
                        'invoice_id' => $invoice->id,
                        'item_name' => $item['item_name'],
                    ],
                    $item,
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function invoices(): array
    {
        return [
            [
                'invoice_number' => 'INV-2026-001',
                'customer_code' => 'CUST001',
                'currency_code' => 'UGX',
                'order_number' => 'ORD-2026-001',
                'invoice_date' => '2026-01-31',
                'due_date' => '2026-03-02',
                'status' => 'paid',
                'subtotal_amount' => 1200.00,
                'discount_amount' => 0.00,
                'tax_amount' => 96.00,
                'total_amount' => 1296.00,
                'amount_paid' => 1296.00,
                'balance_due' => 0.00,
                'notes' => 'Payment received in full.',
                'created_by_email' => 'admin@suits.com',
                'issued_at' => '2026-01-31 10:00:00',
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Navy Business Suit',
                        'description' => 'Classic two-button navy suit with pinstripes.',
                        'quantity' => 1,
                        'unit_price' => 1200.00,
                        'line_total' => 1200.00,
                    ],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'customer_code' => 'CUST003',
                'currency_code' => 'UGX',
                'order_number' => 'ORD-2026-003',
                'invoice_date' => '2025-12-31',
                'due_date' => '2026-01-30',
                'status' => 'paid',
                'subtotal_amount' => 2800.00,
                'discount_amount' => 100.00,
                'tax_amount' => 216.00,
                'total_amount' => 2916.00,
                'amount_paid' => 2916.00,
                'balance_due' => 0.00,
                'notes' => 'Wedding package discount applied.',
                'created_by_email' => 'admin@suits.com',
                'issued_at' => '2025-12-31 11:30:00',
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Black Tuxedo with Tails',
                        'description' => 'Traditional formal tuxedo.',
                        'quantity' => 1,
                        'unit_price' => 2500.00,
                        'line_total' => 2500.00,
                    ],
                    [
                        'item_name' => 'Matching Waistcoat',
                        'description' => 'Double-breasted waistcoat.',
                        'quantity' => 1,
                        'unit_price' => 300.00,
                        'line_total' => 300.00,
                    ],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'customer_code' => 'CUST002',
                'currency_code' => 'USD',
                'order_number' => 'ORD-2026-002',
                'invoice_date' => '2026-02-15',
                'due_date' => '2026-03-17',
                'status' => 'partially_paid',
                'subtotal_amount' => 3600.00,
                'discount_amount' => 0.00,
                'tax_amount' => 288.00,
                'total_amount' => 3888.00,
                'amount_paid' => 2000.00,
                'balance_due' => 1888.00,
                'notes' => 'Partial payment received, balance due soon.',
                'created_by_email' => 'admin@suits.com',
                'issued_at' => '2026-02-15 09:45:00',
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Charcoal Grey Power Suit',
                        'description' => 'Executive cut power suits.',
                        'quantity' => 2,
                        'unit_price' => 1500.00,
                        'line_total' => 3000.00,
                    ],
                    [
                        'item_name' => 'White Silk Blouses',
                        'description' => 'French cuff blouses.',
                        'quantity' => 3,
                        'unit_price' => 200.00,
                        'line_total' => 600.00,
                    ],
                ],
            ],
            [
                'invoice_number' => 'INV-2026-004',
                'customer_code' => 'CUST004',
                'currency_code' => 'UGX',
                'order_number' => null,
                'invoice_date' => '2026-03-25',
                'due_date' => '2026-04-24',
                'status' => 'draft',
                'subtotal_amount' => 0.00,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'total_amount' => 0.00,
                'amount_paid' => 0.00,
                'balance_due' => 0.00,
                'notes' => 'Draft invoice for consultation services.',
                'created_by_email' => 'admin@suits.com',
                'issued_at' => null,
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'items' => [],
            ],
            [
                'invoice_number' => 'INV-2026-005',
                'customer_code' => 'CUST005',
                'currency_code' => 'UGX',
                'order_number' => 'ORD-2026-005',
                'invoice_date' => '2026-03-22',
                'due_date' => '2026-04-21',
                'status' => 'issued',
                'subtotal_amount' => 1900.00,
                'discount_amount' => 50.00,
                'tax_amount' => 148.00,
                'total_amount' => 1998.00,
                'amount_paid' => 0.00,
                'balance_due' => 1998.00,
                'notes' => 'Seasonal discount applied.',
                'created_by_email' => 'admin@suits.com',
                'issued_at' => '2026-03-22 15:20:00',
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Seasonal Business Suits',
                        'description' => 'Classic fit business suits.',
                        'quantity' => 2,
                        'unit_price' => 950.00,
                        'line_total' => 1900.00,
                    ],
                ],
            ],
        ];
    }
}
