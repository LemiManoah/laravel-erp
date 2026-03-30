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
            $domains = $record['domains'];
            unset($record['domains']);

            $tenant = Tenant::query()->firstWhere('slug', $record['slug']);

            if ($tenant === null) {
                $tenant = Tenant::create($record);
            } else {
                $tenant->fill($record);

                if ($tenant->isDirty()) {
                    $tenant->save();
                }
            }

            foreach ($domains as $domain) {
                $tenant->domains()->updateOrCreate(
                    ['domain' => $domain],
                    [],
                );
            }
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
                'email' => 'hello@erp.test',
                'phone' => '+256700000101',
                'is_active' => true,
                'domains' => ['erp.test', 'acme.localhost'],
            ],
            [
                'name' => 'Savile Demo House',
                'slug' => 'savile-demo-house',
                'email' => 'hello@savile.localhost',
                'phone' => '+256700000202',
                'is_active' => true,
                'domains' => ['savile.localhost'],
            ],
        ];
    }
}
