<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Login - Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }
        .top-header {
            position: fixed; top: 0; left: 0; right: 0; height: 44px;
            background: #1d2125; border-bottom: 1px solid #38414a;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 10px; }
        .trello-logo {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
            border-radius: 6px; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 4px; padding: 6px;
            flex-shrink: 0;
        }
        .trello-logo span {
            display: block; width: 10px; height: 4.5px;
            background: white; border-radius: 1px;
        }
        .trello-text { font-size: 18px; font-weight: 700; color: #fff; }
        .header-right { display: flex; align-items: center; gap: 10px; }
        .btn-back {
            padding: 5px 14px; background: #323940; color: #b6c2cf;
            border: none; border-radius: 4px; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px;
        }
        .btn-back:hover { background: #3c444d; color: #fff; }
        .user-avatar-header {
            width: 32px; height: 32px; border-radius: 50%; background: #0c66e4; color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; text-transform: uppercase;
        }
        .page-wrapper {
            margin-top: 44px; padding: 32px 24px;
            max-width: 1100px; margin-left: auto; margin-right: auto;
        }
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: 12px; padding: 20px;
            display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-label { font-size: 12px; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .stat-value { font-size: 28px; font-weight: 800; color: white; line-height: 1.2; }
        /* Card */
        .card {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: 12px; padding: 24px; margin-bottom: 24px;
        }
        .card-title {
            font-size: 18px; font-weight: 700; color: white;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; gap: 10px;
        }
        .form-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 180px; margin-bottom: 0; }
        .form-label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text-secondary); text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 8px;
        }
        .form-input {
            width: 100%; padding: 10px 12px; font-size: 14px;
            background: var(--bg-primary); border: 1px solid var(--border-color);
            border-radius: 6px; color: white; font-family: inherit;
        }
        .form-input:focus { outline: none; border-color: var(--accent-blue); }
        .form-input::placeholder { color: #5e6c84; }
        .btn-primary {
            padding: 10px 20px; background: #0c66e4; color: white;
            border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
            cursor: pointer; white-space: nowrap; height: 42px;
        }
        .btn-primary:hover { background: #0055cc; }
        /* Search row */
        .search-row {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; gap: 12px; flex-wrap: wrap;
        }
        .search-wrap { position: relative; }
        .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9fadbc; }
        .search-input {
            padding: 9px 12px 9px 34px; background: var(--bg-primary);
            border: 1px solid var(--border-color); border-radius: 6px;
            color: white; font-size: 14px; width: 260px; font-family: inherit;
        }
        .search-input:focus { outline: none; border-color: var(--accent-blue); }
        /* User rows */
        .user-list { display: flex; flex-direction: column; gap: 10px; }
        .user-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px; background: var(--bg-primary);
            border: 1px solid var(--border-color); border-radius: 10px;
            transition: border-color 0.2s, transform 0.2s;
        }
        .user-row:hover { border-color: #454f59; transform: translateX(3px); }
        .user-left { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .user-avatar {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, #0c66e4, #0055cc);
            color: white; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 17px; flex-shrink: 0;
        }
        .user-name { font-size: 15px; font-weight: 600; color: #b6c2cf; }
        .user-email { font-size: 12px; color: #5e6c84; margin-top: 2px; }
        .badge {
            padding: 3px 9px; border-radius: 5px; font-size: 11px;
            font-weight: 700; text-transform: uppercase;
        }
        .badge-admin { background: rgba(87,157,255,0.15); color: #579dff; }
        .badge-user { background: rgba(140,156,184,0.1); color: #8c9cb8; }
        .status-dot { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 500; }
        .dot { width: 7px; height: 7px; border-radius: 50%; }
        .dot-active { background: #4bce97; }
        .dot-pending { background: #f79239; }
        .user-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
        .btn-action {
            padding: 7px 14px; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
        }
        .btn-pwd { background: rgba(75,206,151,0.1); color: #4bce97; }
        .btn-pwd:hover { background: rgba(75,206,151,0.3); }
        .btn-grant { background: rgba(12,102,228,0.15); color: #579dff; }
        .btn-grant:hover { background: #0c66e4; color: white; }
        .btn-revoke { background: rgba(182,194,207,0.1); color: #b6c2cf; }
        .btn-revoke:hover { background: rgba(201,55,44,0.2); color: #ef5c48; }
        .btn-danger { background: rgba(201,55,44,0.1); color: #ef5c48; }
        .btn-danger:hover { background: #c9372c; color: white; }
        /* Alert */
        .alert {
            padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;
            font-size: 14px; display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: rgba(75,206,151,0.1); border: 1px solid rgba(75,206,151,0.2); color: #4bce97; }
        .alert-error { background: rgba(248,113,104,0.1); border: 1px solid rgba(248,113,104,0.2); color: #f87168; }
        /* Modal */
        .modal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.75); z-index: 9999;
            align-items: center; justify-content: center; backdrop-filter: blur(4px);
        }
        .modal.active { display: flex; }
        .modal-box {
            background: var(--bg-secondary); border: 1px solid var(--border-color);
            border-radius: 12px; width: 100%; max-width: 440px; padding: 24px; margin: 16px;
        }
        .modal-title {
            font-size: 18px; font-weight: 700; color: white; margin-bottom: 20px;
        }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel {
            padding: 9px 18px; background: #323940; color: #b6c2cf;
            border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-cancel:hover { background: #3c444d; }
    </style>
</head>
<body>

    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <div class="trello-logo"><span></span><span></span></div>
            <span class="trello-text">Trello</span>
        </div>
        <div class="header-right">
            <a href="{{ route('dashboard') }}" class="btn-back">← Dashboard</a>
            <div class="user-avatar-header">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        </div>
    </div>

    <div class="page-wrapper">

        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✕ {{ session('error') }}</div>
        @endif

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(87,157,255,0.1);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#579dff"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value">{{ $users->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(75,206,151,0.1);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#4bce97"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <div>
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $users->where('is_active', true)->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(247,146,57,0.1);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#f79239"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $users->where('is_active', false)->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(248,113,104,0.1);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#f87168"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                </div>
                <div>
                    <div class="stat-label">Admins</div>
                    <div class="stat-value">{{ $users->where('role', 'admin')->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Change My Password -->
        <div class="card">
            <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Change My Password
            </div>
            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                @if($errors->has('current_password') || $errors->has('password'))
                    <div class="alert alert-error" style="margin-bottom:16px;">
                        {{ $errors->first('current_password') ?: $errors->first('password') }}
                    </div>
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" placeholder="Current password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" placeholder="New password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm password" required>
                    </div>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>

        <!-- Users List -->
        <div class="card">
            <div class="search-row">
                <div class="card-title" style="margin-bottom:0; border:none; padding:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    All Users ({{ $users->count() }})
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <div class="search-wrap">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="userSearch" class="search-input" placeholder="Search users...">
                    </div>
                    <button onclick="document.getElementById('createModal').classList.add('active')" class="btn-primary" style="height:38px; padding: 0 16px;">+ Add User</button>
                </div>
            </div>

            <div class="user-list" id="userList">
                @foreach($users as $u)
                <div class="user-row" data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}">
                    <div class="user-left">
                        <div class="user-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                        <div>
                            <div class="user-name">
                                {{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span style="font-size:11px; color:#5e6c84; font-style:italic;">(You)</span>
                                @endif
                            </div>
                            <div class="user-email">{{ $u->email }}</div>
                        </div>
                        <span class="badge {{ $u->role === 'admin' ? 'badge-admin' : 'badge-user' }}">{{ $u->role }}</span>
                        <div class="status-dot">
                            <div class="dot {{ $u->is_active ? 'dot-active' : 'dot-pending' }}"></div>
                            <span style="color: {{ $u->is_active ? '#4bce97' : '#f79239' }};">{{ $u->is_active ? 'Active' : 'Pending' }}</span>
                        </div>
                    </div>
                    @if($u->id !== auth()->id())
                    <div class="user-actions">
                        <button class="btn-action btn-pwd" onclick="openPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')">Password</button>
                        <form action="{{ route('admin.users.toggle', $u) }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn-action {{ $u->is_active ? 'btn-revoke' : 'btn-grant' }}">
                                {{ $u->is_active ? 'Revoke' : 'Approve' }}
                            </button>
                        </form>
                        <form id="delForm{{ $u->id }}" action="{{ route('admin.users.destroy', $u) }}" method="POST" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-action btn-danger" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')">Delete</button>
                        </form>
                    </div>
                    @else
                        <span style="font-size:13px; color:#5e6c84; font-style:italic;">Admin (You)</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-box">
            <div class="modal-title">Change Password — <span id="pwdUserName" style="color:#579dff;"></span></div>
            <div id="pwdError" style="display:none; background:rgba(248,113,104,0.1); color:#f87168; border:1px solid rgba(248,113,104,0.2); padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:13px;"></div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">New Password</label>
                <input type="password" id="newPwd" class="form-input" placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" id="confirmPwd" class="form-input" placeholder="Repeat password">
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="document.getElementById('passwordModal').classList.remove('active')">Cancel</button>
                <button class="btn-primary" onclick="submitPwd()">Save Password</button>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="modal">
        <div class="modal-box">
            <div class="modal-title">Add New User</div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required minlength="6">
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPwdUserId = null;

        function openPasswordModal(id, name) {
            currentPwdUserId = id;
            document.getElementById('pwdUserName').textContent = name;
            document.getElementById('newPwd').value = '';
            document.getElementById('confirmPwd').value = '';
            document.getElementById('pwdError').style.display = 'none';
            document.getElementById('passwordModal').classList.add('active');
        }

        function submitPwd() {
            const newPass = document.getElementById('newPwd').value;
            const confirmPass = document.getElementById('confirmPwd').value;
            const err = document.getElementById('pwdError');
            if (!newPass || newPass.length < 6) {
                err.textContent = 'Password must be at least 6 characters.';
                err.style.display = 'block'; return;
            }
            if (newPass !== confirmPass) {
                err.textContent = 'Passwords do not match.';
                err.style.display = 'block'; return;
            }
            err.style.display = 'none';
            fetch(`/admin/users/${currentPwdUserId}/password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: newPass })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('passwordModal').classList.remove('active');
                    Swal.fire({ icon: 'success', title: 'Password updated!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, background: '#22272b', color: '#b6c2cf' });
                } else {
                    err.textContent = data.error || 'Failed to update password.';
                    err.style.display = 'block';
                }
            })
            .catch(() => {
                err.textContent = 'Network error. Please try again.';
                err.style.display = 'block';
            });
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Delete user?',
                text: `Remove ${name} permanently?`,
                icon: 'warning', background: '#282e33', color: '#b6c2cf',
                showCancelButton: true, confirmButtonColor: '#c9372c', cancelButtonColor: '#323940',
                confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
            }).then(r => { if (r.isConfirmed) document.getElementById('delForm' + id).submit(); });
        }

        document.getElementById('userSearch').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.user-row').forEach(row => {
                const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
                row.style.display = match ? '' : 'none';
            });
        });

        // Close modal on backdrop click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });
    </script>
</body>
</html>
