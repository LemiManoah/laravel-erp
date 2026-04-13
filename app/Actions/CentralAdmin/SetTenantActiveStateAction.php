<?php

declare(strict_types=1);

namespace App\Actions\CentralAdmin;

use App\Models\Tenant;

final readonly class SetTenantActiveStateAction
{
    public function handle(Tenant $tenant, bool $isActive): Tenant
    {
        if ($tenant->is_active !== $isActive) {
            $tenant->forceFill([
                'is_active' => $isActive,
            ])->save();
        }

        return $tenant->fresh(['domains']) ?? $tenant->load('domains');
    }
}
