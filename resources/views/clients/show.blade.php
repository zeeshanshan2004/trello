<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $client->name }} - Trello Profile</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/client.css') }}">
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

                <div class="project-mega-card" style="background: #22272b; border-radius: 14px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
                    
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
                                    <div class="mini-task-card" style="background: #22272b; border: 1px solid #38414a; padding: 12px; border-radius: 10px; transition: all 0.2s ease-in-out;">
                                        <div style="color: #b6c2cf; font-size: 13px; margin-bottom: 8px; line-height: 1.4;">{{ $card->title }}</div>
                                        
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <div style="display: flex; align-items: center; gap: 6px; background: rgba(87, 157, 255, 0.1); padding: 3px 10px; border-radius: 6px;">
                                                <span style="width: 5px; height: 5px; border-radius: 50%; background: #579dff; box-shadow: 0 0 5px #579dff;"></span>
                                                <span style="color: #579dff; font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ strtoupper($card->list->name) }}</span>
                                            </div>
                                            
                                            @if($card->due_date)
                                                <span style="font-size: 10px; color: #626971; font-weight: 500;">
                                                    {{ $card->due_date->format('M d') }}
                                                </span>
                                            @endif
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
</body>
</html>