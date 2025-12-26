<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EconomyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || (! Auth::user()->is_economy && ! Auth::user()->is_admin)) {
            abort(403, 'Tilgang nektet. Økonomi-rettigheter kreves.');
        }

        return $next($request);
    }
}
