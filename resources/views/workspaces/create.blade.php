<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Workspace</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #1d2125; color: #b6c2cf; min-height: 100vh; display: flex; flex-direction: column; }
        .header { background: #22272b; padding: 14px 32px; border-bottom: 1px solid #38414a; display: flex; align-items: center; gap: 16px; }
        .logo { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: white; text-decoration: none; }
        .logo-icon { width: 32px; height: 32px; background: #0052cc; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .page-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 16px; }
        .card { background: #22272b; border-radius: 12px; padding: 36px; width: 100%; max-width: 480px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        h1 { font-size: 22px; font-weight: 700; color: white; margin-bottom: 6px; }
        .subtitle { font-size: 13px; color: #9fadbc; margin-bottom: 28px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #9fadbc; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        input[type="text"], textarea, select {
            width: 100%; padding: 10px 12px; background: #1d2125; border: 1px solid #38414a;
            border-radius: 6px; color: #b6c2cf; font-size: 14px; font-family: inherit;
            transition: border-color 0.2s; outline: none;
        }
        input[type="text"]:focus, textarea:focus, select:focus { border-color: #0052cc; }
        textarea { resize: vertical; min-height: 80px; }
        select option { background: #1d2125; }
        input[type="file"] {
            width: 100%; padding: 10px 12px; background: #1d2125; border: 1px dashed #38414a;
            border-radius: 6px; color: #9fadbc; font-size: 13px; cursor: pointer;
        }
        .hint { font-size: 12px; color: #6b778c; margin-top: 5px; }
        .actions { display: flex; gap: 10px; margin-top: 28px; }
        .btn-submit { flex: 1; padding: 11px; background: #0052cc; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #0065ff; }
        .btn-cancel { padding: 11px 20px; background: #2c333a; color: #b6c2cf; border: 1px solid #38414a; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; display: flex; align-items: center; }
        .btn-cancel:hover { background: #38414a; color: white; }
        .error-box { background: #2b1c1c; border: 1px solid #c62828; color: #ef5350; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; }
        .required { color: #f87171; }
    </style>
</head>
<body>
    <div class="header">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M4 4h7v9H4zm9 0h7v5h-7zm0 7h7v9h-7zM4 15h7v5H4z"/></svg>
            </div>
            Trello
        </a>
    </div>

    <div class="page-wrap">
        <div class="card">
            <h1>Create Workspace</h1>
            <p class="subtitle">Set up a new workspace for your team</p>

            @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('workspaces.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Workspace Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Marketing Team">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your workspace (optional)">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="color">Theme Color</label>
                    <select id="color" name="color">
                        <option value="blue"   {{ old('color','blue')=='blue'   ? 'selected':'' }}>Blue</option>
                        <option value="green"  {{ old('color')=='green'  ? 'selected':'' }}>Green</option>
                        <option value="red"    {{ old('color')=='red'    ? 'selected':'' }}>Red</option>
                        <option value="purple" {{ old('color')=='purple' ? 'selected':'' }}>Purple</option>
                        <option value="orange" {{ old('color')=='orange' ? 'selected':'' }}>Orange</option>
                        <option value="pink"   {{ old('color')=='pink'   ? 'selected':'' }}>Pink</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Workspace Icon (optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <p class="hint">Upload a custom image for your workspace</p>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-submit">Create Workspace</button>
                    <a href="{{ route('dashboard') }}" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
