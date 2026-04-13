<?php

declare(strict_types=1);

namespace App\Http\Controllers\CentralAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Contracts\View\View;

final readonly class DashboardController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->orderByDesc('updated_at')
            ->take(8)
            ->get();

        return view('central-admin.dashboard', [
            'stats' => [
                'total_tenants' => Tenant::query()->count(),
                'active_tenants' => Tenant::query()->where('is_active', true)->count(),
                'inactive_tenants' => Tenant::query()->where('is_active', false)->count(),
                'total_domains' => Domain::query()->count(),
            ],
            'recent_tenants' => $tenants,
        ]);
    }
}
