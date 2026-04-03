<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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

Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Uploads management
    Route::get('/uploads', [AdminUploadController::class, 'index'])->name('uploads.index');
    Route::delete('/uploads/{id}', [AdminUploadController::class, 'destroy'])->name('uploads.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/background', [SettingController::class, 'uploadBackground'])->name('settings.background');
});
