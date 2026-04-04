@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>System Logs</h2>
    <div>
        <a href="{{ route('admin.system.status') }}" class="btn btn-outline-primary">Status</a>
        <a href="{{ route('admin.system.tools') }}" class="btn btn-outline-secondary">Tools</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="level" class="form-select">
                    @foreach($levels as $value => $label)
                        <option value="{{ $value }}" {{ $level === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search logs..." value="{{ $search }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Log Entries</h6>
        <form method="POST" action="{{ route('admin.system.logs.clear') }}" onsubmit="return confirm('Clear all logs?')">
            @csrf
            <input type="hidden" name="level" value="{{ $level }}">
            <button type="submit" class="btn btn-sm btn-outline-danger">Clear Logs</button>
        </form>
    </div>
    <div class="card-body p-0">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Time</th>
                            <th style="width: 100px;">Level</th>
                            <th>Message</th>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td><small>{{ $log->created_at->format('M j, H:i:s') }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $log->level_color }}">{{ $log->level }}</span>
                                </td>
                                <td>
                                    <span class="log-message" data-bs-toggle="collapse" data-bs-target="#log{{ $log->id }}">
                                        {{ Str::limit($log->message, 100) }}
                                    </span>
                                    @if($log->context || $log->extra)
                                        <div class="collapse mt-2" id="log{{ $log->id }}">
                                            <pre class="bg-dark text-light p-2 small" style="white-space: pre-wrap;">{{ print_r(array_filter(['context' => $log->context, 'extra' => $log->extra]), true) }}</pre>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#log{{ $log->id }}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @else
            <div class="card-body text-center text-muted">
                No logs found.
            </div>
        @endif
    </div>
</div>
@endsection
