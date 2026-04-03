<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle( Request $request, Closure $next ): Response
    {
        // Check if user is logged in OR using simple password auth
        if ( Auth::check() ) {
            // User authentication - check role
            if ( ! Auth::user()->hasPermissionTo( 'manage settings' ) ) {
                abort( 403, 'Unauthorized' );
            }

            return $next( $request );
        }

        // Simple password auth (for backwards compatibility)
        if ( session( 'admin_authenticated' ) ) {
            return $next( $request );
        }

        return redirect()->route( 'admin.login' );
    }
}
