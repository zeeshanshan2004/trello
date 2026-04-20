<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            if (!$user->is_active && !$user->isSystemAdmin()) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Your account is pending admin approval.']);
            }
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create default workspace
        $workspace = \App\Models\Workspace::create([
            'name' => "{$user->name}'s Workspace",
            'description' => "Personal workspace for {$user->name}",
            'color' => 'blue',
            'icon' => strtoupper(substr($user->name, 0, 1)),
        ]);
        $workspace->addMember($user->id, 'owner');

        return redirect()->route('login')->with('success', 'Registration successful! Please wait for admin approval before logging in.');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $redirectUri = env('GOOGLE_REDIRECT_URI', url('/auth/google/callback'));
        $scope = 'openid email profile';
        
        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'access_type' => 'offline',
        ]);

        return redirect($authUrl);
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect()->route('login')->withErrors(['email' => 'Google authentication failed.']);
            }

            // Exchange code for token
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')),
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'];

            // Get user info
            $userResponse = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);

            $googleUser = json_decode($userResponse->getBody(), true);
            
            // Find or create user
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'password' => Hash::make(Str::random(24)),
                    'is_active' => true,
                ]);

                // Create workspace
                $workspace = \App\Models\Workspace::create([
                    'name' => "{$user->name}'s Workspace",
                    'description' => "Personal workspace for {$user->name}",
                    'color' => 'blue',
                    'icon' => strtoupper(substr($user->name, 0, 1)),
                ]);
                $workspace->addMember($user->id, 'owner');
            }

            Auth::login($user);
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed: ' . $e->getMessage()]);
        }
    }
}
