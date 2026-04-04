<?php

use App\Http\Controllers\SetupController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\OAuthController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Models\AdminSetting;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Setup wizard (shown if not set up)
Route::middleware(['web', 'setup.not-complete'])->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup');
    Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
});

// Public routes
Route::get('/', [UploadController::class, 'index'])->name('home');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
Route::get('/d/{uuid}', [DownloadController::class, 'show'])->name('download.show');
Route::get('/d/{uuid}/download', [DownloadController::class, 'download'])->name('download.file');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin login (public)
Route::get('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'authenticate'])->name('admin.authenticate');

// OAuth routes (public)
Route::get('/admin/oauth/{provider}', [OAuthController::class, 'redirect'])->name('admin.oauth.redirect');
Route::get('/admin/oauth/{provider}/callback', [OAuthController::class, 'callback'])->name('admin.oauth.callback');

// 2FA routes (public, requires pending session)
Route::get('/admin/2fa', [TwoFactorController::class, 'verify'])->name('admin.2fa.verify');
Route::post('/admin/2fa', [TwoFactorController::class, 'check'])->name('admin.2fa.check');
Route::post('/admin/2fa/recover', [TwoFactorController::class, 'recover'])->name('admin.2fa.recover');

Route::prefix('admin')->name('admin.')->middleware(['web', 'admin'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Users management
    Route::resource('users', UserController::class);

    // Uploads management
    Route::get('/uploads', [AdminUploadController::class, 'index'])->name('uploads.index');
    Route::delete('/uploads/{id}', [AdminUploadController::class, 'destroy'])->name('uploads.destroy');

    // Extension management
    Route::get('/extensions', [ExtensionController::class, 'index'])->name('extensions.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/background', [SettingController::class, 'uploadBackground'])->name('settings.background');
    Route::get('/settings/security', [SettingController::class, 'security'])->name('settings.security');
    Route::post('/settings/security', [SettingController::class, 'updateSecurity'])->name('settings.security.update');

    // System
    Route::get('/system', [SystemController::class, 'status'])->name('system.status');
    Route::get('/system/logs', [SystemController::class, 'logs'])->name('system.logs');
    Route::post('/system/logs/clear', [SystemController::class, 'logsClear'])->name('system.logs.clear');
    Route::get('/system/tools', [SystemController::class, 'tools'])->name('system.tools');
    Route::post('/system/tools/execute', [SystemController::class, 'toolsExecute'])->name('system.tools.execute');

    // 2FA management (requires admin session)
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/regenerate', [TwoFactorController::class, 'regenerate'])->name('2fa.regenerate');

    // OAuth management (requires admin session)
    Route::get('/oauth/manage', [OAuthController::class, 'manage'])->name('oauth.manage');
    Route::post('/oauth/{provider}/disconnect', [OAuthController::class, 'disconnect'])->name('oauth.disconnect');
    Route::post('/oauth/{provider}/grant', [OAuthController::class, 'grantAdmin'])->name('oauth.grant');
});
