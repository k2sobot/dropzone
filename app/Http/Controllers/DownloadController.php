<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\FileService;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DownloadController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    public function show(string $uuid)
    {
        $upload = \App\Models\Upload::find($uuid);

        $payload = [
            'upload' => null,
            'message' => 'This file is no longer available.',
            'siteName' => AdminSetting::getSiteName(),
            'backgroundImage' => AdminSetting::getBackgroundImage(),
        ];

        if (! $upload || $upload->isDownloaded() || $upload->isExpired()) {
            abort_unless($upload, 404);

            return view('download', $payload);
        }

        return view('download', [
            'upload' => $upload,
            'message' => null,
            'siteName' => AdminSetting::getSiteName(),
            'backgroundImage' => AdminSetting::getBackgroundImage(),
        ]);
    }

    public function download(string $uuid)
    {
        $result = $this->fileService->download($uuid);

        if (! $result) {
            abort(404);
        }

        $upload = $result['upload'];

        if (isset($result['url'])) {
            return redirect($result['url']);
        }

        $fallback = preg_replace('/[^\x20-\x7E]/', '_', $upload->filename) ?: 'download';
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $upload->filename,
            $fallback
        );

        $unsafe = [
            'text/html',
            'application/xhtml+xml',
            'image/svg+xml',
            'text/xml',
            'application/xml',
            'application/javascript',
            'text/javascript',
            'application/x-httpd-php',
        ];
        $mime = $upload->mime_type ?: 'application/octet-stream';
        if (in_array(strtolower($mime), $unsafe, true) || str_ends_with(strtolower($upload->filename), '.html') || str_ends_with(strtolower($upload->filename), '.svg') || str_ends_with(strtolower($upload->filename), '.php')) {
            $mime = 'application/octet-stream';
        }

        return response($result['content'])
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', $disposition)
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }
}
