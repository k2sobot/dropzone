@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<h1 class="text-2xl font-bold text-white mb-6">Extensions</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Installed</h2>
        @if(count($extensions) === 0)
            <p class="text-gray-400 mb-3">None installed yet.</p>
            <p class="text-gray-500 text-sm">Install from the server:</p>
            <code class="block bg-gray-900 text-gray-300 p-3 rounded mt-2 text-sm overflow-x-auto">php artisan dropzone:install-extension dropzone/s3</code>
        @else
            <div class="space-y-4">
                @foreach($extensions as $extension)
                    <div class="border border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between gap-3">
                            <div>
                                <h3 class="text-white font-medium">{{ $extension['name'] }}</h3>
                                <p class="text-gray-400 text-sm mt-1">{{ $extension['description'] }}</p>
                                <div class="flex gap-2 mt-2">
                                    <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">{{ $extension['type'] }}</span>
                                    @if($extension['enabled'])
                                        <span class="text-xs px-2 py-1 rounded bg-green-500/20 text-green-400">Enabled</span>
                                    @else
                                        <span class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-400">Disabled</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">{{ $extension['directory'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Available</h2>
        <p class="text-gray-400 text-sm mb-4">Official extensions. Enable from the CLI.</p>
        <div class="space-y-3">
            @foreach($available as $package => $ext)
                <div class="border border-gray-700 rounded-lg p-4">
                    <h3 class="text-white font-medium">{{ $ext['name'] }}</h3>
                    <p class="text-gray-400 text-sm mt-1">{{ $ext['description'] }}</p>
                    <code class="block text-xs text-gray-500 mt-2 break-all">{{ $package }}</code>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
