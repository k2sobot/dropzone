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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Site Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">App URL</label>
                    <input type="url" name="app_url" value="{{ $settings['app_url'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="https://your-domain.com">
                    <p class="text-gray-500 text-xs mt-1">Used for generating download links</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Max File Size (MB)</label>
                    <input type="number" name="max_file_size_mb" value="{{ $settings['max_file_size_mb'] }}"
                        min="1" max="1024"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
                    <p class="text-gray-500 text-xs mt-1">Maximum upload size (1-1024 MB)</p>
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Default Expiration (hours)</label>
                    <input type="number" name="default_expiration" value="{{ $settings['default_expiration'] }}"
                        min="1" max="720"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
                    <p class="text-gray-500 text-xs mt-1">How long files remain available (default: 24h)</p>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Save Settings
            </button>
        </form>
    </div>

    <!-- Storage Settings -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Storage Configuration</h2>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-300 text-sm mb-2">Storage Driver</label>
                <select name="storage_driver" class="w-full bg-gray-700 text-white rounded-lg px-4 py-2">
                    <option value="local" {{ $settings['storage_driver'] === 'local' ? 'selected' : '' }}>
                        Local Storage (default)
                    </option>
                    <option value="s3" {{ $settings['storage_driver'] === 's3' ? 'selected' : '' }}>
                        Amazon S3 / DigitalOcean Spaces
                    </option>
                </select>
                <p class="text-gray-500 text-xs mt-1">Choose where uploaded files are stored</p>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Save Storage Settings
            </button>
        </form>
    </div>

    <!-- S3 / DO Spaces Settings -->
    <div class="bg-gray-800 rounded-lg p-6" id="s3-settings">
        <h2 class="text-xl font-semibold text-white mb-4">
            S3 / DigitalOcean Spaces
            <span class="text-sm text-gray-400 ml-2">(Extension Required)</span>
        </h2>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center mb-4">
                <input type="checkbox" name="s3_enabled" id="s3_enabled" value="1"
                    {{ $settings['s3_enabled'] ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded">
                <label for="s3_enabled" class="ml-2 text-gray-300 text-sm">Enable S3/DO Spaces Storage</label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Access Key ID</label>
                    <input type="text" name="s3_key" value="{{ $settings['s3_key'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="AKIAIOSFODNN7EXAMPLE">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Access Key Secret</label>
                    <input type="password" name="s3_secret" value="{{ $settings['s3_secret'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="••••••••••••••••">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Bucket Name</label>
                    <input type="text" name="s3_bucket" value="{{ $settings['s3_bucket'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="my-dropzone-bucket">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Region</label>
                    <input type="text" name="s3_region" value="{{ $settings['s3_region'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="us-east-1">
                </div>
            </div>

            <div class="border-t border-gray-700 pt-4 mt-4">
                <h3 class="text-lg font-medium text-white mb-3">DigitalOcean Spaces (Optional)</h3>
                
                <div class="flex items-center mb-4">
                    <input type="checkbox" name="s3_do_spaces" id="s3_do_spaces" value="1"
                        {{ $settings['s3_do_spaces'] ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded">
                    <label for="s3_do_spaces" class="ml-2 text-gray-300 text-sm">Use DigitalOcean Spaces instead of AWS S3</label>
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Custom Endpoint</label>
                    <input type="url" name="s3_endpoint" value="{{ $settings['s3_endpoint'] }}"
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="https://nyc3.digitaloceanspaces.com">
                    <p class="text-gray-500 text-xs mt-1">Required for DigitalOcean Spaces or S3-compatible storage</p>
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Save S3 Settings
            </button>
        </form>
    </div>

    <!-- Admin Password -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Admin Password</h2>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 text-sm mb-2">New Password</label>
                    <input type="password" name="admin_password" 
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="••••••••" autocomplete="new-password">
                    <p class="text-gray-500 text-xs mt-1">Minimum 8 characters</p>
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Confirm Password</label>
                    <input type="password" name="admin_password_confirmation" 
                        class="w-full bg-gray-700 text-white rounded-lg px-4 py-2"
                        placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Update Password
            </button>
        </form>
    </div>

    <!-- Cleanup Command -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Scheduled Cleanup</h2>
        <p class="text-gray-400 mb-4">Add this to your crontab to clean up expired files hourly:</p>
        <code class="block bg-gray-900 p-4 rounded-lg text-green-400 text-sm overflow-x-auto">
            * * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1
        </code>
        @if($settings['storage_driver'] === 'local')
        <p class="text-gray-400 text-sm mt-3">
            💡 Local files are stored in <code class="text-green-400">storage/app/uploads/</code>
        </p>
        @endif
    </div>
</div>

@section('scripts')
<script>
// Show/hide S3 settings based on storage driver selection
document.querySelector('select[name="storage_driver"]').addEventListener('change', function() {
    const s3Settings = document.getElementById('s3-settings');
    if (this.value === 's3') {
        s3Settings.style.display = 'block';
        document.getElementById('s3_enabled').checked = true;
    } else {
        s3Settings.style.display = 'none';
    }
});

// Initial state
if (document.querySelector('select[name="storage_driver"]').value !== 's3') {
    document.getElementById('s3-settings').style.display = 'none';
}
</script>
@endsection
@endsection
