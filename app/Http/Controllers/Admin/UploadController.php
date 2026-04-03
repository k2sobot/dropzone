<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Models\Upload;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadController
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * List all uploads.
     */
    public function index(Request $request): View
    {
        $query = Upload::query()->latest();

        // Filter by status
        if ($request->get('filter') === 'active') {
            $query->whereNull('downloaded_at')
                ->where('expires_at', '>', now());
        } elseif ($request->get('filter') === 'downloaded') {
            $query->whereNotNull('downloaded_at');
        } elseif ($request->get('filter') === 'expired') {
            $query->whereNull('downloaded_at')
                ->where('expires_at', '<', now());
        }

        $uploads = $query->paginate(20);

        return view('admin.uploads', [
            'uploads' => $uploads,
            'siteName' => AdminSetting::getSiteName(),
        ]);
    }

    /**
     * Delete an upload.
     */
    public function destroy(string $id): RedirectResponse
    {
        $upload = Upload::findOrFail($id);

        $this->fileService->delete($upload);

        return back()->with('success', 'Upload deleted successfully.');
    }
}
