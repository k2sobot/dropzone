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
     * Validates both authentication flag and session integrity.
     */
    public function handle( Request $request, Closure $next ): Response
    {
        // Check if admin is authenticated
        if ( ! session( 'admin_authenticated' ) ) {
            return redirect()->route( 'admin.login' )->with( 'error', 'Please log in to access the admin area.' );
        }

        // Validate session has required data
        if ( ! session( 'admin_username' ) || ! session( 'admin_login_time' ) ) {
            // Invalid session - clear and redirect
            session()->forget( [
                'admin_authenticated',
                'admin_username',
                'admin_login_time',
            ] );
            return redirect()->route( 'admin.login' )->with( 'error', 'Session invalid. Please log in again.' );
        }

        // Optional: Check session age (24 hour max)
        $maxSessionAge = config( 'app.admin_session_lifetime', 86400 ); // 24 hours default
        if ( time() - session( 'admin_login_time' ) > $maxSessionAge ) {
            session()->forget( [
                'admin_authenticated',
                'admin_username',
                'admin_login_time',
            ] );
            return redirect()->route( 'admin.login' )->with( 'error', 'Session expired. Please log in again.' );
        }

        return $next( $request );
    }
}
