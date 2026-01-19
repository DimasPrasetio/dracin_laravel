<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Category;

class CategoryAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Check if user has access to the specified category.
     * Super admin has access to all categories.
     *
     * Usage in routes:
     * Route::get('/categories/{category}/movies', ...)->middleware('category.access');
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // Super admin has access to all categories
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get category from route parameter
        $category = $request->route('category');

        if (!$category) {
            // No category in route, allow access
            return $next($request);
        }

        // If category is an ID, fetch the model
        if (!$category instanceof Category) {
            $category = Category::find($category);
        }

        if (!$category) {
            abort(404, 'Kategori tidak ditemukan');
        }

        // Check if user has access to this category
        if (!$user->hasAccessToCategory($category->id)) {
            abort(403, 'Anda tidak memiliki akses ke kategori ini');
        }

        return $next($request);
    }
}
