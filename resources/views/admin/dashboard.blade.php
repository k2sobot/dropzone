@extends('admin.layout', ['siteName' => $siteName])

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="text-gray-400 text-sm">Total Files</div>
        <div class="text-3xl font-bold text-white">{{ $stats['total_files'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="text-gray-400 text-sm">Active Files</div>
        <div class="text-3xl font-bold text-green-400">{{ $stats['active_files'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="text-gray-400 text-sm">Downloaded</div>
        <div class="text-3xl font-bold text-blue-400">{{ $stats['downloaded_files'] }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="text-gray-400 text-sm">Expired</div>
        <div class="text-3xl font-bold text-red-400">{{ $stats['expired_files'] }}</div>
    </div>
</div>

<div class="bg-gray-800 rounded-lg p-6">
    <h2 class="text-xl font-semibold text-white mb-4">Total Storage Used</h2>
    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_size'] / 1048576, 2) }} MB</p>
</div>

<div class="mt-8 bg-gray-800 rounded-lg p-6">
    <h2 class="text-xl font-semibold text-white mb-4">Quick Actions</h2>
    <div class="flex space-x-4">
        <a href="{{ route('admin.uploads.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Manage Uploads
        </a>
        <a href="{{ route('admin.settings.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            Settings
        </a>
    </div>
</div>
@endsection