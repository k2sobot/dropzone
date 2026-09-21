@extends('layout', ['siteName' => $siteName])

@section('content')
<div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-xl p-5 sm:p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-white text-center mb-6">Choose a new password</h1>

    <form action="{{ route('admin.password.update', $token) }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-gray-300 text-sm mb-2">New password</label>
            <input type="password" name="password" required minlength="8" autocomplete="new-password"
                class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-gray-300 text-sm mb-2">Confirm password</label>
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition min-h-12">
            Update password
        </button>
    </form>
</div>
@endsection
