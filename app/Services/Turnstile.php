<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Turnstile
{
    public static function siteKey(): string
    {
        return trim((string) AdminSetting::get('turnstile_site_key', ''));
    }

    public static function secretKey(): string
    {
        return trim((string) AdminSetting::get('turnstile_secret_key', ''));
    }

    public static function enabled(): bool
    {
        return self::siteKey() !== '' && self::secretKey() !== '';
    }

    public static function verify(Request $request): bool
    {
        if (! self::enabled()) {
            return true;
        }

        $token = (string) $request->input('cf-turnstile-response', '');
        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => self::secretKey(),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (\Throwable $e) {
            return false;
        }

        return (bool) $response->json('success');
    }

    public static function rejectUnlessValid(Request $request)
    {
        if (self::verify($request)) {
            return null;
        }

        return back()
            ->with('error', 'Please complete the verification check.')
            ->withInput($request->except('password', 'password_confirmation', 'cf-turnstile-response'));
    }
}
