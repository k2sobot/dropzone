<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $maxFileSize = $user
            ? $user->max_file_size
            : AdminSetting::getMaxFileSize();

        return view('upload-content', [
            'siteName' => AdminSetting::getSiteName(),
            'maxFileSize' => $maxFileSize,
            'user' => $user,
        ]);
    }

    /**
     * Handle file upload.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $maxFileSize = $user
            ? $user->max_file_size
            : AdminSetting::getMaxFileSize();

        $request->validate([
            'file' => "required|file|max:{$maxFileSize}",
        ]);

        // Check user-specific upload limits
        $canUpload = $this->fileService->canUpload(
            $request->file('file')->getSize(),
            $user
        );

        if (!$canUpload['allowed']) {
            return back()->with('error', $canUpload['reason']);
        }

        // Rate limiting - 5 uploads per minute per IP
        $key = 'upload:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Too many uploads. Please wait a minute and try again.');
        }

        RateLimiter::hit($key, 60);

        // Store the file
        $upload = $this->fileService->store(
            $request->file('file'),
            $request->ip(),
            $user
        );

        return redirect()->route('home')->with([
            'success' => 'File uploaded successfully!',
            'download_url' => $upload->download_url,
        ]);
    }
}