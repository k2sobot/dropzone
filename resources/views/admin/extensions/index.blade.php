@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<h1 class="text-2xl font-bold text-white mb-2">Modules</h1>
<p class="text-gray-400 text-sm mb-6">Core is free. Official modules are a one-time purchase (1 year of updates included), same idea as FreeScout.</p>

<div class="space-y-4">
    @foreach($modules as $module)
        <div class="bg-gray-800 rounded-lg p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ $module['name'] }}</h2>
                    <p class="text-gray-400 text-sm mt-1">{{ $module['description'] }}</p>
                    <p class="text-gray-500 text-xs mt-2 font-mono break-all">{{ $module['package'] }}</p>
                </div>
                <div class="text-left sm:text-right shrink-0">
                    <div class="text-white font-semibold">${{ number_format($module['price']) }}</div>
                    <div class="text-gray-500 text-xs">{{ $module['billing'] }} · {{ $module['updates'] }}</div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-3">
                @if($module['licensed'])
                    <span class="text-xs px-2 py-1 rounded bg-green-500/20 text-green-400">Licensed</span>
                    @if($module['updates_until'])
                        <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">Updates until {{ $module['updates_until']->toDateString() }}</span>
                    @endif
                @else
                    <span class="text-xs px-2 py-1 rounded bg-yellow-500/20 text-yellow-400">Unlicensed</span>
                @endif
                @if($module['installed'])
                    <span class="text-xs px-2 py-1 rounded bg-blue-500/20 text-blue-400">Installed</span>
                @endif
            </div>

            @if($module['licensed'])
                <form method="POST" action="{{ route('admin.extensions.deactivate') }}" class="mt-4" onsubmit="return confirm('Remove this license?')">
                    @csrf
                    <input type="hidden" name="package" value="{{ $module['package'] }}">
                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">Deactivate license</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.extensions.activate') }}" class="mt-4 flex flex-col sm:flex-row gap-2">
                    @csrf
                    <input type="hidden" name="package" value="{{ $module['package'] }}">
                    <input type="text" name="license_key" required placeholder="Paste license key (DZ1.…)"
                        class="flex-1 bg-gray-700 text-white rounded-lg px-3 py-2 text-base font-mono">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg min-h-12">Activate</button>
                </form>
            @endif
        </div>
    @endforeach
</div>

@if(count($installed))
    <h2 class="text-lg font-semibold text-white mt-10 mb-4">Installed on this server</h2>
    <div class="space-y-3">
        @foreach($installed as $ext)
            <div class="bg-gray-800 rounded-lg p-4 flex justify-between gap-3">
                <div>
                    <div class="text-white">{{ $ext['name'] }}</div>
                    <div class="text-gray-500 text-xs font-mono">{{ $ext['composer_name'] }}</div>
                </div>
                <span class="text-xs px-2 py-1 h-fit rounded {{ $ext['licensed'] ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                    {{ $ext['licensed'] ? 'Active' : 'Blocked (no license)' }}
                </span>
            </div>
        @endforeach
    </div>
@endif
@endsection
