@extends('layout')

@section('content')
<div class="max-w-lg w-full">
    <div class="bg-gray-800/80 backdrop-blur rounded-2xl p-5 sm:p-8 shadow-xl">
        <h1 class="text-2xl sm:text-3xl font-bold text-white text-center">{{ $siteName ?? 'Dropzone' }}</h1>
        <p class="text-gray-400 text-center mt-2 mb-6 text-sm sm:text-base">Send a file. Get a one-time link. It deletes after download.</p>

        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="border-2 border-dashed border-gray-600 rounded-xl p-6 sm:p-10 text-center hover:border-blue-500 transition-colors cursor-pointer" onclick="document.getElementById('file-input').click()">
                <input type="file" name="file" id="file-input" class="hidden" required onchange="updateFileName(this)">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 10h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-3 text-sm text-gray-300">Drop a file here or tap to choose</p>
                <p class="mt-1 text-xs text-gray-500">Max {{ number_format(($maxFileSize ?? 104857600) / 1048576, 0) }} MB. Link works once.</p>
                <p id="file-name" class="mt-3 text-sm text-blue-400 hidden break-all px-1"></p>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors min-h-12">
                Get a transfer link
            </button>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        fileName.classList.remove('hidden');
    }
}
const dropZone = document.querySelector('.border-dashed');
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-blue-500', 'bg-blue-500/10'); });
dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('border-blue-500', 'bg-blue-500/10'); });
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-500/10');
    if (e.dataTransfer.files.length) {
        document.getElementById('file-input').files = e.dataTransfer.files;
        updateFileName(document.getElementById('file-input'));
    }
});
</script>
@endsection
