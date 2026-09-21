@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <h2 class="text-2xl font-bold text-white">System Status</h2>
    <div class="flex gap-2">
        <a href="{{ route('admin.system.logs') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Logs</a>
        <a href="{{ route('admin.system.tools') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Tools</a>
    </div>
</div>

<div class="bg-gray-800 rounded-lg p-6 mb-6 overflow-x-auto">
    <h3 class="text-white font-semibold mb-4">Server</h3>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div class="flex justify-between gap-4"><dt class="text-gray-400">PHP</dt><dd class="text-white">{{ $server_info['php_version'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-400">Laravel</dt><dd class="text-white">{{ $server_info['laravel_version'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-400">Server</dt><dd class="text-white break-all">{{ $server_info['server_software'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-400">Database</dt><dd class="text-white">{{ $server_info['database'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-400">Cache</dt><dd class="text-white">{{ $server_info['cache_driver'] }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-400">Queue</dt><dd class="text-white">{{ $server_info['queue_driver'] }}</dd></div>
    </dl>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-800 rounded-lg p-4 text-center"><div class="text-2xl font-bold text-white">{{ number_format($uploads_count) }}</div><div class="text-gray-400 text-sm">Uploads</div></div>
    <div class="bg-gray-800 rounded-lg p-4 text-center"><div class="text-2xl font-bold text-white">{{ number_format($logs_count) }}</div><div class="text-gray-400 text-sm">{{ $error_count > 0 ? $error_count.' errors' : 'Log entries' }}</div></div>
    <div class="bg-gray-800 rounded-lg p-4 text-center"><div class="text-2xl font-bold text-white">{{ $storage_size }}</div><div class="text-gray-400 text-sm">Storage {{ $storage_writable ? '(writable)' : '(not writable)' }}</div></div>
    <div class="bg-gray-800 rounded-lg p-4 text-center"><div class="text-2xl font-bold text-white">{{ $cron_status === 'ok' ? 'OK' : ($cron_status === 'warning' ? 'WARN' : '?') }}</div><div class="text-gray-400 text-sm">Cron {{ $last_cron ? \Carbon\Carbon::createFromTimestamp($last_cron)->diffForHumans() : 'never' }}</div></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="text-white font-semibold mb-2">Disk</h3>
        <p class="text-gray-300 text-sm">Free: {{ $disk_free }}</p>
        <p class="text-gray-300 text-sm">Total: {{ $disk_total }}</p>
    </div>
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="text-white font-semibold mb-2">Memory</h3>
        <p class="text-gray-300 text-sm">Current: {{ $memory_usage }}</p>
        <p class="text-gray-300 text-sm">Peak: {{ $memory_peak }}</p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg p-6">
    <h3 class="text-white font-semibold mb-3">PHP extensions</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($extensions as $name => $loaded)
            <span class="text-xs px-2 py-1 rounded {{ $loaded ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">{{ $name }}</span>
        @endforeach
    </div>
</div>
@endsection
