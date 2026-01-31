<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyManager
{
    /**
     * Handle an incoming request.
     *
     * Requires the user to be an owner or manager of the current company.
     * Used for company settings, user management, and other administrative actions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $company = app('current.company');

        if (! $user || ! $company) {
            abort(403, 'Du har ikke tilgang til denne siden.');
        }

        if (! $user->canManage($company)) {
            abort(403, 'Du må være eier eller administrator for å utføre denne handlingen.');
        }

        return $next($request);
    }
}
