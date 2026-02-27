<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetDefaultTenantParameter
{
    public function handle(Request $request, Closure $next)
    {
        // Check if a tenant is currently identified by stancl/tenancy
        if (tenant('id')) {
            // Set the default value for the {tenant} route parameter
            URL::defaults([
                'tenant' => tenant('id')
            ]);
        }

        return $next($request);
    }
}
