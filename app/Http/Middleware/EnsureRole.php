<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureRole Middleware
 *
 * Usage in routes:
 *   ->middleware('role:super_admin')
 *   ->middleware('role:admin')          // passes for admin OR super_admin
 *   ->middleware('role:super_admin,admin')  // multi-role OR check
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // Normalize: super_admin always passes an admin-level check too
        foreach ($roles as $role) {
            if ($role === 'admin' && $user->isSuperAdmin()) {
                return $next($request);
            }
            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
