<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * Sets the current company in the application container based on
     * the authenticated user's current_company_id.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $company = null;

            if ($request->user()->current_company_id) {
                // Load current company if user belongs to it
                $company = $request->user()->companies()
                    ->where('companies.id', $request->user()->current_company_id)
                    ->first();
            }

            // If no current company or user doesn't belong to it, try default
            if (! $company) {
                $company = $request->user()->defaultCompany();

                // Update user's current company if we found a default
                if ($company && $request->user()->current_company_id !== $company->id) {
                    $request->user()->update(['current_company_id' => $company->id]);
                }
            }

            // Bind the current company to the container
            app()->instance('current.company', $company);
        } else {
            // No authenticated user, set null
            app()->instance('current.company', null);
        }

        return $next($request);
    }
}
