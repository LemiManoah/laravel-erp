<?php

declare(strict_types=1);

namespace App\Http\Controllers\CentralAdmin;

use App\Actions\CentralAdmin\CreateTenantFromSupportAction;
use App\Actions\CentralAdmin\RunTenantMaintenanceAction;
use App\Actions\CentralAdmin\SetTenantActiveStateAction;
use App\Actions\CentralAdmin\UpdateTenantFromSupportAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stancl\Tenancy\Database\Models\Domain;

final readonly class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->orderBy('name')
            ->get();

        return view('central-admin.tenants.index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): View
    {
        return view('central-admin.tenants.create');
    }

    public function store(Request $request, CreateTenantFromSupportAction $action): RedirectResponse
    {
        $payload = [
            ...$request->all(),
            'slug' => filled($request->input('slug'))
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
            'primary_domain' => Str::lower(trim((string) $request->input('primary_domain'))),
        ];

        $validated = validator($payload, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Tenant::class, 'slug')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'primary_domain' => ['required', 'string', 'max:255', Rule::unique(Domain::class, 'domain')],
            'is_active' => ['nullable', 'boolean'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_phone' => ['nullable', 'string', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        $tenant = $action->handle($validated);

        $routeName = $request->routeIs('support.*')
            ? 'support.tenants.index'
            : 'central.admin.tenants.index';

        return redirect()
            ->route($routeName)
            ->with('success', sprintf('Tenant "%s" created successfully.', $tenant->name));
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('central-admin.tenants.edit', [
            'tenant' => $tenant,
            'primaryDomain' => $tenant->domains->sortBy('id')->first(),
        ]);
    }

    public function update(Request $request, Tenant $tenant, UpdateTenantFromSupportAction $action): RedirectResponse
    {
        $primaryDomain = $tenant->domains()->orderBy('id')->first();

        $payload = [
            ...$request->all(),
            'slug' => filled($request->input('slug'))
                ? Str::slug((string) $request->input('slug'))
                : Str::slug((string) $request->input('name')),
            'primary_domain' => Str::lower(trim((string) $request->input('primary_domain'))),
        ];

        $validated = validator($payload, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Tenant::class, 'slug')->ignore($tenant->getKey())],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'primary_domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Domain::class, 'domain')->ignore($primaryDomain?->getKey()),
            ],
            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        $tenant = $action->handle($tenant, $validated);

        $routeName = $request->routeIs('support.*')
            ? 'support.tenants.index'
            : 'central.admin.tenants.index';

        return redirect()
            ->route($routeName)
            ->with('success', sprintf('Tenant "%s" updated successfully.', $tenant->name));
    }

    public function suspend(Request $request, Tenant $tenant, SetTenantActiveStateAction $action): RedirectResponse
    {
        $tenant = $action->handle($tenant, false);

        return redirect()
            ->route($this->tenantIndexRoute($request))
            ->with('success', sprintf('Tenant "%s" suspended successfully.', $tenant->name));
    }

    public function reactivate(Request $request, Tenant $tenant, SetTenantActiveStateAction $action): RedirectResponse
    {
        $tenant = $action->handle($tenant, true);

        return redirect()
            ->route($this->tenantIndexRoute($request))
            ->with('success', sprintf('Tenant "%s" reactivated successfully.', $tenant->name));
    }

    public function rerunBootstrap(Request $request, Tenant $tenant, RunTenantMaintenanceAction $action): RedirectResponse
    {
        $action->handle($tenant, 'bootstrap');

        return redirect()
            ->route($this->tenantEditRoute($request), $tenant)
            ->with('success', sprintf('Core setup refreshed for tenant "%s".', $tenant->name));
    }

    public function refreshDemoData(Request $request, Tenant $tenant, RunTenantMaintenanceAction $action): RedirectResponse
    {
        $action->handle($tenant, 'demo_refresh');

        return redirect()
            ->route($this->tenantEditRoute($request), $tenant)
            ->with('success', sprintf('Demo baseline refreshed for tenant "%s".', $tenant->name));
    }

    private function tenantIndexRoute(Request $request): string
    {
        return $request->routeIs('support.*')
            ? 'support.tenants.index'
            : 'central.admin.tenants.index';
    }

    private function tenantEditRoute(Request $request): string
    {
        return $request->routeIs('support.*')
            ? 'support.tenants.edit'
            : 'central.admin.tenants.edit';
    }
}
