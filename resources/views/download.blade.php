@extends('layout', ['siteName' => $siteName, 'backgroundImage' => $backgroundImage ?? null])

@section('content')
<div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-xl p-5 sm:p-8 max-w-md w-full">
    @if($message)
        <div class="text-center">
            <div class="text-5xl sm:text-6xl mb-4">⚠️</div>
            <h2 class="text-lg sm:text-xl font-semibold text-white mb-2">{{ $message }}</h2>
            <a href="/" class="mt-4 inline-block text-blue-400 hover:text-blue-300 min-h-12 py-3">Upload another file</a>
        </div>
    @elseif($upload)
        <div class="text-center">
            <div class="text-5xl sm:text-6xl mb-4">📄</div>
            <h2 class="text-lg sm:text-xl font-semibold text-white mb-2 break-all">{{ $upload->filename }}</h2>
            <p class="text-gray-400 mb-1">{{ $upload->human_size }}</p>
            <p class="text-gray-500 text-sm mb-6">Expires: {{ $upload->expires_at->diffForHumans() }}</p>

            <a href="{{ route('download.file', $upload->id) }}"
               class="inline-block w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition min-h-12">
                Download File
            </a>

            <p class="mt-4 text-gray-500 text-xs">This file will be deleted after download.</p>
        </div>
    @endif
</div>
@endsection
