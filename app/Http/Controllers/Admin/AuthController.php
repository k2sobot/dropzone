<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Services\AdminSession;
use App\Services\Turnstile;
use App\Models\SystemLog;
use App\Models\TwoFactorAuth;
use App\Services\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
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

        if ($failed = Turnstile::rejectUnlessValid($request)) {
            return $failed;
        }

        $throttleKey = 'admin-login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Too many login attempts. Try again in {$seconds} seconds.")->onlyInput('username');
        }

        $username = $request->get('username');
        $password = $request->get('password');
        $loginUsername = (string) $username;

        // Get stored credentials
        $storedUsername = AdminSetting::get('admin_username');
        $storedPasswordHash = AdminSetting::get('admin_password');

        $credentialsValid = false;

        // Check if credentials are set in database
        if ($storedUsername && $storedPasswordHash) {
            $storedEmail = (string) AdminSetting::get('admin_email', '');
            $usernameMatches = hash_equals((string) $storedUsername, (string) $username)
                || ($storedEmail !== '' && hash_equals(strtolower($storedEmail), strtolower((string) $username)));

            // Verify username or email matches
            if (! $usernameMatches) {
                RateLimiter::hit($throttleKey, 60);
                SystemLog::warning('Failed login attempt - invalid username', [
                    'ip' => $request->ip(),
                    'username' => $username,
                ]);
                return back()->with('error', 'Invalid credentials.')->onlyInput('username');
            }

            // Verify password hash
            if (Hash::check($password, $storedPasswordHash)) {
                $credentialsValid = true;
                $loginUsername = (string) $storedUsername;
            }
        } else {
            // Fall back to env credentials (initial setup)
            $envUsername = (string) config('app.admin_username', env('ADMIN_USERNAME', 'admin'));
            $envPassword = (string) config('app.admin_password', env('ADMIN_PASSWORD', ''));

            if ($envPassword !== '' && hash_equals($envUsername, (string) $username) && hash_equals($envPassword, (string) $password)) {
                $credentialsValid = true;
                $loginUsername = $envUsername;
            }
        }

        if ($credentialsValid) {
            $remember = $request->filled('remember');

            // Check if 2FA is enabled
            $twoFactor = TwoFactorAuth::getForUsername($loginUsername);
            if ($twoFactor && $twoFactor->isEnabled()) {
                $request->session()->put('2fa_pending', [
                    'username' => $loginUsername,
                    'remember' => $remember,
                ]);
                return redirect()->route('admin.2fa.verify');
            }

            RateLimiter::clear($throttleKey);
            AdminSession::login($request, $loginUsername, $remember);

            SystemLog::info('Admin login successful', [
                'username' => $loginUsername,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        SystemLog::warning('Failed login attempt - invalid credentials', [
            'ip' => $request->ip(),
            'username' => $username,
        ]);

        return back()->with('error', 'Invalid credentials.')->onlyInput('username');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $username = session('admin_username');

        AdminSession::logout($request);

        if ($username) {
            SystemLog::info('Admin logout', ['username' => $username]);
        }

        return redirect()->route('home');
    }
}
