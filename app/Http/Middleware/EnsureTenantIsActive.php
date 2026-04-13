<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $tenant = tenant();

        if ($tenant === null || $tenant->is_active) {
            return $next($request);
        }

        $user = $request->user();

        if ($request->routeIs('support.*') && $user?->canAccessPlatformTenants()) {
            return $next($request);
        }

        abort(403, 'This tenant is currently suspended.');
    }
}
