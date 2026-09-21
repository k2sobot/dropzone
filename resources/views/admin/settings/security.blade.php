@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <h2 class="text-2xl font-bold text-white">Security Settings</h2>
    <a href="{{ route('admin.dashboard') }}" class="text-gray-300 hover:text-white text-sm">Back to Dashboard</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Two-factor authentication</h3>
            @if($twoFactorEnabled ?? false)
                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-400">Enabled</span>
            @else
                <span class="px-2 py-1 text-xs rounded bg-yellow-500/20 text-yellow-400">Disabled</span>
            @endif
        </div>
        @if($twoFactorEnabled ?? false)
            <p class="text-gray-300 text-sm mb-4">Your account is protected with 2FA. Recovery codes remaining: <strong>{{ $recoveryCodesCount ?? 0 }}</strong></p>
            <a href="{{ route('admin.2fa.setup') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Manage 2FA</a>
        @else
            <p class="text-gray-400 text-sm mb-4">Add an authenticator app for an extra sign-in step.</p>
            <a href="{{ route('admin.2fa.setup') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Set up 2FA</a>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-white mb-4">OAuth providers</h3>
        @if(empty($enabledProviders))
            <p class="text-gray-400 text-sm">Not configured. Add Google/GitHub client IDs to <code class="text-gray-300">.env</code>.</p>
        @else
            <div class="space-y-3 text-sm text-gray-300">
                @foreach($enabledProviders as $provider)
                    <div class="flex items-center justify-between">
                        <span class="capitalize">{{ $provider }}</span>
                        @php $connected = $provider === 'google' ? ($googleConnected ?? false) : ($githubConnected ?? false); @endphp
                        @if($connected)
                            <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-400">Connected</span>
                        @else
                            <a href="{{ route('admin.oauth.redirect', $provider) }}" class="text-blue-400 hover:text-blue-300">Connect</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-gray-800 rounded-lg p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Admin account</h3>
        <form method="POST" action="{{ route('admin.settings.security.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-gray-300 text-sm mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username', $currentUsername) }}" required autocomplete="username"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base">
            </div>

            <div>
                <label class="block text-gray-300 text-sm mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $currentEmail ?? '') }}" required autocomplete="email"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base"
                    placeholder="you@example.com">
                <p class="text-gray-500 text-xs mt-1">Used for lost-password resets.</p>
            </div>

            <div>
                <label class="block text-gray-300 text-sm mb-2">New password</label>
                <input type="password" name="password" minlength="8" autocomplete="new-password"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base"
                    placeholder="Leave blank to keep current">
            </div>

            <div>
                <label class="block text-gray-300 text-sm mb-2">Confirm new password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base">
            </div>

            <div>
                <label class="block text-gray-300 text-sm mb-2">Current password</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-3 text-base">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg min-h-12">
                Save account
            </button>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Current session</h3>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Logged in as</dt><dd class="text-white break-all">{{ session('admin_username') }}</dd></div>
            @if(session('admin_oauth_provider'))
                <div class="flex justify-between gap-4"><dt class="text-gray-400">OAuth</dt><dd class="text-white">{{ ucfirst(session('admin_oauth_provider')) }}</dd></div>
            @endif
            <div class="flex justify-between gap-4"><dt class="text-gray-400">Login</dt><dd class="text-white text-right">{{ \Carbon\Carbon::createFromTimestamp(session('admin_login_time'))->format('M j, Y H:i') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-400">IP</dt><dd class="text-white">{{ request()->ip() }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="w-full border border-red-500 text-red-400 hover:bg-red-500/10 py-3 rounded-lg min-h-12">Log out</button>
        </form>
    </div>
</div>
@endsection
