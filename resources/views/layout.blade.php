<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteName ?? 'Dropzone' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { -webkit-text-size-adjust: 100%; }
        input, select, textarea, button { font-size: 16px; }
        .bg-custom {
            @if($backgroundImage ?? null)
                background-image: url('{{ $backgroundImage }}');
                background-size: cover;
                background-position: center;
            @endif
        }
        @media (min-width: 768px) {
            .bg-custom {
                background-attachment: fixed;
            }
        }
    </style>
</head>
<body class="bg-custom min-h-dvh bg-gray-900 overflow-x-hidden">
    <div class="min-h-dvh flex flex-col items-center justify-center p-3 sm:p-4">
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200 max-w-md w-full">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-200 max-w-md w-full">
                <p>{{ session('success') }}</p>
                @if(session('download_url'))
                    <p class="text-sm mt-2 mb-2">Share this one-time link:</p>
                    <input type="text" value="{{ session('download_url') }}" readonly
                        class="w-full bg-gray-800 text-white p-3 rounded-lg text-sm break-all"
                        onclick="this.select(); navigator.clipboard.writeText(this.value);">
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200 max-w-md w-full">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')

        <footer class="mt-8 text-gray-500 text-xs flex gap-4">
            <a href="/" class="hover:text-white">{{ $siteName ?? 'Dropzone' }}</a>
            <a href="{{ route('admin.login') }}" class="hover:text-gray-300">Admin</a>
        </footer>
    </div>
</body>
</html>
