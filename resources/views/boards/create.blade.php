<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Board - Trello</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal {
            background: #22272b;
            border-radius: 12px;
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #38414a;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        .close-btn {
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
        .close-btn:hover {
            background: #2c333a;
        }
        .modal-body {
            padding: 24px;
        }
        .board-preview {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            background: #0052cc;
        }
        .board-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .board-preview-gradient {
            width: 100%;
            height: 100%;
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
        .section {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 12px;
        }
        .background-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #b6c2cf;
            margin-bottom: 8px;
        }
        .required {
            color: #c9372c;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 10px 12px;
            background: #1d2125;
            border: 2px solid #38414a;
            border-radius: 4px;
            color: #b6c2cf;
            font-size: 14px;
            font-family: inherit;
        }
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #0052cc;
        }
        input.error {
            border-color: #c9372c;
        }
        .error-message {
            color: #c9372c;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .workspace-info {
            font-size: 12px;
            color: #9fadbc;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #38414a;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #0052cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            margin-top: 24px;
        }
        .btn-submit:hover {
            background: #0065ff;
        }
        .btn-submit:disabled {
            background: #38414a;
            cursor: not-allowed;
        }
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
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            font-family: inherit;
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
        @media (max-width: 768px) {
            .background-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Create board</h2>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="{{ route('dashboard') }}" class="close-btn" style="order: 2;">×</a>
                    <div class="user-avatar-header" id="userAvatar" onclick="toggleUserDropdown()" style="order: 1;">
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
                            <button type="button" class="dropdown-item" onclick="document.getElementById('logout-form').submit();">
                                Log out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('boards.store') }}" id="boardForm">
                    @csrf

                    <!-- Board Preview -->
                    <div class="board-preview" id="boardPreview">
                        <div class="board-preview-content" id="previewTitle">Board title</div>
                    </div>

                    <!-- Background Selection -->
                    <div class="section">
                        <div class="section-title">Background</div>
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
                            <div class="background-option" data-type="gradient" data-value="light-blue">
                                <div class="background-gradient bg-light-blue"></div>
                            </div>
                            <div class="background-option" data-type="gradient" data-value="medium-blue">
                                <div class="background-gradient bg-medium-blue"></div>
                            </div>
                            <div class="background-option" data-type="gradient" data-value="purple-pink">
                                <div class="background-gradient bg-purple-pink"></div>
                            </div>
                            <div class="background-option" data-type="gradient" data-value="pink-purple">
                                <div class="background-gradient bg-pink-purple"></div>
                            </div>
                        </div>
                        <input type="hidden" name="background_type" id="backgroundType" value="image">
                        <input type="hidden" name="background_value" id="backgroundValue" value="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=400&h=200&fit=crop">
                    </div>

                    <!-- Board Title -->
                    <div class="form-group">
                        <label for="name">
                            Board title <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required
                            placeholder="Enter board title"
                            autofocus
                        >
                        @error('name')
                            <div class="error-message">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                        <div class="error-message" id="titleError" style="display: none;">
                            <span>⚠</span> Board title is required
                        </div>
                    </div>

                    <!-- Workspace Selection -->
                    <div class="form-group">
                        <label for="workspace_id">Workspace</label>
                        <select id="workspace_id" name="workspace_id">
                            @foreach($workspaces as $workspace)
                                <option value="{{ $workspace->id }}" {{ ($defaultWorkspaceId == $workspace->id) ? 'selected' : '' }}>
                                    {{ $workspace->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('workspace_id')
                            <div class="error-message">
                                <span>⚠</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Visibility -->
                    <div class="form-group">
                        <label for="visibility">Visibility</label>
                        <select id="visibility" name="visibility">
                            <option value="workspace" selected>Workspace</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>

                    <!-- Workspace Info -->
                    @if($workspaces->count() > 0)
                        @php
                            $selectedWorkspace = $workspaces->firstWhere('id', $defaultWorkspaceId) ?? $workspaces->first();
                            $remainingBoards = 10 - $selectedWorkspace->boards()->where('is_archived', false)->count();
                        @endphp
                        <div class="workspace-info">
                            <p>This Workspace has <strong>{{ $remainingBoards }}</strong> boards remaining.</p>
                            <p>Free Workspaces can only have 10 open boards.</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-message" style="margin-top: 16px;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn-submit" id="submitBtn">Create</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Background selection
        document.querySelectorAll('.background-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.background-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                const type = this.dataset.type;
                const value = this.dataset.value;
                
                document.getElementById('backgroundType').value = type;
                document.getElementById('backgroundValue').value = value;
                
                // Update preview
                const preview = document.getElementById('boardPreview');
                preview.innerHTML = '<div class="board-preview-content" id="previewTitle">' + 
                    (document.getElementById('name').value || 'Board title') + '</div>';
                
                if (type === 'image') {
                    preview.style.backgroundImage = `url(${value})`;
                    preview.style.backgroundSize = 'cover';
                    preview.style.backgroundPosition = 'center';
                } else {
                    preview.style.backgroundImage = 'none';
                    preview.className = 'board-preview background-gradient';
                    if (value === 'blue') preview.classList.add('bg-blue');
                    else if (value === 'light-blue') preview.classList.add('bg-light-blue');
                    else if (value === 'medium-blue') preview.classList.add('bg-medium-blue');
                    else if (value === 'purple-pink') preview.classList.add('bg-purple-pink');
                    else if (value === 'pink-purple') preview.classList.add('bg-pink-purple');
                }
            });
        });

        // Board title preview update
        document.getElementById('name').addEventListener('input', function() {
            const previewTitle = document.getElementById('previewTitle');
            if (previewTitle) {
                previewTitle.textContent = this.value || 'Board title';
            }
            
            // Validation
            const titleError = document.getElementById('titleError');
            const submitBtn = document.getElementById('submitBtn');
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

        // Workspace change - update remaining boards
        document.getElementById('workspace_id').addEventListener('change', function() {
            // You can fetch remaining boards count via AJAX if needed
        });

        // Form validation on submit
        document.getElementById('boardForm').addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                nameInput.classList.add('error');
                document.getElementById('titleError').style.display = 'flex';
                nameInput.focus();
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
                    if (!document.getElementById('userAvatar').contains(e.target)) {
                        dropdown.classList.remove('active');
                        document.removeEventListener('click', closeHandler);
                    }
                };
                
                setTimeout(() => document.addEventListener('click', closeHandler), 0);
            }
        }
    </script>
</body>
</html>
