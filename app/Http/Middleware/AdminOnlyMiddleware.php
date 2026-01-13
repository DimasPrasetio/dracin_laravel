<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     * Only allow admin (not moderator)
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Only admin can access
        if (!$user || !$user->isAdmin()) {
            abort(403, 'This action requires admin privileges');
        }

        return $next($request);
    }
}
