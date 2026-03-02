<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InitializeTenantSession
{
    // app/Http/Middleware/InitializeTenantSession.php

    public function handle(Request $request, Closure $next)
    {
        if (auth()->guard('admin')->check()) {
            $firstTenant = \App\Models\Tenant::first();

            // If NO tenants exist in the whole system
            if (!$firstTenant) {
                // Avoid infinite redirect loop if already on create page
                if (!$request->routeIs('tenants.create')) {
                    return redirect()->route('tenants.create');
                }
            }

            // If tenants exist but session is not set
            elseif (!session()->has('active_tenant_id')) {
                session(['active_tenant_id' => $firstTenant->id]);
            }
        }

        return $next($request);
    }
}
