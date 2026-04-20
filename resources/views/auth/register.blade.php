@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="name">
            Full name <span class="required">*</span>
        </label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            value="{{ old('name') }}" 
            required 
            autofocus
            placeholder="Enter your full name"
        >
    </div>

    <div class="form-group">
        <label for="email">
            User ID (Email) <span class="required">*</span>
        </label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            value="{{ old('email') }}" 
            required
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
            placeholder="Create a password (min. 6 characters)"
        >
    </div>

    <div class="form-group">
        <label for="password_confirmation">
            Confirm password <span class="required">*</span>
        </label>
        <input 
            type="password" 
            id="password_confirmation" 
            name="password_confirmation" 
            required
            placeholder="Confirm your password"
        >
    </div>

    <button type="submit" class="btn-primary">
        Continue
    </button>
</form>

@endsection

@section('footer')
<p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
