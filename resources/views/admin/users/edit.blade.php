@extends('admin.layout')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Edit User: {{ $user->name }}</h2>
</div>

<div class="bg-gray-800 rounded-lg p-6 max-w-lg">
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="block text-gray-200 mb-2">Name</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-gray-200 mb-2">Email</label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-gray-200 mb-2">New Password (leave blank to keep current)</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-gray-200 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-gray-200 mb-2">Roles</label>
                @foreach($roles as $role => $label)
                    <label class="flex items-center text-gray-300 mb-2">
                        <input type="checkbox" name="roles[]" value="{{ $role }}"
                               class="mr-2" {{ in_array($role, $userRoles) ? 'checked' : '' }}>
                        {{ $role }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4 mt-6">
            <a href="{{ route('admin.users.index') }}" class="flex-1 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg text-center">
                Cancel
            </a>
            <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection