@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Settings</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<div class="row">
    <!-- Security Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">🔐 Security Settings</h6>
                <span class="badge bg-info">Authentication</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.security') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Admin Username</label>
                        <input type="text" name="username" class="form-control" 
                            value="{{ $currentUsername }}" required
                            placeholder="Enter new username">
                        <div class="form-text">Current: {{ $currentUsername ?: 'Using .env default' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" 
                            placeholder="Enter new password" minlength="8">
                        <div class="form-text">Minimum 8 characters. Leave blank to keep current password.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" 
                            placeholder="Confirm new password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Password (required to save changes)</label>
                        <input type="password" name="current_password" class="form-control" 
                            placeholder="Enter current password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Credentials</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Session Info -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">📋 Current Session</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Logged in as</th>
                        <td>{{ session('admin_username') }}</td>
                    </tr>
                    <tr>
                        <th>Login time</th>
                        <td>{{ \Carbon\Carbon::createFromTimestamp(session('admin_login_time'))->format('M j, Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Session age</th>
                        <td>{{ \Carbon\Carbon::createFromTimestamp(session('admin_login_time'))->diffForHumans() }}</td>
                    </tr>
                    <tr>
                        <th>IP address</th>
                        <td>{{ request()->ip() }}</td>
                    </tr>
                </table>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Future Security Features -->
<div class="row">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">🚀 Coming Soon: Enhanced Security</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6>🔐 Two-Factor Authentication</h6>
                                <p class="text-muted small">Add an extra layer of security with TOTP-based 2FA using Google Authenticator or similar apps.</p>
                                <span class="badge bg-secondary">Planned</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6>🔗 OAuth / Single Sign-On</h6>
                                <p class="text-muted small">Integrate with OAuth providers like Google, GitHub, or enterprise SSO for seamless authentication.</p>
                                <span class="badge bg-secondary">Planned</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6>📊 Audit Log</h6>
                                <p class="text-muted small">Detailed logging of all admin actions, login attempts, and security events for compliance.</p>
                                <span class="badge bg-secondary">Planned</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
