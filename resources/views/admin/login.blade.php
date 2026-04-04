@extends('layout', ['siteName' => $siteName])

@section('content')
<div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-white text-center mb-6">
        🔐 Admin Login
    </h1>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-500/20 border border-red-500 rounded-lg text-red-200 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.authenticate') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-gray-300 text-sm mb-2">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus
                class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter your username">
        </div>

        <div>
            <label class="block text-gray-300 text-sm mb-2">Password</label>
            <input type="password" name="password" required
                class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter your password">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
            Login
        </button>
    </form>

    <p class="text-center text-gray-500 text-xs mt-6">
        Default: admin / admin123 (set ADMIN_USERNAME and ADMIN_PASSWORD in .env)
    </p>
</div>
@endsection
