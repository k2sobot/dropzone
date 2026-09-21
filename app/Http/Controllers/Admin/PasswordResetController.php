<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Services\AdminSession;
use App\Services\Turnstile;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController
{
    public function request(): View
    {
        return view('admin.forgot-password', [
            'siteName' => AdminSetting::getSiteName(),
        ]);
    }

    public function email(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if ($failed = Turnstile::rejectUnlessValid($request)) {
            return $failed;
        }

        $key = 'admin-forgot:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->with('error', "Too many reset attempts. Try again in {$seconds} seconds.");
        }
        RateLimiter::hit($key, 60);

        $submitted = strtolower(trim($request->email));
        $stored = strtolower((string) AdminSetting::get('admin_email', ''));

        if ($stored !== '' && hash_equals($stored, $submitted)) {
            $token = Str::random(64);
            AdminSetting::set('admin_password_reset_hash', Hash::make($token));
            AdminSetting::set('admin_password_reset_expires', now()->addHour()->toIso8601String());

            $url = url('/admin/reset-password/'.$token);

            try {
                Mail::raw(
                    "A password reset was requested for your Dropzone admin account.\n\nReset link (valid for 1 hour):\n{$url}\n\nIf you did not request this, you can ignore this email.",
                    function ($message) use ($submitted) {
                        $message->to($submitted)->subject('Reset your Dropzone password');
                    }
                );
            } catch (\Throwable $e) {
                SystemLog::error('Password reset email failed', [
                    'error' => $e->getMessage(),
                ]);

                return back()->with('error', 'Could not send the reset email. Check mail settings.');
            }

            SystemLog::info('Admin password reset email sent', [
                'email' => $submitted,
                'ip' => $request->ip(),
            ]);
        }

        return back()->with('success', 'If that email is registered, a reset link has been sent.');
    }

    public function show(string $token): View|RedirectResponse
    {
        if (! $this->tokenIsValid($token)) {
            return redirect()->route('admin.login')->with('error', 'That reset link is invalid or has expired.');
        }

        return view('admin.reset-password', [
            'siteName' => AdminSetting::getSiteName(),
            'token' => $token,
        ]);
    }

    public function update(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        if (! $this->tokenIsValid($token)) {
            return redirect()->route('admin.login')->with('error', 'That reset link is invalid or has expired.');
        }

        AdminSetting::set('admin_password', Hash::make($request->password));
        AdminSession::invalidateRemember();
        AdminSetting::set('admin_password_reset_hash', null);
        AdminSetting::set('admin_password_reset_expires', null);

        SystemLog::info('Admin password reset completed', [
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.login')->with('success', 'Password updated. You can sign in now.');
    }

    protected function tokenIsValid(string $token): bool
    {
        $hash = AdminSetting::get('admin_password_reset_hash');
        $expires = AdminSetting::get('admin_password_reset_expires');

        if (! $hash || ! $expires) {
            return false;
        }

        try {
            if (now()->greaterThan(\Carbon\Carbon::parse($expires))) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return Hash::check($token, $hash);
    }
}
