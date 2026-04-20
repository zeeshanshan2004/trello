<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Workspace - {{ config('app.name', 'Trello') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #1d2125; color: #b6c2cf; min-height: 100vh; }
        .top-header {
            position: fixed; top: 0; left: 0; right: 0; height: 40px;
            background: #1d2125; border-bottom: 1px solid #38414a;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 12px; z-index: 1000;
        }
        .header-left { display: flex; align-items: center; gap: 8px; }
        .trello-logo-small {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
            border-radius: 6px; position: relative;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 4px; padding: 6px;
        }
        .trello-logo-small::before, .trello-logo-small::after {
            content: ''; width: 10px; height: 4px;
            background: white; border-radius: 1px; display: block;
        }
        .trello-text-header { font-size: 18px; font-weight: 700; color: #fff; }
        .user-avatar-header {
            width: 32px; height: 32px; border-radius: 50%; background: #0c66e4; color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 14px; text-transform: uppercase; cursor: pointer;
        }
        .page-wrapper {
            margin-top: 40px; min-height: calc(100vh - 40px);
            display: flex; justify-content: center; padding: 40px 24px; overflow-y: auto;
        }
        .edit-container { width: 100%; max-width: 640px; }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: #8c9cb8; text-decoration: none; font-size: 14px; margin-bottom: 24px;
        }
        .back-link:hover { color: #b6c2cf; }
        .page-title { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 24px; }
        .card { background: #22272b; border: 1px solid #38414a; border-radius: 8px; padding: 24px; margin-bottom: 24px; }
        .card-title {
            font-size: 16px; font-weight: 600; color: #fff; margin-bottom: 20px;
            padding-bottom: 12px; border-bottom: 1px solid #38414a;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: #9fadbc; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
        }
        .form-group label .required { color: #ef5c48; }
        .form-input {
            width: 100%; padding: 10px 12px; font-size: 14px;
            background: #282e33; border: 1px solid #38414a;
            border-radius: 4px; color: #b6c2cf; font-family: inherit; transition: border-color 0.2s;
        }
        .form-input:focus { outline: none; border-color: #0c66e4; background: #2c333a; }
        .form-input::placeholder { color: #5e6c84; }
        select.form-input { cursor: pointer; }
        .img-preview { width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 1px solid #38414a; margin-bottom: 10px; }
        .img-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-hint { font-size: 12px; color: #5e6c84; margin-top: 4px; }
        .btn-row { display: flex; gap: 12px; margin-top: 24px; }
        .btn-primary {
            flex: 1; padding: 10px 20px; background: #0c66e4; color: white;
            border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .btn-primary:hover { background: #0055cc; }
        .btn-cancel {
            padding: 10px 20px; background: #323940; color: #b6c2cf;
            border: none; border-radius: 4px; font-size: 14px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: flex; align-items: center;
        }
        .btn-cancel:hover { background: #3c444d; }
        .add-member-row { display: flex; gap: 10px; align-items: flex-end; }
        .add-member-row .form-group { flex: 1; margin-bottom: 0; }
        .btn-add {
            padding: 10px 18px; background: #0c66e4; color: white;
            border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap;
        }
        .btn-add:hover { background: #0055cc; }
        .member-list { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }
        .member-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px; background: #282e33; border-radius: 6px; border: 1px solid #38414a;
        }
        .member-info { display: flex; align-items: center; gap: 12px; }
        .member-avatar {
            width: 36px; height: 36px; border-radius: 50%; background: #0c66e4; color: white;
            display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px;
        }
        .member-name { font-size: 14px; font-weight: 500; color: #b6c2cf; }
        .member-email { font-size: 12px; color: #5e6c84; }
        .role-badge {
            padding: 3px 8px; border-radius: 4px; font-size: 11px;
            font-weight: 600; text-transform: uppercase; background: #323940; color: #8c9cb8;
        }
        .role-badge.owner { background: rgba(12,102,228,0.15); color: #579dff; }
        .btn-remove {
            padding: 6px 12px; background: rgba(201,55,44,0.1); color: #ef5c48;
            border: 1px solid rgba(201,55,44,0.2); border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;
        }
        .btn-remove:hover { background: #c9372c; color: white; border-color: #c9372c; }
        .owner-label { font-size: 13px; color: #5e6c84; font-style: italic; }
        .btn-delete {
            padding: 10px 20px; background: rgba(201,55,44,0.1); color: #ef5c48;
            border: 1px solid rgba(201,55,44,0.3); border-radius: 4px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-delete:hover { background: #c9372c; color: white; border-color: #c9372c; }
        .alert-success {
            background: rgba(75,206,151,0.1); color: #4bce97;
            border: 1px solid rgba(75,206,151,0.2);
            padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px;
        }
        .alert-error {
            background: rgba(201,55,44,0.1); color: #ef5c48;
            border: 1px solid rgba(201,55,44,0.2);
            padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="header-left">
            <div class="trello-logo-small"></div>
            <span class="trello-text-header">Trello</span>
        </div>
        <div class="user-avatar-header">{{ substr(auth()->user()->name, 0, 1) }}</div>
    </div>

    <div class="page-wrapper">
        <div class="edit-container">
            <a href="{{ route('workspaces.show', $workspace) }}" class="back-link">← Back to Workspace</a>
            <div class="page-title">Edit Workspace</div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any() && !$errors->has('delete_password'))
                <div class="alert-error">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <!-- Workspace Settings -->
            <div class="card">
                <div class="card-title">Workspace Settings</div>
                <form method="POST" action="{{ route('workspaces.update', $workspace) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Workspace Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $workspace->name) }}" placeholder="Enter workspace name" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Describe your workspace (optional)" style="resize: vertical;">{{ old('description', $workspace->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Workspace Icon / Image</label>
                        @if($workspace->image_url)
                            <div class="img-preview"><img src="{{ asset('storage/' . $workspace->image_url) }}" alt="Current icon"></div>
                        @endif
                        <input type="file" name="image" class="form-input" accept="image/*">
                        <p class="file-hint">Upload a new image to replace the current one.</p>
                    </div>
                    <div class="form-group">
                        <label>Theme Color</label>
                        <select name="color" class="form-input">
                            @foreach(['blue','green','red','purple','orange','pink'] as $c)
                                <option value="{{ $c }}" {{ old('color', $workspace->color) == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="btn-row">
                        <button type="submit" class="btn-primary">Update Workspace</button>
                        <a href="{{ route('workspaces.show', $workspace) }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Members Section -->
            <div class="card">
                <div class="card-title">Workspace Members</div>
                <form method="POST" action="{{ route('workspaces.members.add', $workspace) }}" id="addMemberForm">
                    @csrf
                    <input type="hidden" name="email" id="selectedMemberEmail">
                    <div class="add-member-row">
                        <div class="form-group" style="position: relative; flex:1;">
                            <label>Search Member</label>
                            <div style="position: relative;">
                                <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9fadbc; pointer-events:none; display:flex; align-items:center;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input type="text" id="memberSearchInput" class="form-input"
                                    placeholder="Search by name or email..."
                                    autocomplete="off" spellcheck="false"
                                    style="padding-left: 32px;"
                                    oninput="filterMembers(this.value)"
                                    onfocus="if(this.value.trim()) filterMembers(this.value)">
                                <div id="memberDropdown" style="display:none; position:absolute; top:44px; left:0; right:0; background:#22272b; border:1px solid #38414a; border-radius:8px; box-shadow:0 8px 16px rgba(0,0,0,0.4); z-index:200; padding:8px 0; max-height:240px; overflow-y:auto;">
                                    @foreach($addableUsers as $au)
                                    <div class="member-option"
                                        data-email="{{ $au->email }}"
                                        data-name="{{ strtolower($au->name) }}"
                                        data-emailsearch="{{ strtolower($au->email) }}"
                                        onclick="selectMember('{{ $au->email }}', '{{ addslashes($au->name) }}')"
                                        style="padding:10px 14px; cursor:pointer; display:flex; align-items:center; gap:10px; border-radius:6px; margin:0 4px;"
                                        onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                                        <div style="width:32px; height:32px; border-radius:50%; background:#0052cc; color:white; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:12px; flex-shrink:0;">{{ strtoupper(substr($au->name, 0, 2)) }}</div>
                                        <div>
                                            <div style="font-weight:500; color:white; font-size:14px;">{{ $au->name }}</div>
                                            <div style="font-size:11px; color:#9fadbc;">{{ $au->email }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div id="memberNoResults" style="display:none; padding:10px 14px; color:#9fadbc; font-size:13px;">No users found</div>
                                    @if($addableUsers->count() === 0)
                                    <div style="padding:10px 14px; color:#9fadbc; font-size:13px;">All users are already members</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <label style="font-size:12px; font-weight:600; color:#9fadbc; text-transform:uppercase; letter-spacing:0.5px;">Role</label>
                            <select name="role" class="form-input" style="width:130px;">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-add" style="align-self:flex-end;">Add Member</button>
                    </div>
                </form>

                <div class="member-list">
                    @foreach($workspace->users as $member)
                    <div class="member-row">
                        <div class="member-info">
                            <div class="member-avatar">{{ strtoupper(substr($member->name, 0, 2)) }}</div>
                            <div>
                                <div class="member-name">{{ $member->name }}</div>
                                <div class="member-email">{{ $member->email }}</div>
                            </div>
                            <span class="role-badge {{ $member->pivot->role === 'owner' ? 'owner' : '' }}">{{ $member->pivot->role }}</span>
                        </div>
                        @if($member->pivot->role !== 'owner')
                            <form id="removeMemberForm{{ $member->id }}" method="POST" action="{{ route('workspaces.members.remove', [$workspace, $member]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-remove" onclick="confirmRemoveMember({{ $member->id }}, '{{ addslashes($member->name) }}')">Remove</button>
                            </form>
                        @else
                            <span class="owner-label">Owner</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Danger Zone (Admin only) -->
            @if(auth()->user()->isSystemAdmin())
            <div class="card" style="border-color: rgba(201,55,44,0.3);">
                <div class="card-title" style="color: #ef5c48; border-bottom-color: rgba(201,55,44,0.2);">Danger Zone</div>
                <p style="font-size: 14px; color: #8c9cb8; margin-bottom: 16px;">Permanently delete this workspace and all its boards. This action cannot be undone.</p>
                <button type="button" class="btn-delete" onclick="document.getElementById('deleteModal').style.display='flex'">Delete Workspace</button>
            </div>

            <!-- Delete Modal -->
            <div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:#22272b; border:1px solid #38414a; border-radius:8px; width:100%; max-width:420px; padding:24px; margin:16px;">
                    <div style="font-size:18px; font-weight:700; color:#ef5c48; margin-bottom:8px;">Delete Workspace</div>
                    <p style="font-size:14px; color:#8c9cb8; margin-bottom:20px;">
                        Enter your admin password to confirm deletion of <strong style="color:#b6c2cf;">{{ $workspace->name }}</strong>.
                    </p>
                    @if($errors->has('delete_password'))
                        <div class="alert-error">{{ $errors->first('delete_password') }}</div>
                    @endif
                    <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}">
                        @csrf
                        @method('DELETE')
                        <div class="form-group" style="margin-top:12px;">
                            <label>Admin Password</label>
                            <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                        </div>
                        <div style="display:flex; gap:12px; margin-top:20px;">
                            <button type="submit" class="btn-delete" style="flex:1;">Confirm Delete</button>
                            <button type="button" class="btn-cancel" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>

    <script>
        // Auto-open delete modal if password error
        @if($errors->has('delete_password'))
        document.getElementById('deleteModal').style.display = 'flex';
        @endif

        function filterMembers(val) {
            const dropdown = document.getElementById('memberDropdown');
            const items = document.querySelectorAll('.member-option');
            const noResults = document.getElementById('memberNoResults');
            const q = val.trim().toLowerCase();
            if (!q) { dropdown.style.display = 'none'; return; }
            dropdown.style.display = 'block';
            let visible = 0;
            items.forEach(item => {
                const match = item.dataset.name.includes(q) || item.dataset.emailsearch.includes(q);
                item.style.display = match ? 'flex' : 'none';
                if (match) visible++;
            });
            noResults.style.display = visible === 0 ? 'block' : 'none';
        }

        function selectMember(email, name) {
            document.getElementById('selectedMemberEmail').value = email;
            document.getElementById('memberSearchInput').value = name + ' (' + email + ')';
            document.getElementById('memberDropdown').style.display = 'none';
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#memberSearchInput') && !e.target.closest('#memberDropdown')) {
                document.getElementById('memberDropdown').style.display = 'none';
            }
        });

        document.getElementById('addMemberForm').addEventListener('submit', function(e) {
            if (!document.getElementById('selectedMemberEmail').value) {
                e.preventDefault();
                alert('Please select a member from the dropdown.');
            }
        });

        function confirmRemoveMember(id, name) {
            Swal.fire({
                title: 'Remove member?',
                text: `Are you sure you want to remove ${name} from this workspace?`,
                icon: 'warning',
                background: '#282e33',
                color: '#b6c2cf',
                showCancelButton: true,
                confirmButtonColor: '#c9372c',
                cancelButtonColor: '#323940',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('removeMemberForm' + id).submit();
                }
            });
        }
    </script>
</body>
</html>
