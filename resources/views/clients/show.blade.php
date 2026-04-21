<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $client->name }} - Trello</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #1d2125;
            color: #b6c2cf;
            min-height: 100vh;
        }
        .header {
            background: #22272b;
            padding: 12px 24px;
            border-bottom: 1px solid #38414a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left { display: flex; align-items: center; gap: 20px; }
        .client-info { display: flex; align-items: center; gap: 16px; }
        .client-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #38414a;
        }
        .client-initials {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: #0052cc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        .client-name { font-size: 22px; font-weight: 600; color: white; }
        .client-details { font-size: 13px; color: #9fadbc; }
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 15px; font-weight: 600; color: #b6c2cf; margin-bottom: 12px; }
        .boards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .board-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            height: 120px;
        }
        .board-card:hover { transform: translateY(-4px); }
        .board-card-image { width: 100%; height: 100%; object-fit: cover; }
        .board-card-gradient {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0052cc 0%, #0065ff 100%);
        }
        .board-card-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 14px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-secondary { 
            background: #2c333a; 
            color: #b6c2cf; 
            border: 1px solid #454f59; 
            padding: 6px 12px; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: #3c444d;
            color: white;
            border-color: #579dff;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="{{ route('dashboard') }}" class="btn-secondary">← Back</a>
            <div class="client-info">
                <div style="position: relative; width: 70px; height: 70px;">
                    @if($client->image_path)
                        <img src="{{ Storage::url($client->image_path) }}" class="client-image" style="width: 70px; height: 70px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="client-initials" style="display: none; width: 70px; height: 70px; font-size: 28px;">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
                    @else
                        <div class="client-initials" style="width: 70px; height: 70px; font-size: 28px;">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
                    @endif
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="client-name">{{ $client->name }}</div>
                        <span style="background: rgba(0,82,204,0.2); color: #579dff; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase;">Client</span>
                    </div>
                    <div class="client-details">
                        <span style="color: #b6c2cf;">{{ $client->email }}</span> • <span style="color: #b6c2cf;">{{ $client->phone ?? 'No Phone' }}</span>
                        @if($client->father_name) • <span style="color: #b6c2cf;">{{ $client->father_name }}</span>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2 class="section-title">Boards for this Client ({{ count($boards) }})</h2>
            <div class="boards-grid">
                @foreach($boards as $board)
                    <a href="{{ route('boards.show', $board) }}" style="text-decoration: none;">
                        <div class="board-card">
                            @if($board->background_type == 'image' && $board->background_value)
                                <img src="{{ $board->background_value }}" alt="{{ $board->name }}" class="board-card-image">
                            @else
                                <div class="board-card-gradient"></div>
                            @endif
                            <div class="board-card-title">{{ $board->name }}</div>
                        </div>
                    </a>
                @endforeach
                
                @if(count($boards) === 0)
                    <div style="background: #22272b; padding: 40px; border-radius: 8px; text-align: center; grid-column: 1/-1;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📂</div>
                        <p style="color: #9fadbc;">No boards linked to this client yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
