@extends('admin.layout')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Create User</h2>
</div>

<div class="bg-gray-800 rounded-lg p-6 max-w-lg">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="space-y-4">
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

            <div>
                <label class="block text-gray-200 mb-2">Roles</label>
                @foreach($roles as $role => $label)
                    <label class="flex items-center text-gray-300 mb-2">
                        <input type="checkbox" name="roles[]" value="{{ $role }}"
                               class="mr-2" {{ old('roles') && in_array($role, old('roles')) ? 'checked' : '' }}>
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
                Create User
            </button>
        </div>
    </form>
</div>
@endsection