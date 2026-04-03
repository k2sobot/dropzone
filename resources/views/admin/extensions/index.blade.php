<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Extensions - Dropzone Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    @include('admin.partials.nav')

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Extensions</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Installed Extensions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Installed Extensions</h2>

                @if(count($extensions) === 0)
                    <p class="text-gray-600 mb-4">No extensions installed yet.</p>
                    <p class="text-sm text-gray-500">Use the command line to install extensions:</p>
                    <code class="block bg-gray-100 p-2 rounded mt-2 text-sm">
                        php artisan dropzone:install-extension dropzone/s3
                    </code>
                @else
                    <div class="space-y-4">
                        @foreach($extensions as $extension)
                            <div class="border rounded p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $extension['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $extension['description'] }}</p>
                                        <div class="flex items-center mt-2 space-x-2">
                                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-800 rounded">
                                                {{ $extension['type'] }}
                                            </span>
                                            @if($extension['enabled'])
                                                <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">
                                                    Enabled
                                                </span>
                                            @else
                                                <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded">
                                                    Disabled
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $extension['directory'] }}
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-2">
                                    @if(!$extension['enabled'])
                                        <button class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            Enable
                                        </button>
                                    @else
                                        <button class="text-sm px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">
                                            Disable
                                        </button>
                                    @endif
                                    <button class="text-sm px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Update
                                    </button>
                                    <button class="text-sm px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                        Uninstall
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Available Extensions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Available Extensions</h2>
                <p class="text-gray-600 mb-4">Official Dropzone extensions available for installation.</p>

                <div class="space-y-4">
                    @foreach($available as $package => $info)
                        <div class="border rounded p-4">
                            <h3 class="font-medium text-gray-900">{{ $info['name'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $info['description'] }}</p>
                            <div class="mt-4">
                                <button class="text-sm px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Install {{ $package }}
                                </button>
                                <a href="https://github.com/k2sobot/{{ $info['repo'] }}" target="_blank"
                                   class="text-sm px-4 py-2 ml-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                    View Repository
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium text-gray-900 mb-2">Install via Command Line</h3>
                    <code class="block bg-gray-100 p-2 rounded text-sm">
                        php artisan dropzone:install-extension dropzone/s3
                    </code>
                    <p class="text-sm text-gray-600 mt-2">
                        After installing, enable the extension in <code>config/extensions.php</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>