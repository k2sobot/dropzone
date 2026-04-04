@extends('admin.layout')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-md mx-auto mt-16">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Enter the verification code from your authenticator app.</p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <p class="text-red-800 dark:text-red-200 text-sm">{{ session('error') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.2fa.check') }}" class="space-y-6">
            @csrf

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Verification Code
                </label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    class="w-full text-center text-2xl tracking-widest px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="000000"
                    required
                    autofocus
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors"
            >
                Verify
            </button>
        </form>

        @if($hasRecoveryCodes)
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">
                Lost your device? Use a recovery code.
            </p>

            <details class="group">
                <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 hover:underline text-center block">
                    Use recovery code
                </summary>

                <form method="POST" action="{{ route('admin.2fa.recover') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="recovery_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Recovery Code
                        </label>
                        <input
                            type="text"
                            name="recovery_code"
                            id="recovery_code"
                            class="w-full text-center tracking-wider px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="XXXXXXXX-XXXXXXXX"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                        Use Recovery Code
                    </button>
                </form>
            </details>
        </div>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('admin.login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                ← Back to login
            </a>
        </div>
    </div>
</div>
@endsection
