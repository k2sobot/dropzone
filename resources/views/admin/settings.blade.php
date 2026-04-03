@extends('admin.layout', ['siteName' => $siteName])

@section('content')
<div class="space-y-8">
    <!-- Background Image -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Download Page Background</h2>
        
        @if($backgroundImage)
        <div class="mb-4">
            <img src="{{ $backgroundImage }}" alt="Current background" class="w-full h-48 object-cover rounded-lg mb-4">
        </div>
        @endif

        <form action="{{ route('admin.settings.background') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center space-x-4">
                <input type="file" name="background" accept="image/*" class="text-gray-300">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Upload
                </button>
            </div>
        </form>
    </div>

    <!-- General Settings -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">General Settings</h2>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-300 text-sm mb-2">Site Name</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] }}"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
            </div>

            <div>
                <label class="block text-gray-300 text-sm mb-2">Max File Size (bytes)</label>
                <input type="number" name="max_file_size" value="{{ $settings['max_file_size'] }}"
                    class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
                <p class="text-gray-500 text-xs mt-1">Current: {{ number_format($settings['max_file_size'] / 1048576, 0) }}MB</p>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Save Settings
            </button>
        </form>
    </div>

    <!-- Cleanup Command -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Scheduled Cleanup</h2>
        <p class="text-gray-400 mb-4">Add this to your crontab to clean up expired files hourly:</p>
        <code class="block bg-gray-900 p-4 rounded-lg text-green-400 text-sm">
            * * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1
        </code>
    </div>
</div>
@endsection