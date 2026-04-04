@extends('admin.layout')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Create User</h2>
</div>

<div class="bg-gray-800 rounded-lg p-6 max-w-2xl">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            {{-- Account Info --}}
            <div class="border-b border-gray-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Account Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-200 mb-2">Name</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>

                    <div>
                        <label class="block text-gray-200 mb-2">Email</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>

                    <div>
                        <label class="block text-gray-200 mb-2">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>

                    <div>
                        <label class="block text-gray-200 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-200 mb-2">Roles</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($roles as $role => $label)
                                <label class="flex items-center text-gray-300">
                                    <input type="checkbox" name="roles[]" value="{{ $role }}"
                                           class="mr-2" {{ old('roles') && in_array($role, old('roles')) ? 'checked' : '' }}>
                                    <span class="px-2 py-1 bg-gray-700 rounded text-sm">{{ $role }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                            <span>Active (user can log in)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Upload Settings --}}
            <div class="border-b border-gray-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Upload Settings</h3>
                <p class="text-gray-400 text-sm mb-4">Leave blank to use global defaults. Global max file size: {{ $globalMaxFileSize }}MB</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-200 mb-2">Max File Size (MB)</label>
                        <input type="number" name="max_file_size_mb" min="1" max="1024" step="1"
                               value="{{ old('max_file_size_mb') }}"
                               placeholder="Global default: {{ $globalMaxFileSize }}MB"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500">
                    </div>

                    <div>
                        <label class="block text-gray-200 mb-2">Max Uploads Per Day</label>
                        <input type="number" name="max_uploads_per_day" min="0" step="1"
                               value="{{ old('max_uploads_per_day') }}"
                               placeholder="0 = Unlimited"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500">
                    </div>

                    <div>
                        <label class="block text-gray-200 mb-2">Default Expiration (hours)</label>
                        <input type="number" name="default_expiration" min="1" max="720" step="1"
                               value="{{ old('default_expiration') }}"
                               placeholder="Global default: {{ $globalDefaultExpiration }}h"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500">
                    </div>
                </div>
            </div>

            {{-- Storage Quota --}}
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Storage Quota</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-200 mb-2">Storage Quota (GB)</label>
                        <input type="number" name="storage_quota_gb" min="0" step="0.1"
                               value="{{ old('storage_quota_gb') }}"
                               placeholder="Leave blank for unlimited"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-4 mt-6">
            <a href="{{ route('admin.users.index') }}" class="flex-1 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg text-center">
                Cancel
            </a>
            <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Create User
            </button>
        </div>
    </form>
</div>
@endsection
