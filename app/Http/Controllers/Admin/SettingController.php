<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController
{
    /**
     * Show settings form.
     */
    public function index(): View
    {
        return view('admin.settings', [
            'settings' => [
                'site_name' => AdminSetting::get('site_name', 'Dropzone'),
                'app_url' => AdminSetting::get('app_url', config('app.url')),
                'max_file_size' => AdminSetting::get('max_file_size', 104857600),
                'max_file_size_mb' => (int) (AdminSetting::get('max_file_size', 104857600) / 1048576),
                'default_expiration' => AdminSetting::get('default_expiration', 24),
                'storage_driver' => AdminSetting::get('storage_driver', 'local'),
                
                // S3 settings
                's3_enabled' => AdminSetting::get('s3_enabled', false),
                's3_key' => AdminSetting::get('s3_key', ''),
                's3_secret' => AdminSetting::get('s3_secret', ''),
                's3_bucket' => AdminSetting::get('s3_bucket', ''),
                's3_region' => AdminSetting::get('s3_region', 'us-east-1'),
                's3_do_spaces' => AdminSetting::get('s3_do_spaces', false),
                's3_endpoint' => AdminSetting::get('s3_endpoint', ''),
            ],
            'backgroundImage' => AdminSetting::getBackgroundImage(),
            'siteName' => AdminSetting::getSiteName(),
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        // General settings
        if ($request->has('site_name')) {
            $request->validate([
                'site_name' => 'required|string|max:255',
            ]);
            AdminSetting::set('site_name', $request->get('site_name'));
        }

        if ($request->has('app_url')) {
            $request->validate([
                'app_url' => 'nullable|url|max:255',
            ]);
            AdminSetting::set('app_url', $request->get('app_url'));
            
            // Update .env APP_URL if provided
            if ($request->get('app_url')) {
                $this->updateEnvValue('APP_URL', $request->get('app_url'));
            }
        }

        if ($request->has('max_file_size_mb')) {
            $request->validate([
                'max_file_size_mb' => 'required|integer|min:1|max:1024',
            ]);
            $bytes = $request->get('max_file_size_mb') * 1048576;
            AdminSetting::set('max_file_size', $bytes);
        }

        if ($request->has('default_expiration')) {
            $request->validate([
                'default_expiration' => 'required|integer|min:1|max:720',
            ]);
            AdminSetting::set('default_expiration', $request->get('default_expiration'));
        }

        // Storage driver
        if ($request->has('storage_driver')) {
            $request->validate([
                'storage_driver' => 'required|in:local,s3',
            ]);
            AdminSetting::set('storage_driver', $request->get('storage_driver'));
            $this->updateEnvValue('STORAGE_DRIVER', $request->get('storage_driver'));
        }

        // Admin password
        if ($request->has('admin_password') && $request->filled('admin_password')) {
            $request->validate([
                'admin_password' => 'required|min:8|confirmed',
            ]);
            
            AdminSetting::set('admin_password', bcrypt($request->get('admin_password')));
            $this->updateEnvValue('ADMIN_PASSWORD', $request->get('admin_password'));
        }

        // S3 settings
        if ($request->has('s3_key') || $request->has('s3_enabled')) {
            $request->validate([
                's3_key' => 'nullable|string|max:255',
                's3_secret' => 'nullable|string|max:255',
                's3_bucket' => 'nullable|string|max:255',
                's3_region' => 'nullable|string|max:50',
                's3_endpoint' => 'nullable|url|max:255',
            ]);

            AdminSetting::set('s3_enabled', $request->boolean('s3_enabled'));
            AdminSetting::set('s3_key', $request->get('s3_key', ''));
            AdminSetting::set('s3_bucket', $request->get('s3_bucket', ''));
            AdminSetting::set('s3_region', $request->get('s3_region', 'us-east-1'));
            AdminSetting::set('s3_do_spaces', $request->boolean('s3_do_spaces'));
            AdminSetting::set('s3_endpoint', $request->get('s3_endpoint', ''));

            // Update secret only if provided (don't overwrite with empty)
            if ($request->filled('s3_secret')) {
                AdminSetting::set('s3_secret', $request->get('s3_secret'));
            }

            // Update .env for S3 configuration
            $this->updateEnvValue('S3_ENABLED', $request->boolean('s3_enabled') ? 'true' : 'false');
            $this->updateEnvValue('S3_KEY', $request->get('s3_key', ''));
            $this->updateEnvValue('S3_BUCKET', $request->get('s3_bucket', ''));
            $this->updateEnvValue('S3_REGION', $request->get('s3_region', 'us-east-1'));
            $this->updateEnvValue('S3_DO_SPACES', $request->boolean('s3_do_spaces') ? 'true' : 'false');
            $this->updateEnvValue('S3_ENDPOINT', $request->get('s3_endpoint', ''));
            
            if ($request->filled('s3_secret')) {
                $this->updateEnvValue('S3_SECRET', $request->get('s3_secret'));
            }

            // Clear config cache
            Artisan::call('config:clear');
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Upload background image.
     */
    public function uploadBackground(Request $request): RedirectResponse
    {
        $request->validate([
            'background' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        // Delete old background if exists
        $oldBackground = AdminSetting::get('background_image');
        if ($oldBackground && Storage::disk('public')->exists($oldBackground)) {
            Storage::disk('public')->delete($oldBackground);
        }

        // Store new background
        $path = $request->file('background')->store('backgrounds', 'public');

        AdminSetting::set('background_image', $path);

        return back()->with('success', 'Background image updated successfully.');
    }

    /**
     * Update a value in the .env file.
     */
    protected function updateEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        // Escape special characters in value
        $escapedValue = str_replace('"', '\\"', $value);
        
        // Quote the value if it contains spaces or special characters
        if (preg_match('/\s|[#\'"\\\\]/', $value)) {
            $escapedValue = '"' . $escapedValue . '"';
        }

        // Update existing key or add new one
        $pattern = "/^{$key}=.*$/m";
        $replacement = "{$key}={$escapedValue}";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= "\n{$key}={$escapedValue}";
        }

        file_put_contents($envPath, $content);
    }
}
