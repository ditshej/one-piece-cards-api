<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = config('auth.api_key');

        if (! $key || $request->bearerToken() !== $key) {
            abort(401, 'Unauthorized');
        }

        return $next($request);
    }
}
