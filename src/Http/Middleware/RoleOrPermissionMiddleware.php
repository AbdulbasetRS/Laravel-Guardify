<?php

namespace Abdulbaset\Guardify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roleOrPermission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        $user = $request->user();
        
        if (! $user || (! $user->hasRole($roleOrPermission) && ! $user->hasPermission($roleOrPermission))) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
