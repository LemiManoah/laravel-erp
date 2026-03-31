<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class SupplierPurchasingReportRequest extends ReportDateRangeRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'supplier_id' => ['nullable', 'integer'],
        ]);
    }
}
