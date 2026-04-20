@extends('layouts.auth')

@section('title', 'Log in to continue')

@section('content')

<style>
.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    width: 100%;
    margin-top: 12px;

    border: 1px solid #dfe1e6;
    border-radius: 6px;
    padding: 10px;

    font-size: 14px;
    font-weight: 500;
    color: #172b4d;

    text-decoration: none;
    background: #fff;
    transition: 0.2s;
}

.google-btn i {
    color: #db4437;
    font-size: 16px;
}

.google-btn:hover {
    background: #f4f5f7;
}
</style>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label for="email">
            Email <span class="required">*</span>
        </label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email') }}" 
            required 
            autofocus
            placeholder="Enter your email"
        >
    </div>

    <div class="form-group">
        <label for="password">
            Password <span class="required">*</span>
        </label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            required
            placeholder="Enter your password"
        >
    </div>

    <div class="remember-me-wrapper">
        <div class="remember-me">
            <input 
                type="checkbox" 
                id="remember" 
                name="remember"
            >
            <label for="remember">Remember me</label>
        </div>
        <div class="info-icon" title="This will keep you logged in for 30 days">i</div>
    </div>

    <button type="submit" class="btn-primary">
        Continue
    </button>
</form>

<!-- Divider -->
<div style="text-align:center; margin:15px 0; color:#6b778c;">
    or
</div>

<!-- Google Login -->
<a href="{{ route('google.login') }}" class="google-btn">
    <i class="fab fa-google"></i>
    Continue with Google
</a>

@endsection

@section('footer')
<p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
@endsection