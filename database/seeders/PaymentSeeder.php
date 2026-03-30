<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

final class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $currencyIds = Currency::query()->pluck('id', 'code');
        $invoiceIds = Invoice::query()->pluck('id', 'invoice_number');
        $paymentMethodIds = PaymentMethod::query()->pluck('id', 'name');
        $userIds = User::query()->pluck('id', 'email');

        foreach ($this->payments() as $payment) {
            $invoiceNumber = $payment['invoice_number'];
            $currencyCode = $payment['currency_code'];
            $receivedByEmail = $payment['received_by_email'];
            $voidedByEmail = $payment['voided_by_email'];
            unset($payment['invoice_number']);
            unset($payment['currency_code']);
            unset($payment['received_by_email']);
            unset($payment['voided_by_email']);

            Payment::query()->updateOrCreate(
                ['reference_number' => $payment['reference_number']],
                [
                    ...$payment,
                    'invoice_id' => $invoiceIds[$invoiceNumber] ?? null,
                    'currency_id' => $currencyIds[$currencyCode] ?? null,
                    'payment_method_id' => $paymentMethodIds[$payment['payment_method']] ?? null,
                    'received_by' => $userIds[$receivedByEmail] ?? null,
                    'voided_by' => $voidedByEmail === null ? null : ($userIds[$voidedByEmail] ?? null),
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function payments(): array
    {
        return [
            [
                'invoice_number' => 'INV-2026-001',
                'currency_code' => 'UGX',
                'payment_date' => '2026-02-08',
                'amount' => 1296.00,
                'payment_method' => 'Card',
                'reference_number' => 'CC-1234567890',
                'notes' => 'Full payment via card.',
                'status' => 'valid',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'currency_code' => 'UGX',
                'payment_date' => '2026-01-09',
                'amount' => 1500.00,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'BT-WEDDING-001',
                'notes' => 'Initial payment for wedding package.',
                'status' => 'valid',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'currency_code' => 'UGX',
                'payment_date' => '2026-01-29',
                'amount' => 1416.00,
                'payment_method' => 'Cash',
                'reference_number' => 'CASH-002',
                'notes' => 'Final payment in cash.',
                'status' => 'valid',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'currency_code' => 'USD',
                'payment_date' => '2026-02-23',
                'amount' => 1000.00,
                'payment_method' => 'Card',
                'reference_number' => 'CC-9876543210',
                'notes' => 'Initial payment for power suits.',
                'status' => 'valid',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'currency_code' => 'USD',
                'payment_date' => '2026-03-10',
                'amount' => 1000.00,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'BT-SUIT-002',
                'notes' => 'Second installment.',
                'status' => 'valid',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'invoice_number' => 'INV-2026-001',
                'currency_code' => 'UGX',
                'payment_date' => '2026-02-03',
                'amount' => 100.00,
                'payment_method' => 'Check',
                'reference_number' => 'CHK-VOID-001',
                'notes' => 'This payment was voided due to insufficient funds.',
                'status' => 'voided',
                'received_by_email' => 'admin@suits.com',
                'voided_at' => '2026-02-04 10:00:00',
                'voided_by_email' => 'admin@suits.com',
                'void_reason' => 'Insufficient funds.',
            ],
        ];
    }
}
