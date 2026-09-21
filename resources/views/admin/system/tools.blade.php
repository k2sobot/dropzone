@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <h2 class="text-2xl font-bold text-white">System Tools</h2>
    <div class="flex gap-2">
        <a href="{{ route('admin.system.status') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Status</a>
        <a href="{{ route('admin.system.logs') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Logs</a>
    </div>
</div>

@if($output)
<div class="bg-gray-800 rounded-lg p-4 mb-6">
    <h3 class="text-white font-semibold mb-2">Output</h3>
    <pre class="bg-black text-green-300 p-3 rounded text-xs overflow-auto max-h-72">{{ $output }}</pre>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @php
        $tools = [
            ['clear_cache', 'Clear cache', 'Config, views, and application cache.', 'Clear'],
            ['clear_logs', 'Clear logs', 'Delete system log rows.', 'Clear logs', true],
            ['storage_link', 'Storage link', 'Link public/storage to storage/app/public (backgrounds only).', 'Create link'],
            ['migrate', 'Migrations', 'Run pending database migrations.', 'Migrate', true],
            ['optimize', 'Optimize', 'Cache config, routes, and views.', 'Optimize'],
            ['run_cron', 'Run cron', 'Trigger scheduled cleanup now.', 'Run'],
        ];
    @endphp
    @foreach($tools as $tool)
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-white font-semibold mb-2">{{ $tool[1] }}</h3>
            <p class="text-gray-400 text-sm mb-4">{{ $tool[2] }}</p>
            <form method="POST" action="{{ route('admin.system.tools.execute') }}" @if(!empty($tool[4])) onsubmit="return confirm('{{ $tool[1] }}?')" @endif>
                @csrf
                <input type="hidden" name="action" value="{{ $tool[0] }}">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">{{ $tool[3] }}</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
