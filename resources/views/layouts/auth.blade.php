<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trello') - {{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background elements */
        .bg-decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }

        .bg-decoration::before,
        .bg-decoration::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            opacity: 0.05;
        }

        .bg-decoration::before {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -200px;
            left: -200px;
        }

        .bg-decoration::after {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -200px;
            right: -200px;
        }

        .auth-container {
            background: white;
            border-radius: 0;
            box-shadow: none;
            width: 100%;
            max-width: 420px;
            padding: 48px 40px;
            position: relative;
            z-index: 1;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .trello-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .trello-logo {
            width: 48px;
            height: 48px;
            background: #0052cc;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .trello-logo::before,
        .trello-logo::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 8px;
            background: white;
            border-radius: 2px;
        }

        .trello-logo::before {
            top: 12px;
            left: 14px;
        }

        .trello-logo::after {
            bottom: 12px;
            left: 14px;
        }

        .trello-text {
            font-size: 32px;
            font-weight: 700;
            color: #172b4d;
            letter-spacing: -0.5px;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 600;
            color: #172b4d;
            margin-bottom: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #172b4d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        label .required {
            color: #de350b;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: 2px solid #dfe1e6;
            border-radius: 4px;
            transition: all 0.2s;
            background: #fafbfc;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #0052cc;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1);
        }

        .remember-me-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0052cc;
        }

        .remember-me label {
            margin: 0;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            cursor: pointer;
            font-size: 14px;
            color: #172b4d;
        }

        .info-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #42526e;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            cursor: help;
        }

        .btn-primary {
            width: 100%;
            padding: 12px 24px;
            background: #0052cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary:hover {
            background: #0065ff;
            box-shadow: 0 2px 4px rgba(0, 82, 204, 0.2);
        }

        .btn-primary:active {
            background: #0040a3;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: #6b778c;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dfe1e6;
        }

        .divider::before {
            margin-right: 16px;
        }

        .divider::after {
            margin-left: 16px;
        }

        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .social-buttons-group {
            margin-bottom: 16px;
        }

        .social-buttons-group-title {
            font-size: 14px;
            font-weight: 600;
            color: #172b4d;
            margin-bottom: 12px;
        }

        .btn-social {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #dfe1e6;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
            color: #172b4d;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: inherit;
        }

        .btn-social:hover {
            border-color: #0052cc;
            background: #f4f5f7;
        }

        .btn-social svg {
            width: 20px;
            height: 20px;
        }

        .btn-passkey {
            background: #172b4d;
            color: white;
            border-color: #172b4d;
        }

        .btn-passkey:hover {
            background: #253858;
            border-color: #253858;
        }

        .auth-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #dfe1e6;
        }

        .auth-footer p {
            font-size: 14px;
            color: #6b778c;
        }

        .auth-footer a {
            color: #0052cc;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }

        .error-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 4px;
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 32px 24px;
            }

            .trello-text {
                font-size: 28px;
            }
        }

        .logout-link-auth {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .logout-link-auth a {
            color: #6b778c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .logout-link-auth a:hover {
            color: #172b4d;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="bg-decoration"></div>
    @auth
        <div class="logout-link-auth">
            <form id="logout-form-auth" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-auth').submit();">
                Log out
            </a>
        </div>
    @endauth
    <div class="auth-container">
        <div class="auth-header">
            <div class="trello-logo-wrapper">
                <div class="trello-logo"></div>
                <span class="trello-text">Trello</span>
            </div>
            <h1 class="auth-title">@yield('title', 'Welcome')</h1>
        </div>

        @if (session('success'))
            <div
                style="background: #e3fcef; color: #006644; padding: 12px; border-radius: 4px; font-size: 14px; margin-bottom: 20px; border: 1px solid #abf5d1;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-message">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

        <div class="auth-footer">
            @yield('footer')
        </div>
    </div>
</body>

</html>