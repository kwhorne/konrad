<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToCompany
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has completed onboarding and belongs to a company.
     * Redirects to onboarding if the user needs to set up a company.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow unauthenticated requests to pass (auth middleware should handle this)
        if (! $user) {
            return $next($request);
        }

        // Check if user needs onboarding
        if ($user->needsOnboarding()) {
            // Don't redirect if already on onboarding pages
            if ($request->routeIs('onboarding.*')) {
                return $next($request);
            }

            return redirect()->route('onboarding.index');
        }

        // Check if current company is set
        $company = app('current.company');

        if (! $company) {
            // User has companies but none is set as current - this shouldn't happen
            // with SetCurrentCompany middleware, but handle it gracefully
            return redirect()->route('onboarding.index')
                ->with('error', 'Kunne ikke finne aktivt selskap. Vennligst velg et selskap.');
        }

        return $next($request);
    }
}
