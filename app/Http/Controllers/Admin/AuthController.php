<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Models\SystemLog;
use App\Models\TwoFactorAuth;
use App\Services\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController
{
    protected OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Show login form.
     */
    public function login(): View
    {
        $enabledProviders = $this->oauthService->getEnabledProviders();

        return view('admin.login', [
            'siteName' => AdminSetting::getSiteName(),
            'enabledProviders' => $enabledProviders,
        ]);
    }

    /**
     * Handle login.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->get('username');
        $password = $request->get('password');

        // Get stored credentials
        $storedUsername = AdminSetting::get('admin_username');
        $storedPasswordHash = AdminSetting::get('admin_password');

        $credentialsValid = false;

        // Check if credentials are set in database
        if ($storedUsername && $storedPasswordHash) {
            // Verify username matches
            if ($username !== $storedUsername) {
                SystemLog::warning('Failed login attempt - invalid username', [
                    'ip' => $request->ip(),
                    'username' => $username,
                ]);
                return back()->with('error', 'Invalid credentials.')->onlyInput('username');
            }

            // Verify password hash
            if (Hash::check($password, $storedPasswordHash)) {
                $credentialsValid = true;
            }
        } else {
            // Fall back to env credentials (initial setup)
            $envUsername = config('app.admin_username', env('ADMIN_USERNAME', 'admin'));
            $envPassword = config('app.admin_password', env('ADMIN_PASSWORD', 'admin123'));

            if ($username === $envUsername && $password === $envPassword) {
                $credentialsValid = true;
            }
        }

        if ($credentialsValid) {
            // Check if 2FA is enabled
            $twoFactor = TwoFactorAuth::getForUsername($username);
            if ($twoFactor && $twoFactor->isEnabled()) {
                // Store pending login and redirect to 2FA
                $request->session()->put('2fa_pending', [
                    'username' => $username,
                ]);
                return redirect()->route('admin.2fa.verify');
            }

            // No 2FA, complete login
            session([
                'admin_authenticated' => true,
                'admin_username' => $username,
                'admin_login_time' => time(),
            ]);

            SystemLog::info('Admin login successful', [
                'username' => $username,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        SystemLog::warning('Failed login attempt - invalid credentials', [
            'ip' => $request->ip(),
            'username' => $username,
        ]);

        return back()->with('error', 'Invalid credentials.')->onlyInput('username');
    }

    /**
     * Handle logout.
     */
    public function logout(): RedirectResponse
    {
        $username = session('admin_username');

        session()->forget([
            'admin_authenticated',
            'admin_username',
            'admin_login_time',
            'admin_oauth_provider',
            'admin_oauth_name',
            'admin_oauth_avatar',
        ]);

        if ($username) {
            SystemLog::info('Admin logout', ['username' => $username]);
        }

        return redirect()->route('home');
    }
}
