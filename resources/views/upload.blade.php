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

        <div class="max-w-md w-full">
            <div class="bg-gray-800/80 backdrop-blur rounded-lg p-8 shadow-xl">
                <h1 class="text-2xl font-bold text-white text-center mb-6">{{ $siteName ?? 'Dropzone' }}</h1>

                <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer" onclick="document.getElementById('file-input').click()">
                        <input type="file" name="file" id="file-input" class="hidden" required onchange="updateFileName(this)">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 10h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-400">Click to upload or drag and drop</p>
                        <p class="mt-1 text-xs text-gray-500">Max file size: {{ $maxFileSize ?? 100 }}MB</p>
                        <p id="file-name" class="mt-2 text-sm text-blue-400 hidden"></p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                        Upload File
                    </button>
                </form>

                @if($siteName ?? null)
                <p class="mt-4 text-center text-gray-500 text-sm">
                    <a href="{{ route('admin.login') }}" class="hover:text-gray-300">Admin</a>
                </p>
                @endif
            </div>
        </div>

        <footer class="mt-8 text-gray-400 text-sm">
            <a href="/" class="hover:text-white">{{ $siteName ?? 'Dropzone' }}</a>
        </footer>
    </div>
</body>

<script>
function updateFileName(input) {
    const fileName = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        fileName.classList.remove('hidden');
    }
}

// Drag and drop support
const dropZone = document.querySelector('.border-dashed');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-500/10');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-500/10');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-500/10');

    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('file-input').files = files;
        updateFileName(document.getElementById('file-input'));
    }
});
</script>
</html>
