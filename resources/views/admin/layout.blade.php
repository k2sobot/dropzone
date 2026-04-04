<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - {{ $siteName ?? 'Dropzone' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-white font-bold text-xl">
                        {{ $siteName ?? 'Dropzone' }} Admin
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="ml-4">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                            Logout
                        </button>
                    </form>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.uploads.index') }}"
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Uploads
                    </a>
                    <a href="{{ route('admin.extensions.index') }}"
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Extensions
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Settings
                    </a>
                    <a href="{{ route('admin.settings.security') }}"
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Security
                    </a>
                    <a href="{{ route('admin.system.status') }}"
                       class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        System
                    </a>
                    <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300 px-3 py-2 rounded-md text-sm font-medium">
                        View Site →
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')

@yield('scripts')
    </main>
</body>
</html>