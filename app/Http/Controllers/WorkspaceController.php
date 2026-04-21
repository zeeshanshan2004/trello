<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of workspaces.
     */
    public function index()
    {
        $user = Auth::user();
        $workspaces = $user->workspaces()->withCount('boards')->get();

        return view('workspaces.index', compact('workspaces'));
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create()
    {
        if (!Auth::user()->isSystemAdmin()) {
            abort(403, 'Only administrators can create workspaces.');
        }
        return view('workspaces.create');
    }

    /**
     * Store a newly created workspace in storage.
     */
    public function store(Request $request)
    {
        // Only system admins can create workspaces
        if (!Auth::user()->isSystemAdmin()) {
            abort(403, 'Only administrators can create workspaces.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|in:blue,green,red,purple,orange,pink',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('workspaces', 'public');
        }

        $workspace = Workspace::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? 'blue',
            'icon' => strtoupper(substr($request->name, 0, 1)),
            'image_url' => $imageUrl,
        ]);

        // Add creator as owner
        $workspace->addMember(Auth::id(), 'owner');

        return redirect()->route('dashboard')
            ->with('success', 'Workspace created successfully!');
    }

    /**
     * Display the specified workspace.
     */
    public function show(Workspace $workspace)
    {
        $user = Auth::user();
        $isWorkspaceMember = $user->isSystemAdmin() || $workspace->hasUser($user->id);

        if (!$isWorkspaceMember) {
            // Check if user has at least one shared board in this workspace
            $hasSharedBoard = $workspace->boards()
                ->whereHas('sharedUsers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->exists();

            if (!$hasSharedBoard) {
                abort(403, 'You do not have access to this workspace.');
            }
        }

        if ($isWorkspaceMember) {
            $isOwnerOrAdmin = $user->isSystemAdmin() || in_array(
                $workspace->getUserRole($user->id), ['owner', 'admin']
            );

            if ($isOwnerOrAdmin) {
                // Owner/admin — load all boards
                $workspace->load(['boards' => function ($query) {
                    $query->where('is_archived', false)
                        ->orderBy('is_starred', 'desc')
                        ->orderBy('created_at', 'desc');
                }, 'users']);
            } else {
                // Regular member — only shared boards
                $sharedBoardIds = \Illuminate\Support\Facades\DB::table('board_user')
                    ->where('user_id', $user->id)
                    ->pluck('board_id')
                    ->toArray();

                $workspace->setRelation('boards', $workspace->boards()
                    ->where('is_archived', false)
                    ->whereIn('id', $sharedBoardIds)
                    ->orderBy('is_starred', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get());

                $workspace->load('users');
            }
        }
        else {
            // Board-only access — load only shared boards
            $sharedBoardIds = \Illuminate\Support\Facades\DB::table('board_user')
                ->where('user_id', $user->id)
                ->pluck('board_id')
                ->toArray();

            $workspace->setRelation('boards', $workspace->boards()
                ->where('is_archived', false)
                ->whereIn('id', $sharedBoardIds)
                ->orderBy('is_starred', 'desc')
                ->orderBy('created_at', 'desc')
                ->get());

            $workspace->setRelation('users', collect()); // hide members
        }

        // Get user role
        if ($user->isSystemAdmin()) {
            $userRole = 'owner';
        }
        elseif ($isWorkspaceMember) {
            $userRole = $workspace->getUserRole($user->id);
        }
        else {
            $userRole = 'guest'; // board-only
        }

        // All active non-admin users excluding ALL workspace members (owner/admin/member) — for Grant Board Access dropdown
        $allWorkspaceMemberIds = $workspace->users()->pluck('users.id')->toArray();

        $grantableUsers = \App\Models\User::where('is_active', true)
            ->where('role', '!=', 'admin')
            ->whereNotIn('id', $allWorkspaceMemberIds)
            ->get(['id', 'name', 'email']);

        $clients = \App\Models\Client::all();
        return view('workspaces.show', compact('workspace', 'userRole', 'isWorkspaceMember', 'grantableUsers', 'clients'));
    }

    /**
     * Show the form for editing the specified workspace.
     */
    public function edit(Workspace $workspace)
    {
        $user = Auth::user();

        // Admin bypass or workspace admin/owner can edit
        if (!$user->isSystemAdmin() && !$workspace->isAdmin($user->id)) {
            abort(403, 'You do not have permission to edit this workspace.');
        }

        // Users not already in this workspace (for search dropdown)
        $existingMemberIds = $workspace->users()->pluck('users.id')->toArray();
        $addableUsers = \App\Models\User::where('is_active', true)
            ->where('role', '!=', 'admin')
            ->whereNotIn('id', $existingMemberIds)
            ->get(['id', 'name', 'email']);

        return view('workspaces.edit', compact('workspace', 'addableUsers'));
    }

    /**
     * Update the specified workspace in storage.
     */
    public function update(Request $request, Workspace $workspace)
    {
        $user = Auth::user();
        // Only owner and admin (or system admin) can update
        if (!$user->isSystemAdmin() && !$workspace->isAdmin($user->id)) {
            abort(403, 'You do not have permission to update this workspace.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|in:blue,green,red,purple,orange,pink',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? $workspace->color,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($workspace->image_url) {
                Storage::disk('public')->delete($workspace->image_url);
            }
            $data['image_url'] = $request->file('image')->store('workspaces', 'public');
        }

        $workspace->update($data);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated successfully!');
    }

    /**
     * Remove the specified workspace from storage.
     */
    public function destroy(Request $request, Workspace $workspace)
    {
        $user = Auth::user();
        // Only system admin can delete
        if (!$user->isSystemAdmin()) {
            abort(403, 'Only system admin can delete the workspace.');
        }

        // Verify admin password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return redirect()->back()->withErrors(['delete_password' => 'Incorrect password. Workspace not deleted.']);
        }

        $workspace->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Workspace "' . $workspace->name . '" deleted successfully!');
    }

    /**
     * Add member to workspace
     */
    public function addMember(Request $request, Workspace $workspace)
    {
        $user = Auth::user();
        // Only admin and owner (or system admin) can add members
        if (!$user->isSystemAdmin() && !$workspace->isAdmin($user->id)) {
            abort(403, 'You do not have permission to add members.');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'role' => 'nullable|string|in:member,admin',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if ($workspace->hasUser($user->id)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User is already a member of this workspace.']);
            }
            return redirect()->back()->with('error', 'User is already a member of this workspace.');
        }

        $workspace->addMember($user->id, $request->input('role', 'member'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member added successfully!',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => 'member'],
            ]);
        }

        return redirect()->back()->with('success', 'Member added successfully!');
    }

    /**
     * Remove member from workspace
     */
    public function removeMember(Workspace $workspace, User $user)
    {
        $currentUser = Auth::user();
        // Only admin and owner (or system admin) can remove members
        if (!$currentUser->isSystemAdmin() && !$workspace->isAdmin($currentUser->id)) {
            abort(403, 'You do not have permission to remove members.');
        }

        // Cannot remove owner
        if ($workspace->isOwner($user->id)) {
            return redirect()->back()
                ->with('error', 'Cannot remove workspace owner.');
        }

        // Cannot remove yourself
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'You cannot remove yourself. Please transfer ownership first.');
        }

        $workspace->removeMember($user->id);

        // Also remove board access for all boards in this workspace
        $workspace->boards()->each(function($board) use ($user) {
            $board->sharedUsers()->detach($user->id);
        });

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Member removed successfully.']);
        }

        return redirect()->back()
            ->with('success', 'Member removed successfully!');
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Request $request, Workspace $workspace, User $user)
    {
        $currentUser = Auth::user();
        // Only owner (or system admin) can update roles
        if (!$currentUser->isSystemAdmin() && !$workspace->isOwner($currentUser->id)) {
            abort(403, 'Only workspace owner can update member roles.');
        }

        // Cannot change owner's role
        if ($workspace->isOwner($user->id) && $request->role !== 'owner') {
            return redirect()->back()
                ->with('error', 'Cannot change owner role.');
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:member,admin,owner',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $workspace->updateMemberRole($user->id, $request->role);

        return redirect()->back()
            ->with('success', 'Member role updated successfully!');
    }
}
