<?php

declare(strict_types=1);

namespace App\Actions\CentralAdmin;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

final readonly class UpdateTenantFromSupportAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Tenant $tenant, array $attributes): Tenant
    {
        return DB::transaction(function () use ($tenant, $attributes): Tenant {
            $tenant->fill([
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'email' => $attributes['email'] ?: null,
                'phone' => $attributes['phone'] ?: null,
                'is_active' => (bool) ($attributes['is_active'] ?? true),
            ]);

            if ($tenant->isDirty()) {
                $tenant->save();
            }

            $primaryDomain = $tenant->domains()
                ->orderBy('id')
                ->first();

            if ($primaryDomain === null) {
                $tenant->domains()->create([
                    'domain' => $attributes['primary_domain'],
                ]);
            } elseif ($primaryDomain->domain !== $attributes['primary_domain']) {
                $primaryDomain->update([
                    'domain' => $attributes['primary_domain'],
                ]);
            }

            return $tenant->load('domains');
        });
    }
}
