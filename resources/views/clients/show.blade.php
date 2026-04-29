<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $client->name }} - Trello Profile</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/client.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="header">
        <div class="header-top">
            <a href="{{ route('dashboard') }}" class="btn-secondary">← Back</a>
        </div>

        <div class="profile-container">
            <div class="profile-avatar-wrapper">
                <div class="avatar-frame">
                    @if($client->image_path)
                        <img src="{{ Storage::url($client->image_path) }}" alt="{{ $client->name }}" class="client-image">
                    @else
                        <div class="client-initials">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
                    @endif
                </div>

                <div class="status-badge">
                    <span class="status-dot"></span> Active
                </div>
            </div>

            <div class="client-info-text">
                <div class="client-name-wrapper">
                    <h1 class="client-name">{{ $client->name }}</h1>
                    <span class="client-tag">Client</span>
                </div>
                <div class="client-contact">
                    <span>{{ $client->email }}</span>
                    @if($client->phone)<span class="contact-sep">•</span><span>{{ $client->phone }}</span>@endif
                </div>
            </div>
        </div>
    </div>

<!-- New design -->
<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    <div class="section">
        <h2 class="section-title" style="font-size: 1.2rem; color: #fff; margin-bottom: 25px; font-weight: 600;">
            Active Projects & Progress ({{ count($boards) }})
        </h2>
        <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; align-items: start;">
            @php
                $cardsByBoard = $cards->groupBy('list.board_id');
            @endphp

            @forelse($boards as $board)
                @php
                    $boardCards = $cardsByBoard->get($board->id, collect());
                @endphp

                <div class="project-mega-card" id="board-card-{{ $board->id }}" style="background: #22272b; border-radius: 14px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 20px rgba(0,0,0,0.2); position: relative;">
                    
                    <!-- Remove Board Button -->
                    <button onclick="confirmRemoveBoard('{{ $board->id }}', '{{ $board->name }}')" style="position: absolute; top: 12px; right: 12px; background: rgba(255, 59, 48, 0.2); border: 1px solid rgba(255, 59, 48, 0.3); backdrop-filter: blur(8px); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #ff3b30; cursor: pointer; z-index: 10; transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 59, 48, 0.4)'" onmouseout="this.style.background='rgba(255, 59, 48, 0.2)'" title="Remove Board">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>

                    <div style="position: relative; height: 110px;">
                        @if($board->background_type == 'image' && $board->background_value)
                            <img src="{{ $board->background_value }}" alt="{{ $board->name }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                        @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2c333a 0%, #1d2125 100%);"></div>
                        @endif
                        
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, #1d2125 5%, transparent 90%);"></div>
                        
                        <div style="position: absolute; bottom: 12px; left: 15px; display: flex; align-items: center; gap: 10px;">
                             <div style="width: 34px; height: 34px; background: rgba(87, 157, 255, 0.2); border: 1px solid rgba(87, 157, 255, 0.3); backdrop-filter: blur(8px); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #579dff; font-weight: 700; font-size: 0.9rem;">
                                {{ strtoupper(substr($board->name, 0, 1)) }}
                            </div>
                            <h3 style="color: white; font-size: 15px; margin: 0; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">{{ $board->name }}</h3>
                        </div>
                    </div>

                    <div class="project-tasks-area" style="padding: 12px 15px 20px 15px; background: #1d2125;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse($boardCards as $card)
                                <a href="{{ route('boards.show', $board->id) . '#card-' . $card->id }}" style="text-decoration: none; display: block;">
                                    <div class="mini-task-card" style="background: #22272b; border: 1px solid #38414a; padding: 12px; border-radius: 10px; transition: all 0.2s ease-in-out; position: relative;">
                                        
                                        <!-- Labels -->
                                        @if($card->labels && count($card->labels) > 0)
                                            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 8px;">
                                                @foreach($card->labels as $labelId)
                                                    @php
                                                        $label = $board->labels->firstWhere('id', $labelId);
                                                    @endphp
                                                    @if($label)
                                                        <div style="height: 8px; width: 40px; background: {{ $label->color }}; border-radius: 4px;" title="{{ $label->name }}"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        <div style="color: #b6c2cf; font-size: 14px; margin-bottom: 12px; line-height: 1.4; font-weight: 500;">{{ $card->title }}</div>
                                        
                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <!-- Due Date -->
                                                @if($card->due_date)
                                                    @php
                                                        $isOverdue = $card->due_date->isPast() && !$card->is_completed;
                                                        $bg = $isOverdue ? '#f87168' : 'rgba(87, 157, 255, 0.1)';
                                                        $color = $isOverdue ? '#1d2125' : '#579dff';
                                                    @endphp
                                                    <div style="display: flex; align-items: center; gap: 4px; background: {{ $bg }}; padding: 3px 8px; border-radius: 4px; color: {{ $color }}; font-size: 11px; font-weight: 600;">
                                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                                        {{ $card->due_date->format('M d') }}
                                                    </div>
                                                @endif

                                                <!-- Attachments Count -->
                                                @if($card->attachments->count() > 0)
                                                    <div style="display: flex; align-items: center; gap: 4px; color: #9fadbc; font-size: 11px; font-weight: 500;">
                                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                                        {{ $card->attachments->count() }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.05); padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.05);">
                                                <span style="color: #9fadbc; font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ strtoupper($card->list->name) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div style="text-align: center; padding: 20px; border: 1px dashed #38414a; border-radius: 10px; background: rgba(255,255,255,0.02);">
                                    <p style="font-size: 11px; color: #626971; margin: 0;">No pending actions</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div style="padding: 12px 15px; background: #22272b; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 10px; color: #626971; font-weight: 600;">{{ $boardCards->count() }} ACTIVE TASKS</span>
                        <a href="{{ route('boards.show', $board) }}" style="font-size: 11px; color: #579dff; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                            Board <span style="font-size: 14px;">→</span>
                        </a>
                    </div>
                </div>
            @empty
                @endforelse
        </div>
    </div>
</div>

<script>
    function confirmRemoveBoard(boardId, boardName) {
        Swal.fire({
            title: 'Remove Board?',
            text: `Are you sure you want to remove "${boardName}" from this client?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3b30',
            cancelButtonColor: '#38414a',
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel',
            background: '#1d2125',
            color: '#fff',
            iconColor: '#ff9f1a',
            backdrop: `rgba(0,0,0,0.6)`
        }).then((result) => {
            if (result.isConfirmed) {
                removeBoard(boardId);
            }
        });
    }

    async function removeBoard(boardId) {
        try {
            const response = await fetch(`/boards/${boardId}/detach-client`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    client_id: '{{ $client->id }}'
                })
            });

            const data = await response.json();

            if (data.success) {
                // Smoothly remove the card from UI
                const card = document.getElementById(`board-card-${boardId}`);
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.remove();
                    // Update count if necessary
                    location.reload(); // Simpler than updating all UI parts manually
                }, 300);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to remove board',
                    icon: 'error',
                    background: '#1d2125',
                    color: '#fff'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong',
                icon: 'error',
                background: '#1d2125',
                color: '#fff'
            });
        }
    }
</script>

</body>
</html>