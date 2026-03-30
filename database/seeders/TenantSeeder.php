<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

final class TenantSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->tenants() as $record) {
            $domain = $record['domain'];
            unset($record['domain']);

            $tenant = Tenant::query()->firstWhere('slug', $record['slug']);

            if ($tenant === null) {
                $tenant = Tenant::create($record);
            } else {
                $tenant->fill($record);

                if ($tenant->isDirty()) {
                    $tenant->save();
                }
            }

            $tenant->domains()->updateOrCreate(
                ['domain' => $domain],
                [],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tenants(): array
    {
        return [
            [
                'name' => 'Acme Bespoke',
                'slug' => 'acme-bespoke',
                'email' => 'hello@acme.localhost',
                'phone' => '+256700000101',
                'is_active' => true,
                'domain' => 'acme.localhost',
            ],
            [
                'name' => 'Savile Demo House',
                'slug' => 'savile-demo-house',
                'email' => 'hello@savile.localhost',
                'phone' => '+256700000202',
                'is_active' => true,
                'domain' => 'savile.localhost',
            ],
        ];
    }
}
