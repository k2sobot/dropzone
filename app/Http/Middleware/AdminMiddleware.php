<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensures user is authenticated as admin.
     */
    public function handle( Request $request, Closure $next ): Response
    {
        if ( ! session( 'admin_authenticated' ) ) {
            return redirect()->route( 'admin.login' );
        }

        return $next( $request );
    }
}
