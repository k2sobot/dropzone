<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Models\SystemLog;
use App\Models\TwoFactorAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController
{
    /**
     * Show 2FA verification form.
     */
    public function verify(Request $request): View|RedirectResponse
    {
        // Check if coming from OAuth or regular login
        $oauthPending = $request->session()->get('oauth_2fa_pending');
        $regularPending = $request->session()->get('2fa_pending');

        if (!$oauthPending && !$regularPending) {
            return redirect()->route('admin.login');
        }

        return view('admin.2fa-verify', [
            'siteName' => AdminSetting::getSiteName(),
            'hasRecoveryCodes' => true,
        ]);
    }

    /**
     * Verify 2FA code.
     */
    public function check(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $oauthPending = $request->session()->get('oauth_2fa_pending');
        $regularPending = $request->session()->get('2fa_pending');

        if (!$oauthPending && !$regularPending) {
            return redirect()->route('admin.login');
        }

        $username = $oauthPending['username'] ?? $regularPending['username'] ?? null;
        $twoFactor = TwoFactorAuth::getForUsername($username);

        if (!$twoFactor || !$twoFactor->isEnabled()) {
            return redirect()->route('admin.login');
        }

        // Verify the code
        if ($twoFactor->verifyCode($request->get('code'))) {
            $request->session()->forget(['oauth_2fa_pending', '2fa_pending']);

            // Complete login
            if ($oauthPending) {
                session([
                    'admin_authenticated' => true,
                    'admin_username' => $username,
                    'admin_login_time' => time(),
                    'admin_oauth_provider' => $oauthPending['provider'],
                    'admin_oauth_name' => $oauthPending['name'],
                ]);
            } else {
                session([
                    'admin_authenticated' => true,
                    'admin_username' => $username,
                    'admin_login_time' => time(),
                ]);
            }

            SystemLog::info('2FA verification successful', [
                'username' => $username,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        SystemLog::warning('2FA verification failed', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);

        return back()->with('error', 'Invalid verification code.')->withInput();
    }

    /**
     * Verify recovery code.
     */
    public function recover(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $oauthPending = $request->session()->get('oauth_2fa_pending');
        $regularPending = $request->session()->get('2fa_pending');

        if (!$oauthPending && !$regularPending) {
            return redirect()->route('admin.login');
        }

        $username = $oauthPending['username'] ?? $regularPending['username'] ?? null;
        $twoFactor = TwoFactorAuth::getForUsername($username);

        if (!$twoFactor) {
            return redirect()->route('admin.login');
        }

        // Verify recovery code
        if ($twoFactor->verifyRecoveryCode($request->get('recovery_code'))) {
            $request->session()->forget(['oauth_2fa_pending', '2fa_pending']);

            // Complete login
            if ($oauthPending) {
                session([
                    'admin_authenticated' => true,
                    'admin_username' => $username,
                    'admin_login_time' => time(),
                    'admin_oauth_provider' => $oauthPending['provider'],
                    'admin_oauth_name' => $oauthPending['name'],
                ]);
            } else {
                session([
                    'admin_authenticated' => true,
                    'admin_username' => $username,
                    'admin_login_time' => time(),
                ]);
            }

            SystemLog::info('2FA recovery code used', [
                'username' => $username,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('warning', 'Recovery code used. You have ' . count($twoFactor->fresh()->recovery_codes) . ' codes remaining.');
        }

        SystemLog::warning('Invalid 2FA recovery code', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);

        return back()->with('error', 'Invalid recovery code.')->withInput();
    }

    /**
     * Show 2FA setup page.
     */
    public function setup(Request $request): View
    {
        $username = session('admin_username');
        $twoFactor = TwoFactorAuth::getForUsername($username);

        if (!$twoFactor) {
            // Create new 2FA setup
            $secret = TwoFactorAuth::generateSecret();
            $recoveryCodes = TwoFactorAuth::generateRecoveryCodes();

            $twoFactor = TwoFactorAuth::create([
                'admin_username' => $username,
                'secret' => $secret,
                'recovery_codes' => $recoveryCodes,
                'enabled' => false,
            ]);
        }

        // Generate QR code
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            AdminSetting::getSiteName(),
            $username,
            $twoFactor->secret
        );

        // Use Google Chart API for QR code image
        $qrCodeImage = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrCodeUrl);

        return view('admin.2fa-setup', [
            'siteName' => AdminSetting::getSiteName(),
            'secret' => $twoFactor->secret,
            'qrCodeUrl' => $qrCodeImage,
            'recoveryCodes' => $twoFactor->recovery_codes,
            'enabled' => $twoFactor->isEnabled(),
        ]);
    }

    /**
     * Enable 2FA.
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $username = session('admin_username');
        $twoFactor = TwoFactorAuth::getForUsername($username);

        if (!$twoFactor) {
            return redirect()->route('admin.2fa.setup');
        }

        // Verify the code before enabling
        if (!$twoFactor->verifyCode($request->get('code'))) {
            return back()->with('error', 'Invalid verification code. 2FA was not enabled.');
        }

        $twoFactor->enable();

        SystemLog::info('2FA enabled', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.settings.security')
            ->with('success', 'Two-factor authentication has been enabled.');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $username = session('admin_username');

        // Verify current password
        $storedPasswordHash = AdminSetting::get('admin_password');
        if (!$storedPasswordHash || !\Illuminate\Support\Facades\Hash::check($request->get('password'), $storedPasswordHash)) {
            return back()->with('error', 'Invalid password.');
        }

        $twoFactor = TwoFactorAuth::getForUsername($username);
        if ($twoFactor) {
            $twoFactor->disable();
        }

        SystemLog::info('2FA disabled', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.settings.security')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $username = session('admin_username');

        // Verify current password
        $storedPasswordHash = AdminSetting::get('admin_password');
        if (!$storedPasswordHash || !\Illuminate\Support\Facades\Hash::check($request->get('password'), $storedPasswordHash)) {
            return back()->with('error', 'Invalid password.');
        }

        $twoFactor = TwoFactorAuth::getForUsername($username);
        if ($twoFactor) {
            $twoFactor->recovery_codes = TwoFactorAuth::generateRecoveryCodes();
            $twoFactor->save();
        }

        SystemLog::info('2FA recovery codes regenerated', [
            'username' => $username,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.2fa.setup')
            ->with('success', 'New recovery codes have been generated.');
    }
}
