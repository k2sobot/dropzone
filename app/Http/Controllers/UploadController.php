<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class UploadController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Show the upload form.
     */
    public function index()
    {
        return view('upload', [
            'siteName' => AdminSetting::getSiteName(),
            'maxFileSize' => AdminSetting::getMaxFileSize(),
        ]);
    }

    /**
     * Handle file upload.
     */
    public function store(Request $request)
    {
        $maxFileSize = AdminSetting::getMaxFileSize();

        $request->validate([
            'file' => "required|file|max:{$maxFileSize}",
        ]);

        // Rate limiting - 5 uploads per minute per IP
        $key = 'upload:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Too many uploads. Please wait a minute and try again.');
        }

        RateLimiter::hit($key, 60);

        // Store the file
        $upload = $this->fileService->store(
            $request->file('file'),
            $request->ip()
        );

        return redirect()->route('home')->with([
            'success' => 'File uploaded successfully!',
            'download_url' => $upload->download_url,
        ]);
    }
}