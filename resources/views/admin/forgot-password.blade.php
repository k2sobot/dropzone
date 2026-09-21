@extends('layout', ['siteName' => $siteName])

@section('content')
<div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-xl p-5 sm:p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-white text-center mb-2">Reset password</h1>
    <p class="text-gray-400 text-sm text-center mb-6">Enter the admin email from setup. We'll send a reset link if it matches.</p>

    <form action="{{ route('admin.password.email') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-gray-300 text-sm mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="you@example.com">
        </div>
        @include('partials.turnstile')

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition min-h-12">
            Send reset link
        </button>
    </form>

    <p class="text-center text-sm mt-4">
        <a href="{{ route('admin.login') }}" class="text-gray-400 hover:text-white">Back to login</a>
    </p>
</div>
@endsection
