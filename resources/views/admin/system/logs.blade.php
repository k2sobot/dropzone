@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <h2 class="text-2xl font-bold text-white">System Logs</h2>
    <div class="flex gap-2">
        <a href="{{ route('admin.system.status') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Status</a>
        <a href="{{ route('admin.system.tools') }}" class="px-3 py-2 rounded-lg bg-gray-700 text-white text-sm">Tools</a>
    </div>
</div>

<form method="GET" class="bg-gray-800 rounded-lg p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <select name="level" class="bg-gray-700 text-white rounded-lg px-3 py-2 text-base">
        @foreach($levels as $value => $label)
            <option value="{{ $value }}" {{ $level === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <input type="text" name="search" value="{{ $search }}" placeholder="Search logs..." class="sm:col-span-2 bg-gray-700 text-white rounded-lg px-3 py-2 text-base">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-2">Filter</button>
</form>

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
        <h3 class="text-white font-semibold">Entries</h3>
        <form method="POST" action="{{ route('admin.system.logs.clear') }}" onsubmit="return confirm('Clear all logs?')">
            @csrf
            <input type="hidden" name="level" value="{{ $level }}">
            <button type="submit" class="text-red-400 text-sm hover:text-red-300">Clear</button>
        </form>
    </div>
    @if($logs->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[36rem]">
                <thead class="bg-gray-700 text-gray-300 text-left">
                    <tr>
                        <th class="px-4 py-2">Time</th>
                        <th class="px-4 py-2">Level</th>
                        <th class="px-4 py-2">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($logs as $log)
                        <tr>
                            <td class="px-4 py-2 text-gray-400 whitespace-nowrap">{{ $log->created_at->format('M j, H:i:s') }}</td>
                            <td class="px-4 py-2 text-white">{{ $log->level }}</td>
                            <td class="px-4 py-2 text-gray-300 break-all">{{ \Illuminate\Support\Str::limit($log->message, 160) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 text-gray-400">{{ $logs->links() }}</div>
    @else
        <p class="p-6 text-gray-400 text-center">No logs found.</p>
    @endif
</div>
@endsection
