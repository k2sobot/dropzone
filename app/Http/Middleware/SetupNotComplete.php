<?php

namespace App\Http\Middleware;

use App\Models\AdminSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupNotComplete
{
    /**
     * Allow the setup wizard only until setup has been completed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $setupComplete = (bool) AdminSetting::get('setup_complete');
        } catch (\Throwable $e) {
            $setupComplete = false;
        }

        if ($setupComplete) {
            return redirect('/');
        }

        return $next($request);
    }
}
