<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'max_file_size' => AdminSetting::get('max_file_size', 104857600),
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
        $request->validate([
            'site_name' => 'required|string|max:255',
            'max_file_size' => 'required|integer|min:1048576|max:1073741824', // 1MB to 1GB
        ]);

        AdminSetting::set('site_name', $request->get('site_name'));
        AdminSetting::set('max_file_size', $request->get('max_file_size'));

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
}
