<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Settings - Trello</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --bg-primary: #1d2125;
            --bg-secondary: #22272b;
            --bg-hover: #2c333a;
            --text-primary: #b6c2cf;
            --text-secondary: #8c9cb8;
            --accent-blue: #579dff;
            --accent-green: #4bce97;
            --accent-red: #f87168;
            --border-color: #38414a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .back-link:hover { opacity: 0.8; }

        .profile-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 14px;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(87, 157, 255, 0.2);
        }

        .btn {
            background: var(--accent-blue);
            color: #1d2125;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn:hover { background: #85b8ff; }
        .btn:active { transform: translateY(1px); }

        .btn-green { background: var(--accent-green); }
        .btn-green:hover { background: #7ee2b8; }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(75, 206, 151, 0.1);
            border: 1px solid rgba(75, 206, 151, 0.2);
            color: var(--accent-green);
        }

        .alert-error {
            background: rgba(248, 113, 104, 0.1);
            border: 1px solid rgba(248, 113, 104, 0.2);
            color: var(--accent-red);
        }

        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--accent-blue) 0%, #0055cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(87, 157, 255, 0.3);
        }

        .divider {
            height: 1px;
            background: var(--border-color);
            margin: 32px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ route('dashboard') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Boards
            </a>
            <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Account Settings</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="profile-card">
            <div class="user-avatar-large">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <h2 class="section-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Public Profile
                </h2>
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                </div>

                <button type="submit" class="btn btn-green">Update Profile</button>
            </form>

            <div class="divider"></div>

            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <h2 class="section-title" style="margin-bottom: 12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Change Password
                </h2>
                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 24px;">Ensure your account is using a long, random password to stay secure.</p>
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>

                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
        
        <div style="text-align: center; color: var(--text-secondary); font-size: 12px; margin-top: 40px;">
            <p>© 2026 Trello Clone. All rights reserved.</p>
        </div>

        @if(auth()->user()->isSystemAdmin())
        <!-- Admin Panel -->
        <div class="profile-card" style="margin-top: 24px;">
            <h2 class="section-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Admin Panel
            </h2>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: #1d2125; border: 1px solid #38414a; border-radius: 8px; padding: 20px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #579dff;">{{ $users->count() }}</div>
                    <div style="font-size: 13px; color: #8c9cb8; margin-top: 4px;">Total Users</div>
                </div>
                <div style="background: #1d2125; border: 1px solid #38414a; border-radius: 8px; padding: 20px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #4bce97;">{{ $users->where('is_active', true)->count() }}</div>
                    <div style="font-size: 13px; color: #8c9cb8; margin-top: 4px;">Active Users</div>
                </div>
                <div style="background: #1d2125; border: 1px solid #38414a; border-radius: 8px; padding: 20px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #f79239;">{{ $users->where('is_active', false)->count() }}</div>
                    <div style="font-size: 13px; color: #8c9cb8; margin-top: 4px;">Pending Approval</div>
                </div>
                <div style="background: #1d2125; border: 1px solid #38414a; border-radius: 8px; padding: 20px; text-align: center;">
                    <div style="font-size: 32px; font-weight: 800; color: #f87168;">{{ $users->where('role', 'admin')->count() }}</div>
                    <div style="font-size: 13px; color: #8c9cb8; margin-top: 4px;">Admins</div>
                </div>
            </div>

            <!-- Users List -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($users as $u)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: #1d2125; border: 1px solid #38414a; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #0c66e4, #0055cc); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #b6c2cf; font-size: 14px;">
                                {{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span style="font-size: 11px; color: #6b778c; font-style: italic;">(You)</span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: #5e6c84;">{{ $u->email }}</div>
                        </div>
                        <span style="padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: {{ $u->role === 'admin' ? 'rgba(87,157,255,0.15)' : 'rgba(140,156,184,0.1)' }}; color: {{ $u->role === 'admin' ? '#579dff' : '#8c9cb8' }};">
                            {{ $u->role }}
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: {{ $u->is_active ? '#4bce97' : '#f79239' }};">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: {{ $u->is_active ? '#4bce97' : '#f79239' }};"></span>
                            {{ $u->is_active ? 'Active' : 'Pending' }}
                        </span>
                    </div>
                    @if($u->id !== auth()->id())
                    <div style="display: flex; gap: 8px;">
                        <form action="{{ route('admin.users.toggle', $u) }}" method="POST">
                            @csrf
                            <button type="submit" style="padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; background: {{ $u->is_active ? 'rgba(201,55,44,0.1)' : 'rgba(12,102,228,0.15)' }}; color: {{ $u->is_active ? '#ef5c48' : '#579dff' }};">
                                {{ $u->is_active ? 'Revoke' : 'Approve' }}
                            </button>
                        </form>
                        <form id="delUser{{ $u->id }}" action="{{ route('admin.users.destroy', $u) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDel({{ $u->id }}, '{{ addslashes($u->name) }}')" style="padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; background: rgba(201,55,44,0.1); color: #ef5c48;">
                                Delete
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDel(id, name) {
            Swal.fire({
                title: 'Delete user?',
                text: `Are you sure you want to delete ${name}?`,
                icon: 'warning',
                background: '#282e33',
                color: '#b6c2cf',
                showCancelButton: true,
                confirmButtonColor: '#c9372c',
                cancelButtonColor: '#323940',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delUser' + id).submit();
                }
            });
        }
    </script>
</body>
</html>
