<?php

declare(strict_types=1);

namespace App\Actions\CentralAdmin;

use App\Models\Tenant;
use Database\Seeders\TenantAppSeeder;
use Database\Seeders\TenantBootstrapSeeder;
use InvalidArgumentException;

final readonly class RunTenantMaintenanceAction
{
    public function handle(Tenant $tenant, string $operation): void
    {
        $currentTenant = tenancy()->initialized ? tenant() : null;

        if ($currentTenant !== null) {
            tenancy()->end();
        }

        try {
            tenancy()->initialize($tenant);

            match ($operation) {
                'bootstrap' => app(TenantBootstrapSeeder::class)->run(),
                'demo_refresh' => app(TenantAppSeeder::class)->run(),
                default => throw new InvalidArgumentException("Unsupported tenant maintenance operation [{$operation}]."),
            };
        } finally {
            tenancy()->end();

            if ($currentTenant !== null) {
                tenancy()->initialize($currentTenant);
            }
        }
    }
}
