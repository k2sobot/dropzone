@extends('layout', ['siteName' => $siteName, 'backgroundImage' => $backgroundImage ?? null])

@section('content')
<div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl shadow-xl p-5 sm:p-8 max-w-md w-full">
    @if($message)
        <div class="text-center">
            <h2 class="text-lg sm:text-xl font-semibold text-white mb-2">{{ $message }}</h2>
            <p class="text-gray-400 text-sm mb-4">One-time transfers expire after download or when the timer runs out.</p>
            <a href="/" class="inline-block text-blue-400 hover:text-blue-300 min-h-12 py-3">Send a file</a>
        </div>
    @elseif($upload)
        <div class="text-center">
            <p class="text-gray-400 text-sm mb-3">Someone sent you a file</p>
            <h2 class="text-lg sm:text-xl font-semibold text-white mb-2 break-all">{{ $upload->filename }}</h2>
            <p class="text-gray-400 mb-1">{{ $upload->human_size }}</p>
            <p class="text-gray-500 text-sm mb-6">Expires {{ $upload->expires_at->diffForHumans() }}</p>
            <a href="{{ route('download.file', $upload->id) }}"
               class="inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-xl transition min-h-12">Download</a>
            <p class="mt-4 text-gray-500 text-xs">This link works once. The file is deleted after download.</p>
        </div>
    @endif
</div>
@endsection
