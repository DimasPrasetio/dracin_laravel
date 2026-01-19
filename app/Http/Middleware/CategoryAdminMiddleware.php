<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Category;

class CategoryAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Check if user is admin of the specified category.
     * Super admin has admin access to all categories.
     *
     * Usage in routes:
     * Route::delete('/categories/{category}', ...)->middleware('category.admin');
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // Super admin has admin access to all categories
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get category from route parameter
        $category = $request->route('category');

        if (!$category) {
            abort(403, 'Kategori tidak ditemukan');
        }

        // If category is an ID, fetch the model
        if (!$category instanceof Category) {
            $category = Category::find($category);
        }

        if (!$category) {
            abort(404, 'Kategori tidak ditemukan');
        }

        // Check if user is admin for this category
        if (!$user->isAdminForCategory($category->id)) {
            abort(403, 'Anda bukan admin kategori ini');
        }

        return $next($request);
    }
}
