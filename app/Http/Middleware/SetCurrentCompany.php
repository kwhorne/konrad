<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompany
{
    /**
     * Session key for storing the current company ID.
     */
    public const SESSION_KEY = 'current_company_id';

    /**
     * Handle an incoming request.
     *
     * Sets the current company in the application container.
     * Priority: session > database (fallback only)
     *
     * This ensures that multi-tab scenarios are safe - each browser session
     * maintains its own company context without race conditions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $company = $this->resolveCompany($request);

            // Bind the current company to the container
            app()->instance('current.company', $company);
        } else {
            // No authenticated user, set null
            app()->instance('current.company', null);
        }

        return $next($request);
    }

    /**
     * Resolve the current company for the request.
     *
     * Priority:
     * 1. Session storage (safe for multi-tab)
     * 2. Database current_company_id (fallback/default)
     * 3. User's default company
     */
    protected function resolveCompany(Request $request): ?Company
    {
        $user = $request->user();

        // Try session first (multi-tab safe)
        $sessionCompanyId = session(self::SESSION_KEY);
        if ($sessionCompanyId) {
            $company = $user->companies()
                ->where('companies.id', $sessionCompanyId)
                ->first();

            if ($company) {
                return $company;
            }
        }

        // Fallback to database (used as initial default)
        if ($user->current_company_id) {
            $company = $user->companies()
                ->where('companies.id', $user->current_company_id)
                ->first();

            if ($company) {
                // Store in session for this browser session
                session([self::SESSION_KEY => $company->id]);

                return $company;
            }
        }

        // Last resort: user's default company
        $company = $user->defaultCompany();
        if ($company) {
            session([self::SESSION_KEY => $company->id]);

            // Also update database as the new default
            if ($user->current_company_id !== $company->id) {
                $user->update(['current_company_id' => $company->id]);
            }
        }

        return $company;
    }
}
