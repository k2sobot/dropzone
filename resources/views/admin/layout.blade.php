<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - {{ $siteName ?? 'Dropzone' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { -webkit-text-size-adjust: 100%; }
        input, select, textarea, button { font-size: 16px; }
    </style>
</head>
<body class="bg-gray-900 min-h-dvh overflow-x-hidden">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('admin.dashboard') }}" class="text-white font-bold text-lg sm:text-xl truncate">
                    {{ $siteName ?? 'Dropzone' }} Admin
                </a>
                <button type="button" id="nav-toggle" class="md:hidden text-gray-300 hover:text-white p-2" aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="hidden md:flex items-center gap-1 flex-wrap justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <a href="{{ route('admin.uploads.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Uploads</a>
                    <a href="{{ route('admin.extensions.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Extensions</a>
                    <a href="{{ route('admin.settings.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Settings</a>
                    <a href="{{ route('admin.settings.security') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Security</a>
                    <a href="{{ route('admin.system.status') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">System</a>
                    <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300 px-3 py-2 rounded-md text-sm font-medium">View site</a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-300 px-3 py-2 text-sm">Logout</button>
                    </form>
                </div>
            </div>
            <div id="nav-mobile" class="hidden md:hidden pb-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">Dashboard</a>
                <a href="{{ route('admin.uploads.index') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">Uploads</a>
                <a href="{{ route('admin.extensions.index') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">Extensions</a>
                <a href="{{ route('admin.settings.index') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">Settings</a>
                <a href="{{ route('admin.settings.security') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">Security</a>
                <a href="{{ route('admin.system.status') }}" class="block text-gray-300 hover:text-white px-3 py-3 rounded-md text-base">System</a>
                <a href="{{ route('home') }}" class="block text-blue-400 hover:text-blue-300 px-3 py-3 rounded-md text-base">View site</a>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full text-left text-red-400 hover:text-red-300 px-3 py-3 text-base">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
        @yield('scripts')
    </main>
    <script>
        document.getElementById('nav-toggle')?.addEventListener('click', function () {
            document.getElementById('nav-mobile').classList.toggle('hidden');
        });
    </script>
</body>
</html>
