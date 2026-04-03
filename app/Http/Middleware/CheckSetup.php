<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSetup
{
    /**
     * Handle an incoming request.
     *
     * Redirects to setup wizard if not completed.
     */
    public function handle( Request $request, Closure $next ): Response
    {
        $setupComplete = cache()->remember( 'setup_complete', 3600, function () {
            try {
                return (bool) \App\Models\AdminSetting::where( 'key', 'setup_complete' )->value( 'value' );
            } catch ( \Exception $e ) {
                return false;
            }
        } );

        if ( ! $setupComplete && ! $request->is( 'setup' ) && ! $request->is( 'admin/*' ) ) {
            return redirect()->route( 'setup' );
        }

        return $next( $request );
    }
}
