<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController
{
    /**
     * Show login form.
     */
    public function login(): View
    {
        return view('admin.login');
    }

    /**
     * Handle login.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $adminPassword = config('app.admin_password', env('ADMIN_PASSWORD', 'admin123'));

        if ($request->get('password') === $adminPassword) {
            session(['admin_authenticated' => true]);

            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Invalid password.');
    }

    /**
     * Handle logout.
     */
    public function logout(): RedirectResponse
    {
        session()->forget('admin_authenticated');

        return redirect()->route('home');
    }
}