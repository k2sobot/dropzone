@extends('admin.layout')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-white">Users</h2>
    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
        + Add User
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-300">
        {{ session('success') }}
    </div>
@endif

<div class="bg-gray-800 rounded-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Upload Limit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Storage</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($users as $user)
                <tr class="hover:bg-gray-700/50 {{ $user->is_active ? '' : 'opacity-60' }}">
                    <td class="px-6 py-4">
                        <div class="text-white font-medium">{{ $user->name }}</div>
                        <div class="text-gray-400 text-sm">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @foreach($user->roles as $role)
                            <span class="px-2 py-1 text-xs rounded bg-blue-500/20 text-blue-400">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-400">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-500/20 text-red-400">Disabled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-300 text-sm">
                        @if($user->getAttributes()['max_file_size'])
                            {{ (int)($user->max_file_size / 1048576) }}MB
                        @else
                            <span class="text-gray-500">Default</span>
                        @endif
                        @if($user->getAttributes()['max_uploads_per_day'])
                            <br><span class="text-gray-500">{{ $user->max_uploads_per_day }}/day</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-300 text-sm">
                        @if($user->hasStorageQuota())
                            <div class="w-24">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>{{ number_format($user->storage_used_bytes / 1073741824, 1) }}GB</span>
                                    <span>{{ number_format($user->storage_quota_bytes / 1073741824, 0) }}GB</span>
                                </div>
                                <div class="w-full bg-gray-600 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $user->getStorageUsagePercent() > 90 ? 'bg-red-500' : ($user->getStorageUsagePercent() > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                         style="width: {{ min(100, $user->getStorageUsagePercent()) }}%"></div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-500">Unlimited</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-400 hover:text-blue-300">
                            Edit
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                        No users found. <a href="{{ route('admin.users.create') }}" class="text-blue-400">Create one</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $users->links() }}
@endsection
