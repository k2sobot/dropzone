<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\FileService;

class DownloadController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Show the download landing page.
     */
    public function show(string $uuid)
    {
        $upload = \App\Models\Upload::find($uuid);

        if (! $upload) {
            abort(404, 'File not found');
        }

        if ($upload->isDownloaded()) {
            return view('download', [
                'upload' => null,
                'message' => 'This file has already been downloaded.',
                'siteName' => AdminSetting::getSiteName(),
                'backgroundImage' => AdminSetting::getBackgroundImage(),
            ]);
        }

        if ($upload->isExpired()) {
            return view('download', [
                'upload' => null,
                'message' => 'This file has expired.',
                'siteName' => AdminSetting::getSiteName(),
                'backgroundImage' => AdminSetting::getBackgroundImage(),
            ]);
        }

        return view('download', [
            'upload' => $upload,
            'message' => null,
            'siteName' => AdminSetting::getSiteName(),
            'backgroundImage' => AdminSetting::getBackgroundImage(),
        ]);
    }

    /**
     * Download the file.
     */
    public function download(string $uuid)
    {
        $result = $this->fileService->download($uuid);

        if (! $result) {
            abort(404, 'File not found or no longer available');
        }

        $upload = $result['upload'];

        return response($result['content'])
            ->header('Content-Type', $upload->mime_type)
            ->header('Content-Disposition', 'attachment; filename="'.$upload->filename.'"');
    }
}
