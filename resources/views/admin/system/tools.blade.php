@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>System Tools</h2>
    <div>
        <a href="{{ route('admin.system.status') }}" class="btn btn-outline-primary">Status</a>
        <a href="{{ route('admin.system.logs') }}" class="btn btn-outline-secondary">Logs</a>
    </div>
</div>

<!-- Output -->
@if($output)
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">Command Output</h6>
    </div>
    <div class="card-body">
        <pre class="bg-dark text-light p-3 mb-0" style="max-height: 300px; overflow-y: auto;">{{ $output }}</pre>
    </div>
</div>
@endif

<!-- Tools -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Cache Management</h6></div>
            <div class="card-body">
                <p class="text-muted">Clear application cache, config cache, and view cache.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}">
                    @csrf
                    <input type="hidden" name="action" value="clear_cache">
                    <button type="submit" class="btn btn-warning">Clear Cache</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Logs Management</h6></div>
            <div class="card-body">
                <p class="text-muted">Clear all system logs from the database.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}" onsubmit="return confirm('Clear all system logs?')">
                    @csrf
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn btn-danger">Clear Logs</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Storage Link</h6></div>
            <div class="card-body">
                <p class="text-muted">Create a symbolic link from public/storage to storage/app/public.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}">
                    @csrf
                    <input type="hidden" name="action" value="storage_link">
                    <button type="submit" class="btn btn-info">Create Storage Link</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Database Migration</h6></div>
            <div class="card-body">
                <p class="text-muted">Run pending database migrations.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}" onsubmit="return confirm('Run database migrations?')">
                    @csrf
                    <input type="hidden" name="action" value="migrate">
                    <button type="submit" class="btn btn-primary">Run Migrations</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Optimize Application</h6></div>
            <div class="card-body">
                <p class="text-muted">Cache config, routes, and views for better performance.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}">
                    @csrf
                    <input type="hidden" name="action" value="optimize">
                    <button type="submit" class="btn btn-success">Optimize</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Run Cron</h6></div>
            <div class="card-body">
                <p class="text-muted">Manually trigger the system cron job.</p>
                <form method="POST" action="{{ route('admin.system.tools.execute') }}">
                    @csrf
                    <input type="hidden" name="action" value="run_cron">
                    <button type="submit" class="btn btn-secondary">Run Cron</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
