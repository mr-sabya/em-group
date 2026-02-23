<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;

class IdentifyTenantByHeader
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json(['error' => 'Tenant header missing'], 400);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        // This is the magic line that tells Laravel which store to filter for
        tenancy()->initialize($tenant);

        return $next($request);
    }
}
