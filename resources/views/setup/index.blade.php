<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup - Dropzone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .setup-progress { counter-reset: step; }
        .setup-progress li { counter-increment: step; }
        .setup-progress li::before { content: counter(step); }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white">Dropzone</h1>
            <p class="text-gray-400 mt-2">Setup Wizard</p>
        </div>

        <!-- Progress Steps -->
        <div class="setup-progress flex justify-between mb-8 text-sm">
            @php
                $steps = [
                    1 => 'Requirements',
                    2 => 'Database',
                    3 => 'Admin',
                    4 => 'Complete',
                ];
            @endphp
            <ul class="flex w-full justify-between">
                @foreach ($steps as $num => $label)
                    <li class="flex flex-col items-center {{ $step >= $num ? 'text-blue-400' : 'text-gray-600' }}">
                        <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center mb-1
                            {{ $step >= $num ? 'border-blue-400 bg-blue-400/20' : 'border-gray-600' }}">
                            {{ $num }}
                        </span>
                        <span class="hidden sm:inline">{{ $label }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Step Content -->
        <div class="bg-gray-800 rounded-lg shadow-xl p-6 md:p-8">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500 rounded text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Step 1: Requirements --}}
            @if($step == 1)
                <h2 class="text-2xl font-bold text-white mb-6">System Requirements</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-3">PHP Version</h3>
                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded">
                            <span class="text-gray-300">PHP {{ $phpVersion }}</span>
                            <span class="px-2 py-1 text-xs rounded {{ version_compare($phpVersion, '8.2', '>=') ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ version_compare($phpVersion, '8.2', '>=') ? 'OK' : 'Requires 8.2+' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-3">PHP Extensions</h3>
                        <div class="space-y-2">
                            @foreach($extensions as $name => $loaded)
                                <div class="flex items-center justify-between p-3 bg-gray-700 rounded">
                                    <span class="text-gray-300">{{ ucfirst($name) }}</span>
                                    <span class="px-2 py-1 text-xs rounded {{ $loaded ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                        {{ $loaded ? 'OK' : 'Missing' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-3">Directory Permissions</h3>
                        <div class="space-y-2">
                            @foreach($permissions as $name => $writable)
                                <div class="flex items-center justify-between p-3 bg-gray-700 rounded">
                                    <span class="text-gray-300">{{ $name }}/</span>
                                    <span class="px-2 py-1 text-xs rounded {{ $writable ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                        {{ $writable ? 'Writable' : 'Not Writable' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form action="{{ route('setup') }}" method="POST" class="mt-8">
                    @csrf
                    <input type="hidden" name="step" value="1">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Continue to Database Setup →
                    </button>
                </form>
            @endif

            {{-- Step 2: Database --}}
            @if($step == 2)
                <h2 class="text-2xl font-bold text-white mb-6">Database Setup</h2>

                <div class="bg-blue-500/20 border border-blue-500 rounded-lg p-4 mb-6">
                    <p class="text-blue-300">
                        <strong>SQLite Mode:</strong> Dropzone uses SQLite by default for simplicity.
                        The database file will be created automatically.
                    </p>
                    <p class="text-blue-300 mt-2 text-sm">
                        For MySQL/PostgreSQL, update your <code class="bg-blue-500/20 px-1 rounded">.env</code> file before continuing.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-700 rounded">
                        <h3 class="text-gray-200 font-medium mb-2">What happens next:</h3>
                        <ul class="text-gray-300 space-y-2 text-sm">
                            <li>• Create SQLite database (if using SQLite)</li>
                            <li>• Run database migrations</li>
                            <li>• Set up file storage directories</li>
                        </ul>
                    </div>
                </div>

                <form action="{{ route('setup') }}" method="POST" class="mt-8">
                    @csrf
                    <input type="hidden" name="step" value="2">
                    <div class="flex gap-4">
                        <a href="{{ route('setup', ['step' => 1]) }}" class="flex-1 py-3 bg-gray-600 hover:bg-gray-500 text-white rounded-lg font-medium text-center transition">
                            ← Back
                        </a>
                        <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Run Migrations →
                        </button>
                    </div>
                </form>
            @endif

            {{-- Step 3: Admin --}}
            @if($step == 3)
                <h2 class="text-2xl font-bold text-white mb-6">Admin Account</h2>

                <p class="text-gray-300 mb-6">
                    Create your admin password. This will be used to access the admin panel.
                </p>

                <form action="{{ route('setup') }}" method="POST">
                    @csrf
                    <input type="hidden" name="step" value="3">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-200 mb-2">Admin Password</label>
                            <input type="password" name="admin_password" required minlength="8"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500"
                                   placeholder="Minimum 8 characters">
                        </div>

                        <div>
                            <label class="block text-gray-200 mb-2">Confirm Password</label>
                            <input type="password" name="admin_password_confirmation" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500"
                                   placeholder="Confirm password">
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <a href="{{ route('setup', ['step' => 2]) }}" class="flex-1 py-3 bg-gray-600 hover:bg-gray-500 text-white rounded-lg font-medium text-center transition">
                            ← Back
                        </a>
                        <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                            Continue →
                        </button>
                    </div>
                </form>
            @endif

            {{-- Step 4: Settings --}}
            @if($step == 4)
                <h2 class="text-2xl font-bold text-white mb-6">Site Settings</h2>

                <form action="{{ route('setup') }}" method="POST">
                    @csrf
                    <input type="hidden" name="step" value="4">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-200 mb-2">Site Name</label>
                            <input type="text" name="site_name" value="Dropzone" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-200 mb-2">Site URL</label>
                            <input type="url" name="site_url" value="{{ url('/') }}" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <p class="text-gray-400 text-sm mt-1">Used for generating share links</p>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <a href="{{ route('setup', ['step' => 3]) }}" class="flex-1 py-3 bg-gray-600 hover:bg-gray-500 text-white rounded-lg font-medium text-center transition">
                            ← Back
                        </a>
                        <button type="submit" class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                            Complete Setup ✓
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">
            Dropzone v1.0 • Self-hosted file sharing
        </p>
    </div>
</body>
</html>