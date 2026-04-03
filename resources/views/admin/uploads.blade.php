@extends('admin.layout', ['siteName' => $siteName])

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-white">Uploads</h1>
    <div class="flex space-x-2">
        <a href="{{ route('admin.uploads.index') }}" 
           class="px-3 py-2 rounded {{ request('filter') ? 'text-gray-400' : 'bg-blue-600 text-white' }}">All</a>
        <a href="{{ route('admin.uploads.index', ['filter' => 'active']) }}"
           class="px-3 py-2 rounded {{ request('filter') === 'active' ? 'bg-green-600 text-white' : 'text-gray-400' }}">Active</a>
        <a href="{{ route('admin.uploads.index', ['filter' => 'downloaded']) }}"
           class="px-3 py-2 rounded {{ request('filter') === 'downloaded' ? 'bg-blue-600 text-white' : 'text-gray-400' }}">Downloaded</a>
        <a href="{{ route('admin.uploads.index', ['filter' => 'expired']) }}"
           class="px-3 py-2 rounded {{ request('filter') === 'expired' ? 'bg-red-600 text-white' : 'text-gray-400' }}">Expired</a>
    </div>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">File</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Expires</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($uploads as $upload)
            <tr class="hover:bg-gray-700/50">
                <td class="px-6 py-4">
                    <div class="text-white">{{ $upload->filename }}</div>
                    <div class="text-gray-500 text-xs">{{ $upload->id }}</div>
                </td>
                <td class="px-6 py-4 text-gray-300">{{ $upload->human_size }}</td>
                <td class="px-6 py-4">
                    @if($upload->isDownloaded())
                        <span class="px-2 py-1 bg-blue-600/20 text-blue-400 rounded text-xs">Downloaded</span>
                    @elseif($upload->isExpired())
                        <span class="px-2 py-1 bg-red-600/20 text-red-400 rounded text-xs">Expired</span>
                    @else
                        <span class="px-2 py-1 bg-green-600/20 text-green-400 rounded text-xs">Active</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-300">{{ $upload->expires_at->diffForHumans() }}</td>
                <td class="px-6 py-4">
                    <form action="{{ route('admin.uploads.destroy', $upload->id) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No uploads found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $uploads->links() }}
@endsection