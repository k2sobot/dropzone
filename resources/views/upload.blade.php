<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $siteName ?? 'Dropzone' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-custom {
            @if($backgroundImage ?? null)
                background-image: url('{{ $backgroundImage }}');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            @endif
        }
    </style>
</head>
<body class="bg-custom min-h-screen bg-gray-900">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
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
                    <div class="mt-3">
                        <input type="text" value="{{ session('download_url') }}" readonly
                            class="w-full bg-gray-800 text-white p-2 rounded text-sm"
                            onclick="this.select(); navigator.clipboard.writeText(this.value);">
                    </div>
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200 max-w-md w-full">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')

        <footer class="mt-8 text-gray-400 text-sm">
            <a href="/" class="hover:text-white">{{ $siteName ?? 'Dropzone' }}</a>
        </footer>
    </div>
</body>
</html>