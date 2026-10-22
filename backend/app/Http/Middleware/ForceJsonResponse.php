<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Force every API request to expect a JSON response.
     *
     * This guarantees that validation errors, authentication failures and
     * route model binding failures are always rendered as JSON instead of
     * redirects or HTML error pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
