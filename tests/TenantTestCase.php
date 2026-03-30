<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $tenantDomain = 'acme.localhost';

    protected Tenant $tenant;

    protected string $originalAppUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAppUrl = config('app.url');

        $this->tenant = Tenant::create();
        $this->tenant->domains()->create([
            'domain' => $this->tenantDomain,
        ]);

        tenancy()->initialize($this->tenant);

        config()->set('app.url', $this->tenantUrl());
        URL::forceRootUrl($this->tenantUrl());

        $this->withServerVariables([
            'HTTP_HOST' => $this->tenantDomain,
            'SERVER_NAME' => $this->tenantDomain,
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->tenant)) {
            tenancy()->end();
        }

        URL::forceRootUrl($this->originalAppUrl);
        config()->set('app.url', $this->originalAppUrl);

        parent::tearDown();
    }

    protected function tenantUrl(string $path = '/'): string
    {
        return sprintf('http://%s/%s', $this->tenantDomain, ltrim($path, '/'));
    }
}
