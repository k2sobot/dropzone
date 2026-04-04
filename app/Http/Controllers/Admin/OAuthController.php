<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Models\OAuthProvider;
use App\Models\SystemLog;
use App\Services\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OAuthController
{
    protected OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Redirect to OAuth provider.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (!$this->oauthService->isProviderEnabled($provider)) {
            return redirect()->route('admin.login')
                ->with('error', ucfirst($provider) . ' login is not configured.');
        }

        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);
        $request->session()->put('oauth_provider', $provider);

        $redirectUri = route('admin.oauth.callback', ['provider' => $provider]);
        $authUrl = $this->oauthService->getAuthorizationUrl($provider, $redirectUri, $state);

        if (!$authUrl) {
            return redirect()->route('admin.login')
                ->with('error', 'Failed to generate authorization URL.');
        }

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        // Verify state
        $storedState = $request->session()->get('oauth_state');
        $storedProvider = $request->session()->get('oauth_provider');
        $request->session()->forget(['oauth_state', 'oauth_provider']);

        if (!$storedState || $storedState !== $request->get('state') || $storedProvider !== $provider) {
            SystemLog::warning('OAuth invalid state', [
                'provider' => $provider,
                'ip' => $request->ip(),
            ]);
            return redirect()->route('admin.login')
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        // Check for error
        if ($request->has('error')) {
            SystemLog::warning('OAuth error', [
                'provider' => $provider,
                'error' => $request->get('error'),
                'ip' => $request->ip(),
            ]);
            return redirect()->route('admin.login')
                ->with('error', 'OAuth authentication failed: ' . $request->get('error_description', $request->get('error')));
        }

        // Exchange code for token
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('admin.login')
                ->with('error', 'No authorization code received.');
        }

        $redirectUri = route('admin.oauth.callback', ['provider' => $provider]);
        $tokenInfo = $this->oauthService->getAccessToken($provider, $code, $redirectUri);

        if (!$tokenInfo || empty($tokenInfo['access_token'])) {
            return redirect()->route('admin.login')
                ->with('error', 'Failed to obtain access token.');
        }

        // Get user info
        $userInfo = $this->oauthService->getUserInfo($provider, $tokenInfo['access_token']);

        if (!$userInfo || empty($userInfo['email'])) {
            return redirect()->route('admin.login')
                ->with('error', 'Failed to get user information from ' . ucfirst($provider) . '.');
        }

        // Check if this OAuth user has admin access
        $oauthProvider = $this->oauthService->createOrUpdateProvider($userInfo, $tokenInfo);

        if (!$oauthProvider->is_admin) {
            SystemLog::warning('OAuth login attempt by non-admin', [
                'provider' => $provider,
                'email' => $userInfo['email'],
                'ip' => $request->ip(),
            ]);
            return redirect()->route('admin.login')
                ->with('error', 'Access denied. Your ' . ucfirst($provider) . ' account is not authorized for admin access.');
        }

        // Check if 2FA is enabled for this user
        $adminUsername = $this->getAdminUsernameForEmail($userInfo['email']);
        if ($adminUsername && $this->requiresTwoFactor($adminUsername)) {
            // Store OAuth session temporarily and redirect to 2FA
            $request->session()->put('oauth_2fa_pending', [
                'username' => $adminUsername,
                'provider' => $provider,
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
            ]);
            return redirect()->route('admin.2fa.verify');
        }

        // Log in the user
        session([
            'admin_authenticated' => true,
            'admin_username' => $adminUsername ?? $userInfo['email'],
            'admin_login_time' => time(),
            'admin_oauth_provider' => $provider,
            'admin_oauth_name' => $userInfo['name'],
            'admin_oauth_avatar' => $userInfo['avatar'],
        ]);

        SystemLog::info('OAuth admin login successful', [
            'provider' => $provider,
            'email' => $userInfo['email'],
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Grant admin access to an OAuth provider user.
     */
    public function grantAdmin(Request $request, string $provider): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('email', $request->get('email'))
            ->first();

        if (!$oauthProvider) {
            return back()->with('error', 'OAuth account not found.');
        }

        $oauthProvider->grantAdmin();

        SystemLog::info('OAuth admin access granted', [
            'provider' => $provider,
            'email' => $oauthProvider->email,
            'granted_by' => session('admin_username'),
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'Admin access granted to ' . $oauthProvider->email);
    }

    /**
     * Disconnect OAuth provider.
     */
    public function disconnect(string $provider): RedirectResponse
    {
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('email', session('admin_username'))
            ->first();

        if ($oauthProvider) {
            $oauthProvider->delete();
        }

        session()->forget(['admin_oauth_provider', 'admin_oauth_name', 'admin_oauth_avatar']);

        return redirect()->route('admin.settings.security')
            ->with('success', ucfirst($provider) . ' account disconnected.');
    }

    /**
     * Show OAuth management page.
     */
    public function manage(): View
    {
        $enabledProviders = $this->oauthService->getEnabledProviders();
        $connectedProviders = [];

        if (session('admin_authenticated')) {
            $connectedProviders = OAuthProvider::where('email', session('admin_username'))
                ->orWhere('is_admin', true)
                ->get()
                ->keyBy('provider')
                ->toArray();
        }

        return view('admin.settings.oauth', [
            'enabledProviders' => $enabledProviders,
            'connectedProviders' => $connectedProviders,
            'siteName' => AdminSetting::getSiteName(),
        ]);
    }

    /**
     * Get admin username for an email address.
     */
    protected function getAdminUsernameForEmail(string $email): ?string
    {
        // Check if email matches admin username or stored OAuth provider
        $storedUsername = AdminSetting::get('admin_username');
        $storedEmail = AdminSetting::get('admin_email');

        if ($storedEmail && $storedEmail === $email) {
            return $storedUsername;
        }

        // Check OAuth providers table
        $provider = OAuthProvider::where('email', $email)
            ->where('is_admin', true)
            ->first();

        if ($provider) {
            return $provider->email;
        }

        // Use email as username if no match
        return $email;
    }

    /**
     * Check if user requires 2FA.
     */
    protected function requiresTwoFactor(string $username): bool
    {
        $twoFactor = \App\Models\TwoFactorAuth::getForUsername($username);
        return $twoFactor && $twoFactor->isEnabled();
    }
}
