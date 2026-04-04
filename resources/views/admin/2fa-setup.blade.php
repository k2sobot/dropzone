@extends('admin.layout')

@section('title', 'Two-Factor Authentication Setup')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                @if($enabled)
                    2FA is currently <span class="text-green-600 dark:text-green-400 font-medium">enabled</span> for your account.
                @else
                    Set up two-factor authentication for enhanced security.
                @endif
            </p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <p class="text-red-800 dark:text-red-200 text-sm">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <p class="text-green-800 dark:text-green-200 text-sm">{{ session('success') }}</p>
        </div>
        @endif

        @if(!$enabled)
        <div class="space-y-8">
            <!-- Step 1: Install App -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Step 1: Install an Authenticator App
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Install an authenticator app on your phone:
                </p>
                <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• Google Authenticator (Android/iOS)</li>
                    <li>• Microsoft Authenticator (Android/iOS)</li>
                    <li>• Authy (Android/iOS/Desktop)</li>
                    <li>• 1Password, Bitwarden, or other password managers</li>
                </ul>
            </div>

            <!-- Step 2: Scan QR Code -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Step 2: Scan QR Code
                </h2>
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="bg-white p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" class="w-48 h-48">
                    </div>
                    <div class="text-center md:text-left">
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                            Or enter this code manually:
                        </p>
                        <code class="block bg-gray-100 dark:bg-gray-900 px-4 py-2 rounded text-sm font-mono break-all">
                            {{ $secret }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- Step 3: Recovery Codes -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Step 3: Save Recovery Codes
                </h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">
                    Store these recovery codes in a safe place. Each code can only be used once.
                </p>
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                    <code class="text-sm font-mono text-center py-1">{{ $code }}</code>
                    @endforeach
                </div>
            </div>

            <!-- Step 4: Verify -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Step 4: Verify Setup
                </h2>
                <form method="POST" action="{{ route('2fa.enable') }}" class="flex gap-4">
                    @csrf
                    <div class="flex-1">
                        <input
                            type="text"
                            name="code"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            class="w-full text-center text-xl tracking-widest px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter 6-digit code"
                            required
                        >
                    </div>
                    <button
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg transition-colors"
                    >
                        Enable 2FA
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="space-y-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-blue-800 dark:text-blue-200 text-sm">
                    <strong>Important:</strong> You have remaining <strong>{{ count($recoveryCodes) }}</strong> recovery codes.
                    @if(count($recoveryCodes) < 3)
                    Consider regenerating new codes.
                    @endif
                </p>
            </div>

            <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Remaining Recovery Codes</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                    <code class="text-sm font-mono text-center py-1">{{ $code }}</code>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-4">
                <form method="POST" action="{{ route('2fa.regenerate') }}" class="flex-1">
                    @csrf
                    <div class="flex gap-2">
                        <input
                            type="password"
                            name="password"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Current password"
                            required
                        >
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors"
                        >
                            Regenerate Codes
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3">Disable 2FA</h3>
                <form method="POST" action="{{ route('2fa.disable') }}">
                    @csrf
                    <div class="flex gap-2">
                        <input
                            type="password"
                            name="password"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter password to disable"
                            required
                        >
                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors"
                        >
                            Disable 2FA
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.settings.security') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                ← Back to Security Settings
            </a>
        </div>
    </div>
</div>
@endsection
