<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminSetting;
use App\Services\FileService;
use Illuminate\View\View;

class DashboardController
{
    public function __construct(
        protected FileService $fileService
    ) {}

    public function __invoke(): View
    {
        $stats = $this->fileService->getStats();

        return view('admin.dashboard', [
            'stats' => $stats,
            'siteName' => AdminSetting::getSiteName(),
        ]);
    }
}
