<?php

use App\Http\Controllers\SetupController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemController;
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
});
