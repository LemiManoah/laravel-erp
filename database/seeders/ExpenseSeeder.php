<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = ExpenseCategory::query()->pluck('id', 'name');
        $currencyIds = Currency::query()->pluck('id', 'code');
        $paymentMethodIds = PaymentMethod::query()->pluck('id', 'name');
        $userIds = User::query()->pluck('id', 'email');

        foreach ($this->expenses() as $expense) {
            $categoryName = $expense['category_name'];
            $currencyCode = $expense['currency_code'];
            $createdByEmail = $expense['created_by_email'];
            $voidedByEmail = $expense['voided_by_email'];
            unset($expense['category_name']);
            unset($expense['currency_code']);
            unset($expense['created_by_email']);
            unset($expense['voided_by_email']);

            Expense::query()->updateOrCreate(
                ['reference_number' => $expense['reference_number']],
                [
                    ...$expense,
                    'expense_category_id' => $categoryIds[$categoryName] ?? null,
                    'currency_id' => $currencyIds[$currencyCode] ?? null,
                    'payment_method_id' => $paymentMethodIds[$expense['payment_method']] ?? null,
                    'created_by' => $userIds[$createdByEmail] ?? null,
                    'voided_by' => $voidedByEmail === null ? null : ($userIds[$voidedByEmail] ?? null),
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expenses(): array
    {
        return [
            [
                'category_name' => 'Fabric and Materials',
                'currency_code' => 'UGX',
                'expense_date' => '2026-01-04',
                'amount' => 2500.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'Premium Textiles Ltd',
                'reference_number' => 'FAB-001',
                'description' => 'Wool fabric bulk purchase',
                'notes' => 'High-quality merino wool for winter collection.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Fabric and Materials',
                'currency_code' => 'UGX',
                'expense_date' => '2026-01-29',
                'amount' => 1200.00,
                'payment_method' => 'Card',
                'vendor_name' => 'Silk & Co',
                'reference_number' => 'FAB-002',
                'description' => 'Silk lining materials',
                'notes' => 'Various colors for custom orders.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Rent',
                'currency_code' => 'UGX',
                'expense_date' => '2026-02-28',
                'amount' => 3500.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'City Properties Inc',
                'reference_number' => 'RENT-MAR',
                'description' => 'Monthly shop rent',
                'notes' => 'March 2026 rent payment.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Utilities',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-15',
                'amount' => 450.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'Power Company',
                'reference_number' => 'UTIL-001',
                'description' => 'Electricity bill',
                'notes' => 'Monthly electricity consumption.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Tailor Labor',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-05',
                'amount' => 4500.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'Master Tailor - James Wilson',
                'reference_number' => 'SAL-JW',
                'description' => 'Monthly salary',
                'notes' => 'March 2026 salary payment.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Tailor Labor',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-05',
                'amount' => 2800.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'Assistant - Maria Garcia',
                'reference_number' => 'SAL-MG',
                'description' => 'Monthly salary',
                'notes' => 'March 2026 salary payment.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Equipment and Repairs',
                'currency_code' => 'UGX',
                'expense_date' => '2026-02-18',
                'amount' => 1200.00,
                'payment_method' => 'Card',
                'vendor_name' => 'Sewing Machines Pro',
                'reference_number' => 'EQUIP-001',
                'description' => 'New sewing machine',
                'notes' => 'Industrial grade sewing machine.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Marketing',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-10',
                'amount' => 800.00,
                'payment_method' => 'Card',
                'vendor_name' => 'Social Media Ads',
                'reference_number' => 'MKT-001',
                'description' => 'Facebook and Instagram ads',
                'notes' => 'Spring collection promotion.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Marketing',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-20',
                'amount' => 350.00,
                'payment_method' => 'Cash',
                'vendor_name' => 'Local Magazine',
                'reference_number' => 'MKT-002',
                'description' => 'Magazine advertisement',
                'notes' => 'Quarterly business magazine feature.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Miscellaneous',
                'currency_code' => 'UGX',
                'expense_date' => '2026-02-23',
                'amount' => 250.00,
                'payment_method' => 'Cash',
                'vendor_name' => 'Office Depot',
                'reference_number' => 'OFF-001',
                'description' => 'Office supplies',
                'notes' => 'Paper, pens, printer ink, and other stationery.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Packaging',
                'currency_code' => 'UGX',
                'expense_date' => '2026-03-18',
                'amount' => 150.00,
                'payment_method' => 'Card',
                'vendor_name' => 'Express Delivery Co',
                'reference_number' => 'SHIP-001',
                'description' => 'Customer delivery',
                'notes' => 'Express delivery to Emily Rodriguez.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Equipment and Repairs',
                'currency_code' => 'UGX',
                'expense_date' => '2026-02-08',
                'amount' => 500.00,
                'payment_method' => 'Bank Transfer',
                'vendor_name' => 'Accounting Firm LLP',
                'reference_number' => 'PROF-001',
                'description' => 'Monthly accounting services',
                'notes' => 'Bookkeeping and tax preparation.',
                'status' => 'valid',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => null,
                'voided_by_email' => null,
                'void_reason' => null,
            ],
            [
                'category_name' => 'Fabric and Materials',
                'currency_code' => 'UGX',
                'expense_date' => '2026-01-19',
                'amount' => 300.00,
                'payment_method' => 'Cash',
                'vendor_name' => 'Fabric Store',
                'reference_number' => 'VOID-001',
                'description' => 'Fabric purchase',
                'notes' => 'This expense was voided after the wrong item was ordered.',
                'status' => 'voided',
                'created_by_email' => 'admin@suits.com',
                'voided_at' => '2026-01-20 09:30:00',
                'voided_by_email' => 'admin@suits.com',
                'void_reason' => 'Wrong item ordered and returned to vendor.',
            ],
        ];
    }
}
