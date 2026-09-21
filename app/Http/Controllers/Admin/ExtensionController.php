<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExtensionLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ExtensionController extends Controller
{
    public function index(): View
    {
        $installed = $this->getInstalledExtensions();
        $installedPackages = array_column($installed, 'composer_name');

        $modules = [];
        foreach (ExtensionLicense::catalog() as $package => $meta) {
            $licensed = ExtensionLicense::isActive($package);
            $updatesUntil = ExtensionLicense::updatesUntil($package);
            $modules[] = array_merge($meta, [
                'package' => $package,
                'installed' => in_array($package, $installedPackages, true),
                'licensed' => $licensed,
                'updates_until' => $updatesUntil,
            ]);
        }

        return view('admin.extensions.index', [
            'installed' => $installed,
            'modules' => $modules,
        ]);
    }

    public function activate(Request $request): RedirectResponse
    {
        $request->validate([
            'package' => 'required|string',
            'license_key' => 'required|string|max:500',
        ]);

        $package = $request->string('package')->toString();
        if (! isset(ExtensionLicense::catalog()[$package])) {
            return back()->with('error', 'Unknown module.');
        }

        if (! ExtensionLicense::activate($package, $request->string('license_key')->toString())) {
            return back()->with('error', 'Invalid license key for this site.');
        }

        return back()->with('success', 'License activated for '.$package.'.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'package' => 'required|string',
        ]);

        $package = $request->string('package')->toString();
        ExtensionLicense::deactivate($package);

        return back()->with('success', 'License removed for '.$package.'.');
    }

    protected function getInstalledExtensions(): array
    {
        $extensionsPath = base_path('extensions');

        if (! is_dir($extensionsPath)) {
            return [];
        }

        $extensions = [];

        foreach (File::directories($extensionsPath) as $dir) {
            $name = basename($dir);
            $composerPath = "{$dir}/composer.json";

            if (! file_exists($composerPath)) {
                continue;
            }

            $composer = json_decode((string) file_get_contents($composerPath), true) ?: [];
            $package = $composer['name'] ?? $name;
            $extra = $composer['extra']['dropzone'] ?? [];

            $extensions[] = [
                'directory' => $name,
                'name' => $extra['name'] ?? $composer['description'] ?? $name,
                'description' => $extra['description'] ?? $composer['description'] ?? '',
                'type' => ! empty($extra['storage_driver']) ? 'Storage' : 'Feature',
                'licensed' => ExtensionLicense::isActive($package),
                'composer_name' => $package,
            ];
        }

        return $extensions;
    }
}
