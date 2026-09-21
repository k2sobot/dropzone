<?php

namespace App\Http\Middleware;

use App\Services\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('admin_authenticated') && ! AdminSession::restore($request)) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the admin area.');
        }

        if (! session('admin_username') || ! session('admin_login_time')) {
            AdminSession::logout($request);

            return redirect()->route('admin.login')->with('error', 'Session invalid. Please log in again.');
        }

        $maxSessionAge = session('admin_remembered')
            ? AdminSession::REMEMBER_MINUTES * 60
            : (int) config('app.admin_session_lifetime', 86400);

        if (time() - (int) session('admin_login_time') > $maxSessionAge) {
            AdminSession::logout($request);

            return redirect()->route('admin.login')->with('error', 'Session expired. Please log in again.');
        }

        return $next($request);
    }
}
