<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $workspace->name }} - Trello</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            min-height: 100vh;
            /* Hide scrollbars while preserving scroll functionality */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }
        
        /* Hide scrollbar for Webkit browsers (Chrome, Safari, Opera) */
        body::-webkit-scrollbar {
            display: none;
        }
        .header {
            background: #22272b;
            padding: 16px 32px;
            border-bottom: 1px solid #38414a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .workspace-info { display: flex; align-items: center; gap: 12px; }
        .workspace-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            background: #0052cc;
            overflow: hidden;
        }
        .workspace-icon.blue { background: #0052cc; }
        .workspace-icon.green { background: #0c66e4; }
        .workspace-icon.red { background: #c9372c; }
        .workspace-icon.purple { background: #7c3aed; }
        .workspace-icon.orange { background: #f97316; }
        .workspace-icon.pink { background: #ec4899; }
        .workspace-name { font-size: 18px; font-weight: 600; color: white; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .header-actions { display: flex; align-items: center; gap: 12px; }
        .user-avatar-header {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0c66e4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
        }
        .user-dropdown {
            position: absolute;
            top: 40px;
            right: 0;
            background: #282e33;
            border: 1px solid #38414a;
            border-radius: 4px;
            width: 320px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
            display: none;
            z-index: 1001;
            padding: 8px 0;
            text-align: left;
        }
        .user-dropdown.active {
            display: block;
        }
        .dropdown-item {
            padding: 8px 16px;
            color: #b6c2cf;
            font-size: 14px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        .dropdown-item:hover {
            background: #323940;
            color: white;
        }
        .dropdown-divider {
            height: 1px;
            background: #38414a;
            margin: 8px 0;
        }
        .dropdown-user-info {
            padding: 8px 16px;
            font-size: 12px;
            color: #8c9cb8;
            border-bottom: 1px solid #38414a;
            margin-bottom: 8px;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .btn { padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: #0052cc; color: white; }
        .btn-secondary { background: #22272b; color: #b6c2cf; border: 1px solid #38414a; }
        .container { max-width: 1400px; margin: 0 auto; padding: 32px; }
        .section { margin-bottom: 32px; }
        .section-title { font-size: 16px; font-weight: 600; color: #b6c2cf; margin-bottom: 16px; }
        .boards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .board-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            height: 96px;
        }
        .board-card:hover { transform: translateY(-2px); }
        .board-card-image { width: 100%; height: 100%; object-fit: cover; }
        .board-card-gradient {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
        }
        .board-card-gradient.blue { background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%); }
        .board-card-gradient.purple { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); }
        .board-card-gradient.orange { background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); }
        .board-card-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            font-size: 14px;
            font-weight: 600;
        }
       .board-card.create {
    background: #22272b;
    border: 2px dashed #38414a;
    display: flex;
    /* Add these two lines */
    flex-direction: column; 
    text-align: center;
    
    align-items: center;
    justify-content: center;
    color: #b6c2cf;
    font-size: 14px;
    font-weight: 500;
}
        .board-card.create:hover {
            background: #2c333a;
            border-color: #45505c;
        }
        .members-section {
            background: #22272b;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .member-card {
            background: #2c333a;
            padding: 12px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: visible;
        }
        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-transform: uppercase;
            flex-shrink: 0;
        }
        .member-info { flex: 1; min-width: 0; overflow: hidden; }
        .member-name { font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px; }
        .member-role {
            font-size: 12px;
            color: #9fadbc;
            text-transform: capitalize;
        }
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .create-board-modal {
            background: #22272b;
            border-radius: 8px;
            width: 100%;
            max-width: 420px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #38414a;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }

        .close-modal-btn {
            background: none;
            border: none;
            color: #b6c2cf;
            font-size: 24px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .close-modal-btn:hover {
            background: #2c333a;
        }

        .modal-body {
            padding: 20px;
        }

        .board-preview-modal {
            width: 100%;
            height: 150px;
            border-radius: 6px;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
            background: #0052cc;
        }

        .board-preview-modal img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .board-preview-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px;
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-weight: 600;
        }

        .background-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }

        .background-option {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
            position: relative;
        }

        .background-option:hover {
            border-color: #0052cc;
        }

        .background-option.selected {
            border-color: #0052cc;
            border-width: 3px;
        }

        .background-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .background-gradient {
            width: 100%;
            height: 100%;
        }

        .bg-blue { background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%); }
        .bg-light-blue { background: linear-gradient(135deg, #0c66e4 0%, #1c88ff 100%); }
        .bg-medium-blue { background: linear-gradient(135deg, #1c88ff 0%, #4db8ff 100%); }
        .bg-purple-pink { background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%); }
        .bg-pink-purple { background: linear-gradient(135deg, #ec4899 0%, #7c3aed 100%); }

        .modal-form-group {
            margin-bottom: 16px;
        }

        .modal-form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 8px;
        }

        .modal-form-group .required {
            color: #c9372c;
        }

        .modal-form-group input[type="text"],
        .modal-form-group select {
            width: 100%;
            padding: 10px 12px;
            background: #1d2125;
            border: 2px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-family: inherit;
        }

        .modal-form-group input[type="text"]:focus,
        .modal-form-group select:focus {
            outline: none;
            border-color: #0052cc;
        }

        .modal-form-group input.error {
            border-color: #c9372c;
        }

        .error-message-modal {
            color: #c9372c;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .workspace-info-modal {
            font-size: 12px;
            color: #9fadbc;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #38414a;
            line-height: 1.5;
        }

        .btn-submit-modal {
            width: 100%;
            padding: 10px;
            background: #0052cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            margin-top: 16px;
        }

        .btn-submit-modal:hover {
            background: #0065ff;
        }

        .btn-submit-modal:disabled {
            background: #38414a;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .container { padding: 16px; }
            .boards-grid { grid-template-columns: 1fr; }
            .background-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .create-board-modal {
                max-width: 90%;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back</a>
            <div class="workspace-info">
                <div class="workspace-icon {{ $workspace->color }}">{!! $workspace->display_icon !!}</div>
                <div>
                    <div class="workspace-name">{{ $workspace->name }}</div>
                    @if($workspace->description)
                        <div style="font-size: 12px; color: #9fadbc; margin-top: 4px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $workspace->description }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="header-actions">
            @if($userRole === 'owner' || $userRole === 'admin')
                <a href="{{ route('workspaces.edit', $workspace) }}" class="btn btn-secondary">Settings</a>
            @endif
            @if($userRole === 'owner')
                <!-- <form id="deleteWorkspaceForm" method="POST" action="{{ route('workspaces.destroy', $workspace) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" style="background: #c9372c; color: white; border-color: #c9372c;" onconfirmDeleteWorkspace()click="">Delete</button>
                </form> -->
            @endif
            <div class="user-avatar-header" id="userAvatar" onclick="toggleUserDropdown()">
                {{ substr(auth()->user()->name, 0, 2) }}
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-user-info">
                        <strong>{{ auth()->user()->name }}</strong><br>
                        {{ auth()->user()->email }}
                    </div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item">Profile & Settings</a>
                    <div class="dropdown-divider"></div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Log out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Members Section -->
        @if($userRole === 'owner' || $userRole === 'admin')
        <div class="members-section">
            <h2 class="section-title">Members (<span id="memberCount">{{ $workspace->member_count }}</span>)</h2>
            <div class="members-grid" id="membersGrid">
                @foreach($workspace->users as $member)
                    <div class="member-card" id="member-card-{{ $member->id }}">
                        @php
                            $avatarColors = ['#0052cc','#c9372c','#7c3aed','#0c66e4','#f97316','#ec4899','#0e7490','#15803d','#b45309','#6d28d9'];
                            $avatarColor = $avatarColors[$member->id % count($avatarColors)];
                        @endphp
                        <div class="member-avatar" style="background: {{ $avatarColor }};">{{ substr($member->name, 0, 2) }}</div>
                        <div class="member-info">
                            <div class="member-name">{{ $member->name }}</div>
                            <div class="member-role">{{ $member->pivot->role }}</div>
                        </div>
                        @if($member->pivot->role !== 'owner')
                        <div style="position: relative; margin-left: auto; flex-shrink: 0; align-self: center;">
                            <button onclick="toggleMemberMenu({{ $member->id }})" style="background: none; border: none; color: #9fadbc; cursor: pointer; padding: 4px 6px; border-radius: 4px; font-size: 16px; line-height: 1;" title="Options">⋯</button>
                            <div id="member-menu-{{ $member->id }}" style="display:none; position: absolute; right: 0; top: 28px; background: #2c333a; border: 1px solid #38414a; border-radius: 6px; min-width: 130px; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.4);">
                                <div onclick="removeMemberAjax({{ $workspace->id }}, {{ $member->id }}, '{{ csrf_token() }}')" style="width: 100%; text-align: left; padding: 10px 14px; color: #f87171; cursor: pointer; font-size: 13px; border-radius: 6px;" onmouseover="this.style.background='#3c444d'" onmouseout="this.style.background='transparent'">Remove</div>
                            </div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Board Access Feedback (toast) -->
            <div id="board-access-feedback" style="display:none;"></div>

            <div style="margin-top: 24px; position: relative;">
                <input
                    id="grantSearchInput"
                    type="text"
                    placeholder="Search member by name or email..."
                    autocomplete="off"
                    spellcheck="false"
                    oninput="filterGrantUsers(this.value)"
                    onfocus="if(this.value.trim().length > 0) filterGrantUsers(this.value)"
                    style="width: 100%; padding: 12px 14px 12px 40px; background: #1d2125; border: 1px solid #38414a; border-radius: 8px; color: #b6c2cf; font-size: 14px; outline: none; box-sizing: border-box; transition: all 0.2s;"
                    onfocus="this.style.borderColor='#0052cc'; this.style.boxShadow='0 0 0 2px rgba(0,82,204,0.2)'"
                    onblur="this.style.borderColor='#38414a'; this.style.boxShadow='none'"
                />
                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9fadbc; pointer-events:none; display:flex; align-items:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <div id="grantDropdown" style="display:none; position: absolute; top: 44px; left: 0; background: #22272b; border: 1px solid #38414a; border-radius: 8px; width: 100%; box-shadow: 0 8px 16px rgba(0,0,0,0.4); z-index: 200; padding: 8px 0; max-height: 260px; overflow-y: auto;">
                    @foreach($grantableUsers as $gu)
                    <div class="grant-user-item"
                        onclick="selectGrantUser({{ $gu->id }}, '{{ addslashes($gu->name) }}', {{ $workspace->id }}, '{{ csrf_token() }}')"
                        data-uid="{{ $gu->id }}"
                        data-name="{{ strtolower($gu->name) }}"
                        data-email="{{ strtolower($gu->email) }}"
                        style="padding: 10px 14px; cursor: pointer; color: #b6c2cf; font-size: 14px; display: flex; align-items: center; gap: 10px; border-radius: 6px; margin: 0 4px;"
                        onmouseover="this.style.background='#2c333a'" onmouseout="this.style.background='transparent'">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #0052cc; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 12px; flex-shrink: 0;">{{ strtoupper(substr($gu->name, 0, 2)) }}</div>
                        <div style="flex:1;">
                            <div style="font-weight: 500; color: white;">{{ $gu->name }}</div>
                            <div style="font-size: 11px; color: #9fadbc;">{{ $gu->email }}</div>
                        </div>
                    </div>
                    @endforeach
                    <div id="grantNoResults" style="display:none; padding: 10px 14px; color: #9fadbc; font-size: 13px;">No members found</div>
                    @if($grantableUsers->count() === 0)
                    <div style="padding: 10px 14px; color: #9fadbc; font-size: 13px;">All members already have access</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Boards Section -->
        <div class="section">
            <h2 class="section-title">Boards ({{ count($workspace->boards) }})</h2>
            <div class="boards-grid">
                @foreach($workspace->boards as $board)
                    <a href="{{ route('boards.show', $board) }}" style="text-decoration: none;">
                        <div class="board-card">
                            @if($board->background_type == 'image' && $board->background_value)
                                <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                            @elseif($board->background_type == 'gradient')
                                <div class="board-card-gradient {{ $board->background_value ?: 'blue' }}"></div>
                            @else
                                <div class="board-card-gradient blue"></div>
                            @endif
                            <div class="board-card-title">
                                {{ $board->name }}
                                @if($board->description)
                                    <br><span style="font-size: 12px; font-weight: 400;">{{ $board->description }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach

                @if($isWorkspaceMember)
                    <div class="board-card create" onclick="openCreateBoardModal({{ $workspace->id }})" style="cursor: pointer;">
                        <div>Create new board</div>
                        @php
                            $remainingBoards = 10 - $workspace->boards()->where('is_archived', false)->count();
                        @endphp
                        @if($remainingBoards > 0)
                            <div style="font-size: 12px; margin-top: 4px; color: #6b778c;">{{ $remainingBoards }} remaining</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="section" style="margin-top: 48px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <h2 class="section-title" style="margin-bottom: 0;">Clients ({{ count($clients) }})</h2>
            </div>
            <div class="boards-grid">
                @foreach($clients as $client)
                    <a href="{{ route('clients.show', $client) }}" style="text-decoration: none;">
                        <div class="board-card client-card" style="position: relative; background: #22272b; border: 1px solid #38414a;">
                            @if($client->image_path)
                                <img src="{{ Storage::url($client->image_path) }}" class="board-card-image" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="board-card-gradient" style="display: none; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: rgba(255,255,255,0.8);">
                                    {{ strtoupper(substr($client->name, 0, 1)) }}
                                </div>
                            @else
                                <div class="board-card-gradient" style="display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; width: 100%; height: 100%; background: linear-gradient(135deg, #1c2b41 0%, #0052cc 100%); color: rgba(255,255,255,0.8);">
                                    {{ strtoupper(substr($client->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="board-card-title" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
                                <div style="font-weight: 700;">{{ $client->name }}</div>
                                <div style="font-size: 11px; font-weight: 400; opacity: 0.8; margin-top: 2px;">{{ $client->email }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach

                <div class="board-card create" onclick="openClientModal()" style="cursor: pointer; background: #1d2125; border: 2px dashed #38414a; height: 96px;">
                    <div style="font-size: 14px; font-weight: 600; color: #9fadbc;">Add Client</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Board Modal -->
    <div class="modal-overlay" id="createBoardModal">
        <div class="create-board-modal">
            <div class="modal-header">
                <h2 class="modal-title">Create board</h2>
                <button type="button" class="close-modal-btn" onclick="closeCreateBoardModal()">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('boards.store') }}" id="boardFormModal">
                    @csrf

                    <!-- Board Preview -->
                    <div class="board-preview-modal" id="boardPreviewModal">
                        <div class="board-preview-content" id="previewTitleModal">Board title</div>
                    </div>

                    <!-- Background Selection -->
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 600; color: #b6c2cf; margin-bottom: 10px;">Background</div>
                        <div class="background-grid">
                            <!-- Images -->
                            <div class="background-option selected" data-type="image" data-value="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=100&h=100&fit=crop" alt="Mountains">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=100&h=100&fit=crop" alt="Sunset">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=100&h=100&fit=crop" alt="Forest">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1448375240586-882707db888b?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1448375240586-882707db888b?w=100&h=100&fit=crop" alt="Road">
                            </div>
                            <div class="background-option" data-type="image" data-value="https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=400&h=200&fit=crop">
                                <img src="https://images.unsplash.com/photo-1518837695005-2083093ee35b?w=100&h=100&fit=crop" alt="Ocean">
                            </div>
                            <!-- Gradients -->
                            <div class="background-option" data-type="gradient" data-value="blue">
                                <div class="background-gradient bg-blue"></div>
                            </div>
                        </div>
                        <input type="hidden" name="background_type" id="backgroundTypeModal" value="image">
                        <input type="hidden" name="background_value" id="backgroundValueModal" value="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop">
                    </div>

                    <!-- Board Title -->
                    <div class="modal-form-group">
                        <label for="nameModal">
                            Board title <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nameModal" 
                            name="name" 
                            required
                            placeholder="Enter board title"
                            autofocus
                        >
                        <div class="error-message-modal" id="titleErrorModal" style="display: none;">
                            <span>⚠</span> Board title is required
                        </div>
                        @error('name')
                            <div class="error-message-modal">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Workspace Selection -->
                    <div class="modal-form-group">
                        <label for="workspace_idModal">Workspace</label>
                        <select id="workspace_idModal" name="workspace_id" required>
                            <option value="{{ $workspace->id }}" selected>{{ $workspace->name }}</option>
                        </select>
                        @error('workspace_id')
                            <div class="error-message-modal">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Visibility -->
                    <div class="modal-form-group">
                        <label for="visibilityModal">Visibility</label>
                        <select id="visibilityModal" name="visibility">
                            <option value="workspace" selected>Workspace</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>

                    <!-- Workspace Info -->
                    <div class="workspace-info-modal">
                        <p>This Workspace has <strong id="remainingBoardsCount">{{ 10 - $workspace->boards()->where('is_archived', false)->count() }}</strong> boards remaining.</p>
                        <p>Free Workspaces can only have 10 open boards.</p>
                    </div>

                    @if($errors->any())
                        <div class="error-message-modal" style="margin-top: 16px;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn-submit-modal" id="submitBtnModal">Create</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Client Modal -->
    <div id="clientModal" class="modal-overlay" style="z-index: 3000;">
        <div class="create-board-modal" style="max-width: 480px;">
            <div class="modal-header">
                <h2 class="modal-title">Add Client</h2>
                <button onclick="closeClientModal()" class="close-modal-btn">×</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-form-group">
                        <label>Client Name <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="Full Name">
                    </div>
                    <div class="modal-form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required placeholder="client@example.com" style="width: 100%; padding: 10px 12px; background: #1d2125; border: 2px solid #38414a; border-radius: 4px; color: #b6c2cf; font-size: 14px;">
                    </div>
                    <div class="modal-form-group">
                        <label>Father Name</label>
                        <input type="text" name="father_name" placeholder="Father Name">
                    </div>
                    <div class="modal-form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="+1234567890">
                    </div>
                    <div class="modal-form-group">
                        <label>Client Image</label>
                        <input type="file" name="image" accept="image/*" style="width: 100%; color: #b6c2cf;">
                    </div>
                    <button type="submit" class="btn-submit-modal">Add Client</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openClientModal() {
            document.getElementById('clientModal').classList.add('active');
        }
        function closeClientModal() {
            document.getElementById('clientModal').classList.remove('active');
        }

        function openCreateBoardModal(workspaceId = null) {
            const modal = document.getElementById('createBoardModal');
            modal.classList.add('active');
            
            if (workspaceId) {
                document.getElementById('workspace_idModal').value = workspaceId;
            }
            
            document.getElementById('nameModal').focus();
        }

        function closeCreateBoardModal() {
            document.getElementById('createBoardModal').classList.remove('active');
            document.getElementById('boardFormModal').reset();
            document.getElementById('titleErrorModal').style.display = 'none';
            document.getElementById('nameModal').classList.remove('error');
            document.getElementById('submitBtnModal').disabled = false;
            
            // Reset to first selected background
            document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
            document.querySelector('.background-option').classList.add('selected');
            document.getElementById('backgroundTypeModal').value = 'image';
            document.getElementById('backgroundValueModal').value = 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop';
            updatePreview('image', 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop');
        }

        function updatePreview(type, value) {
            const preview = document.getElementById('boardPreviewModal');
            const title = document.getElementById('previewTitleModal').textContent || 'Board title';
            preview.innerHTML = '<div class="board-preview-content" id="previewTitleModal">' + title + '</div>';
            
            if (type === 'image') {
                preview.style.backgroundImage = `url(${value})`;
                preview.style.backgroundSize = 'cover';
                preview.style.backgroundPosition = 'center';
                preview.className = 'board-preview-modal';
            } else {
                preview.style.backgroundImage = 'none';
                preview.className = 'board-preview-modal background-gradient';
                if (value === 'blue') preview.classList.add('bg-blue');
                else if (value === 'light-blue') preview.classList.add('bg-light-blue');
                else if (value === 'medium-blue') preview.classList.add('bg-medium-blue');
                else if (value === 'purple-pink') preview.classList.add('bg-purple-pink');
                else if (value === 'pink-purple') preview.classList.add('bg-pink-purple');
            }
        }

        // Background selection
        document.querySelectorAll('.background-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                const type = this.dataset.type;
                const value = this.dataset.value;
                
                document.getElementById('backgroundTypeModal').value = type;
                document.getElementById('backgroundValueModal').value = value;
                
                updatePreview(type, value);
            });
        });

        // Board title preview update
        document.getElementById('nameModal').addEventListener('input', function() {
            const previewTitle = document.getElementById('previewTitleModal');
            if (previewTitle) {
                previewTitle.textContent = this.value || 'Board title';
            }
            
            // Validation
            const titleError = document.getElementById('titleErrorModal');
            const submitBtn = document.getElementById('submitBtnModal');
            if (this.value.trim() === '') {
                this.classList.add('error');
                titleError.style.display = 'flex';
                submitBtn.disabled = true;
            } else {
                this.classList.remove('error');
                titleError.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Form validation on submit
        document.getElementById('boardFormModal').addEventListener('submit', function(e) {
            const nameInput = document.getElementById('nameModal');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                nameInput.classList.add('error');
                document.getElementById('titleErrorModal').style.display = 'flex';
                nameInput.focus();
            }
        });

        // Close modal on overlay click
        document.getElementById('createBoardModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateBoardModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateBoardModal();
            }
        });
    </script>
    <script>
        const BASE_URL = '{{ rtrim(url('/'), '/') }}';
        function grantBoardAccess(workspaceId, userId, userName, csrfToken) {
            fetch(`${BASE_URL}/workspaces/${workspaceId}/members/${userId}/board-access`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                showBoardAccessFeedback(data.success, data.message);
                if (data.success) {
                    // Add member card to grid if not already there
                    if (!document.getElementById('member-card-' + userId)) {
                        const grid = document.getElementById('membersGrid');
                        const initials = userName.substring(0, 2).toUpperCase();
                        const card = document.createElement('div');
                        card.className = 'member-card';
                        card.id = 'member-card-' + userId;
                        card.innerHTML = `
                            <div class="member-avatar">${initials}</div>
                            <div class="member-info">
                                <div class="member-name">${userName}</div>
                                <div class="member-role">member</div>
                            </div>
                            <div style="position: relative; margin-left: auto;">
                                <button onclick="toggleMemberMenu(${userId})" style="background: none; border: none; color: #9fadbc; cursor: pointer; padding: 4px 6px; border-radius: 4px; font-size: 16px; line-height: 1;">⋯</button>
                                <div id="member-menu-${userId}" style="display:none; position: absolute; right: 0; top: 28px; background: #2c333a; border: 1px solid #38414a; border-radius: 6px; min-width: 130px; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.4);">
                                    <div onclick="removeMemberAjax(${workspaceId}, ${userId}, '${csrfToken}')" style="width: 100%; text-align: left; padding: 10px 14px; color: #f87171; cursor: pointer; font-size: 13px; border-radius: 6px;" onmouseover="this.style.background='#3c444d'" onmouseout="this.style.background='transparent'">Remove</div>
                                </div>
                            </div>`;
                        grid.appendChild(card);
                        // Update count
                        const countEl = document.getElementById('memberCount');
                        if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;
                    }
                    // Remove from dropdown
                    const dropdown = document.getElementById('grantDropdown');
                    if (dropdown) {
                        const items = dropdown.querySelectorAll('[data-uid]');
                        items.forEach(i => { if (i.dataset.uid == userId) i.remove(); });
                    }
                    document.getElementById('grantSearchInput').value = '';
                }
            })
            .catch(() => showBoardAccessFeedback(false, 'An unexpected error occurred.'));
        }

        function selectGrantUser(userId, userName, workspaceId, csrfToken) {
            document.getElementById('grantDropdown').style.display = 'none';
            document.getElementById('grantSearchInput').value = '';
            grantBoardAccess(workspaceId, userId, userName, csrfToken);
        }

        function filterGrantUsers(query) {
            const d = document.getElementById('grantDropdown');
            const q = query.toLowerCase().trim();
            if (q.length < 1) { d.style.display = 'none'; return; }
            d.style.display = 'block';
            const items = document.querySelectorAll('.grant-user-item');
            const noResults = document.getElementById('grantNoResults');
            let visible = 0;
            items.forEach(item => {
                const name = item.dataset.name || '';
                if (name.includes(q)) { item.style.display = 'flex'; visible++; }
                else item.style.display = 'none';
            });
            if (noResults) noResults.style.display = (visible === 0 && items.length > 0) ? 'block' : 'none';
        }

        function toggleMemberMenu(userId) {
            const menu = document.getElementById('member-menu-' + userId);
            // Close all other menus
            document.querySelectorAll('[id^="member-menu-"]').forEach(m => {
                if (m.id !== 'member-menu-' + userId) m.style.display = 'none';
            });
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        function removeMemberAjax(workspaceId, userId, csrfToken) {
            Swal.fire({
                title: 'Remove member?',
                text: 'This will remove their access.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c9372c',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Remove',
                background: '#22272b',
                color: '#b6c2cf',
            }).then((result) => {
                if (!result.isConfirmed) return;
                const card = document.getElementById('member-card-' + userId);
                const name = card ? card.querySelector('.member-name')?.textContent.trim() : '';
                fetch(`${BASE_URL}/workspaces/${workspaceId}/members/${userId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(() => {
                    if (card) card.remove();
                    const countEl = document.getElementById('memberCount');
                    if (countEl) countEl.textContent = parseInt(countEl.textContent) - 1;
                    // Add back to search dropdown
                    const dropdown = document.getElementById('grantDropdown');
                    if (dropdown && name) {
                        const initials = name.substring(0, 2).toUpperCase();
                        const item = document.createElement('div');
                        item.className = 'grant-user-item';
                        item.dataset.uid = userId;
                        item.dataset.name = name.toLowerCase();
                        item.dataset.email = '';
                        item.style.cssText = 'padding:10px 14px; cursor:pointer; color:#b6c2cf; font-size:14px; display:none; align-items:center; gap:10px; border-radius:6px; margin:0 4px;';
                        item.innerHTML = `<div style="width:32px;height:32px;border-radius:50%;background:#0052cc;color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;flex-shrink:0;">${initials}</div><div style="flex:1;"><div style="font-weight:500;color:white;">${name}</div></div>`;
                        item.onmouseover = () => item.style.background = '#2c333a';
                        item.onmouseout = () => item.style.background = 'transparent';
                        item.onclick = () => selectGrantUser(userId, name, workspaceId, csrfToken);
                        dropdown.appendChild(item);
                    }
                    showBoardAccessFeedback(true, 'Access removed successfully.');
                })
                .catch(() => showBoardAccessFeedback(false, 'Could not remove member.'));
            });
        }

        function showBoardAccessFeedback(success, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: success ? 'success' : 'error',
                title: message,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                background: '#22272b',
                color: '#b6c2cf',
                customClass: { popup: 'swal2-toast-dark' }
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="member-menu-"]') && !e.target.closest('button[onclick^="toggleMemberMenu"]')) {
                document.querySelectorAll('[id^="member-menu-"]').forEach(m => m.style.display = 'none');
            }
            if (!e.target.closest('#grantDropdown') && !e.target.closest('#grantSearchInput')) {
                const d = document.getElementById('grantDropdown');
                if (d) d.style.display = 'none';
            }
        });
    </script>
    <script>
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
            
            // Close when clicking outside
            if (dropdown.classList.contains('active')) {
                const closeHandler = (e) => {
                    const avatar = document.getElementById('userAvatar');
                    if (avatar && !avatar.contains(e.target)) {
                        dropdown.classList.remove('active');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }

        function confirmDeleteWorkspace() {
            Swal.fire({
                title: 'Delete workspace?',
                text: "Are you sure you want to delete this workspace? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#eb5a46',
                cancelButtonColor: '#38414a',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                background: '#22272b',
                color: '#b6c2cf'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteWorkspaceForm').submit();
                }
            });
        }
    </script>
</body>
</html>
