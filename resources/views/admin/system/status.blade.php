@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>System Status</h2>
    <div>
        <a href="{{ route('admin.system.logs') }}" class="btn btn-outline-primary">Logs</a>
        <a href="{{ route('admin.system.tools') }}" class="btn btn-outline-secondary">Tools</a>
    </div>
</div>

<!-- Server Info -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Server Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr><th>PHP Version</th><td>{{ $server_info['php_version'] }}</td></tr>
                    <tr><th>Laravel Version</th><td>{{ $server_info['laravel_version'] }}</td></tr>
                    <tr><th>Server Software</th><td>{{ $server_info['server_software'] }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr><th>Database</th><td>{{ $server_info['database'] }}</td></tr>
                    <tr><th>Cache Driver</th><td>{{ $server_info['cache_driver'] }}</td></tr>
                    <tr><th>Queue Driver</th><td>{{ $server_info['queue_driver'] }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($uploads_count) }}</h3>
                <small>Uploads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ number_format($logs_count) }}</h3>
                @if($error_count > 0)
                    <small class="text-warning">{{ $error_count }} errors</small>
                @else
                    <small>Log entries</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $storage_size }}</h3>
                <small>Storage @if($storage_writable)(Writable)@else(Not Writable)@endif</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        @php $cron_card_class = $cron_status === 'ok' ? 'bg-success' : ($cron_status === 'warning' ? 'bg-warning' : 'bg-secondary') @endphp
        <div class="card {{ $cron_card_class }} text-white">
            <div class="card-body text-center">
                <h3 class="mb-1">@if($cron_status === 'ok')OK@elseif($cron_status === 'warning')WARN@else@?@endif</h3>
                <small>Cron: {{ $last_cron ? \Carbon\Carbon::createFromTimestamp($last_cron)->diffForHumans() : 'Never' }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Disk & Memory -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Disk Space</h6></div>
            <div class="card-body">
                <p class="mb-1">Free: <strong>{{ $disk_free }}</strong></p>
                <p class="mb-0">Total: <strong>{{ $disk_total }}</strong></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Memory Usage</h6></div>
            <div class="card-body">
                <p class="mb-1">Current: <strong>{{ $memory_usage }}</strong></p>
                <p class="mb-0">Peak: <strong>{{ $memory_peak }}</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Extensions -->
<div class="card">
    <div class="card-header"><h6 class="mb-0">PHP Extensions</h6></div>
    <div class="card-body">
        <div class="row">
            @foreach($extensions as $name => $loaded)
                <div class="col-md-3 col-sm-4 mb-2">
                    @if($loaded)
                        <span class="badge bg-success">{{ $name }}</span>
                    @else
                        <span class="badge bg-danger">{{ $name }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
