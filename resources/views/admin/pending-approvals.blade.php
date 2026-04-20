<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pending Approvals - Admin</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #1d2125; color: #b6c2cf; min-height: 100vh; }

        .top-header {
            position: fixed; top: 0; left: 0; right: 0; height: 48px;
            background: #22272b; border-bottom: 1px solid #38414a;
            display: flex; align-items: center; padding: 0 24px;
            z-index: 100; gap: 16px;
        }
        .logo { font-size: 18px; font-weight: 700; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .logo-box { width: 24px; height: 24px; background: #0052cc; border-radius: 4px; }
        .back-btn {
            margin-left: auto; padding: 6px 14px; background: #2c333a;
            border: 1px solid #38414a; border-radius: 4px; color: #b6c2cf;
            font-size: 13px; cursor: pointer; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .back-btn:hover { background: #38414a; }

        .page-wrap { max-width: 900px; margin: 0 auto; padding: 80px 24px 40px; }

        .page-header { margin-bottom: 28px; }
        .page-title { font-size: 22px; font-weight: 700; color: white; margin-bottom: 4px; }
        .page-subtitle { font-size: 13px; color: #9fadbc; }

        .badge {
            display: inline-flex; align-items: center; justify-content: center;
            background: #eb5a46; color: white; font-size: 11px; font-weight: 700;
            border-radius: 10px; padding: 2px 7px; margin-left: 8px;
        }

        .filter-bar {
            display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .filter-input {
            flex: 1; min-width: 200px; padding: 8px 12px;
            background: #2c333a; border: 1px solid #38414a; border-radius: 4px;
            color: #b6c2cf; font-size: 13px; outline: none;
        }
        .filter-input:focus { border-color: #579dff; }

        .empty-state {
            text-align: center; padding: 60px 20px; color: #9fadbc;
        }
        .empty-state svg { margin-bottom: 16px; opacity: 0.4; }
        .empty-state p { font-size: 15px; }

        .requests-list { display: flex; flex-direction: column; gap: 10px; }

        .request-card {
            background: #22272b; border: 1px solid #38414a; border-radius: 8px;
            padding: 16px 20px; display: flex; align-items: center; gap: 16px;
            transition: border-color 0.15s;
        }
        .request-card:hover { border-color: #45505c; }

        .avatar {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: white; flex-shrink: 0;
        }

        .request-info { flex: 1; min-width: 0; }
        .request-name { font-size: 14px; font-weight: 600; color: white; margin-bottom: 2px; }
        .request-email { font-size: 12px; color: #9fadbc; margin-bottom: 6px; }
        .request-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .meta-tag {
            font-size: 11px; padding: 2px 8px; border-radius: 3px;
            background: #2c333a; border: 1px solid #38414a; color: #9fadbc;
        }
        .meta-tag.board { color: #579dff; border-color: #1c3a5e; background: #0d2240; }
        .meta-tag.workspace { color: #9fadbc; }
        .meta-tag.time { color: #6b778c; }

        .request-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-approve {
            padding: 7px 16px; background: #0052cc; color: white;
            border: none; border-radius: 4px; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: background 0.15s;
        }
        .btn-approve:hover { background: #0065ff; }
        .btn-reject {
            padding: 7px 16px; background: transparent; color: #9fadbc;
            border: 1px solid #38414a; border-radius: 4px; font-size: 13px;
            cursor: pointer; transition: all 0.15s;
        }
        .btn-reject:hover { background: #2c333a; color: #eb5a46; border-color: #eb5a46; }

        .toast {
            position: fixed; bottom: 24px; right: 24px; padding: 12px 20px;
            border-radius: 6px; font-size: 13px; font-weight: 500; z-index: 9999;
            opacity: 0; transform: translateY(8px); transition: all 0.25s;
            pointer-events: none;
        }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success { background: #1c3a1c; border: 1px solid #2e7d32; color: #4caf50; }
        .toast.error { background: #3a1c1c; border: 1px solid #c62828; color: #ef5350; }

        .section-label {
            font-size: 11px; font-weight: 700; color: #6b778c;
            text-transform: uppercase; letter-spacing: 0.8px;
            margin: 24px 0 10px; padding-bottom: 8px;
            border-bottom: 1px solid #2c333a;
        }
    </style>
</head>
<body>
    <div class="top-header">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-box"></div>
            Trello
        </a>
        <span style="color: #6b778c; font-size: 13px;">/ Admin / Pending Approvals</span>
        <a href="{{ route('dashboard') }}" class="back-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            Dashboard
        </a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <div class="page-title">
                Pending Approvals
                @if($requests->count() > 0)
                    <span class="badge">{{ $requests->count() }}</span>
                @endif
            </div>
            <div class="page-subtitle">Review and manage board join requests from users</div>
        </div>

        @if($requests->count() > 0)
        <div class="filter-bar">
            <input type="text" class="filter-input" id="filterInput" placeholder="Search by name, email or board..." oninput="filterRequests(this.value)">
        </div>
        @endif

        <div id="requestsList">
            @if($requests->count() === 0)
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <p>No pending approval requests</p>
                </div>
            @else
                @php
                    $grouped = $requests->groupBy(fn($r) => $r->board->workspace->name ?? 'Unknown');
                @endphp
                @foreach($grouped as $workspaceName => $groupRequests)
                    <div class="section-label">{{ $workspaceName }}</div>
                    <div class="requests-list">
                        @foreach($groupRequests as $req)
                        @php
                            $colors = ['#0052cc','#ae2a19','#216e4e','#974f0c','#5e4db2','#c9372c'];
                            $color = $colors[$req->user->id % count($colors)];
                        @endphp
                        <div class="request-card" id="req-{{ $req->id }}" data-search="{{ strtolower($req->user->name . ' ' . $req->user->email . ' ' . $req->board->name) }}">
                            <div class="avatar" style="background: {{ $color }};">
                                {{ strtoupper(substr($req->user->name, 0, 1)) }}
                            </div>
                            <div class="request-info">
                                <div class="request-name">{{ $req->user->name }}</div>
                                <div class="request-email">{{ $req->user->email }}</div>
                                <div class="request-meta">
                                    <span class="meta-tag board">{{ $req->board->name }}</span>
                                    <span class="meta-tag workspace">{{ $req->board->workspace->name ?? '—' }}</span>
                                    <span class="meta-tag time">{{ $req->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="request-actions">
                                <button class="btn-approve" onclick="handleRequest({{ $req->id }}, {{ $req->board_id }}, 'approve')">Approve</button>
                                <button class="btn-reject" onclick="handleRequest({{ $req->id }}, {{ $req->board_id }}, 'reject')">Reject</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function handleRequest(requestId, boardId, action) {
            const card = document.getElementById('req-' + requestId);
            const btns = card.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);

            fetch(`/boards/${boardId}/join-requests/${requestId}/${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        card.remove();
                        updateCount();
                    }, 300);
                    showToast(action === 'approve' ? 'User approved!' : 'Request rejected', action === 'approve' ? 'success' : 'success');
                } else {
                    btns.forEach(b => b.disabled = false);
                    showToast(data.message || 'Something went wrong', 'error');
                }
            })
            .catch(() => {
                btns.forEach(b => b.disabled = false);
                showToast('Network error', 'error');
            });
        }

        function updateCount() {
            const remaining = document.querySelectorAll('.request-card').length;
            const badge = document.querySelector('.badge');
            if (remaining === 0) {
                document.getElementById('requestsList').innerHTML = `
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <p>No pending approval requests</p>
                    </div>`;
                if (badge) badge.remove();
            } else if (badge) {
                badge.textContent = remaining;
            }
        }

        function filterRequests(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.request-card').forEach(card => {
                const match = !q || card.dataset.search.includes(q);
                card.style.display = match ? '' : 'none';
            });
        }

        function showToast(msg, type) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + type + ' show';
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        // Auto-refresh every 10 seconds
        setInterval(() => location.reload(), 10000);
    </script>
</body>
</html>
