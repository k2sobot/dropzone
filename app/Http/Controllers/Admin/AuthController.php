<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        // Check database-stored password first, fall back to env
        $storedHash = AdminSetting::get('admin_password');
        
        if ($storedHash) {
            // Password was set via UI - verify hash
            if (Hash::check($request->get('password'), $storedHash)) {
                session(['admin_authenticated' => true]);
                return redirect()->route('admin.dashboard');
            }
        } else {
            // Fall back to env password (setup wizard or .env)
            $adminPassword = config('app.admin_password', env('ADMIN_PASSWORD', 'admin123'));
            
            if ($request->get('password') === $adminPassword) {
                session(['admin_authenticated' => true]);
                return redirect()->route('admin.dashboard');
            }
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