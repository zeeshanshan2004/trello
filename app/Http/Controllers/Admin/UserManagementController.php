<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        if (!Auth::user()->isSystemAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update the specified user's status.
     */
    public function toggleStatus(User $user)
    {
        if (!Auth::user()->isSystemAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->isSystemAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return back()->with('success', "User {$user->name} has been deleted.");
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isSystemAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:user,admin',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active') ? $request->is_active : false,
        ]);

        // Create default workspace
        $workspace = \App\Models\Workspace::create([
            'name' => "{$user->name}'s Workspace",
            'description' => "Personal workspace for {$user->name}",
            'color' => 'blue',
            'icon' => strtoupper(substr($user->name, 0, 1)),
        ]);
        $workspace->addMember($user->id, 'owner');

        return back()->with('success', "User {$request->name} has been created successfully with a personal workspace.");
    }

    /**
     * Change a user's password (admin action).
     */
    public function changePassword(Request $request, User $user)
    {
        if (!Auth::user()->isSystemAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['password' => 'required|string|min:6']);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return response()->json(['success' => true]);
    }
}
