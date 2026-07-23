<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Has No Access
        if( auth()->check() ) {
            $userRole = auth()->user()->role;
            $hasAccess = in_array( $userRole, $roles );

            if ( !$hasAccess ) {
                return abort(403, "You did't have a permision to login with this account in this system, try again later or call the technical supportc team.");
            }
        }

        // Has Access
        return $next($request);
    }
}
