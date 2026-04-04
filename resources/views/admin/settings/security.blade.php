@extends('admin.layout', ['siteName' => $siteName ?? 'Dropzone'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Security Settings</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<div class="row">
    <!-- Two-Factor Authentication -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">🔐 Two-Factor Authentication</h6>
                @if($twoFactorEnabled ?? false)
                    <span class="badge bg-success">Enabled</span>
                @else
                    <span class="badge bg-warning">Disabled</span>
                @endif
            </div>
            <div class="card-body">
                @if($twoFactorEnabled ?? false)
                    <p class="text-success mb-3">
                        <strong>2FA is enabled</strong> - Your account is protected with two-factor authentication.
                    </p>
                    <p class="text-muted small mb-3">
                        Recovery codes remaining: <strong>{{ $recoveryCodesCount ?? 0 }}</strong>
                    </p>
                    <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
                        Manage 2FA
                    </a>
                @else
                    <p class="text-muted mb-3">
                        Add an extra layer of security by enabling two-factor authentication. You'll need an authenticator app like Google Authenticator or Authy.
                    </p>
                    <a href="{{ route('2fa.setup') }}" class="btn btn-success">
                        Set Up 2FA
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- OAuth Providers -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">🔗 OAuth Providers</h6>
            </div>
            <div class="card-body">
                @if(empty($enabledProviders))
                    <p class="text-muted mb-0">
                        OAuth providers are not configured. Add the following to your <code>.env</code> file:
                    </p>
                    <pre class="bg-light p-3 rounded mt-3 small"><code># Google OAuth
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

# GitHub OAuth
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret</code></pre>
                @else
                    <div class="list-group list-group-flush">
                        @if(in_array('google', $enabledProviders))
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <svg class="w-5 h-5 align-middle me-2" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle;">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Google
                                </div>
                                @if($googleConnected ?? false)
                                    <span class="badge bg-success">Connected</span>
                                @else
                                    <a href="{{ route('admin.oauth.redirect', 'google') }}" class="btn btn-sm btn-outline-primary">
                                        Connect
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if(in_array('github', $enabledProviders))
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <svg class="w-5 h-5 align-middle me-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                                    </svg>
                                    GitHub
                                </div>
                                @if($githubConnected ?? false)
                                    <span class="badge bg-success">Connected</span>
                                @else
                                    <a href="{{ route('admin.oauth.redirect', 'github') }}" class="btn btn-sm btn-outline-primary">
                                        Connect
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        <strong>Note:</strong> After connecting an OAuth account, you must grant admin access from the database or grant via this interface.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Security Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">🔑 Password Authentication</h6>
                <span class="badge bg-info">Credentials</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.security.update') }}">
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
                    @if(session('admin_oauth_provider'))
                    <tr>
                        <th>OAuth Provider</th>
                        <td>{{ ucfirst(session('admin_oauth_provider')) }}</td>
                    </tr>
                    @if(session('admin_oauth_name'))
                    <tr>
                        <th>Name</th>
                        <td>{{ session('admin_oauth_name') }}</td>
                    </tr>
                    @endif
                    @endif
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
@endsection
