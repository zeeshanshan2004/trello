<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\BoardActivity;
use App\Notifications\BoardActivityNotification;

class BoardController extends Controller
{
    /**
     * Show the form for creating a new board.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Admin sees all workspaces, regular users see workspaces they are members of
        if ($user->isSystemAdmin()) {
            $workspaces = Workspace::all();
        } else {
            // Get workspaces where user is a member (any role)
            $workspaces = Workspace::whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->get();
        }

        // Default workspace from query or first workspace
        $defaultWorkspaceId = $request->query('workspace_id') ?? $workspaces->first()?->id;

        return view('boards.create', compact('workspaces', 'defaultWorkspaceId'));
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'workspace_id' => 'required|exists:workspaces,id',
            'background_type' => 'nullable|string|in:gradient,image,color',
            'background_value' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if user has access to the workspace (admin bypass)
        $workspace = Workspace::findOrFail($request->workspace_id);
        if (!Auth::user()->isSystemAdmin() && !$workspace->hasUser(Auth::id())) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check workspace board limit (10 for free)
        $boardCount = $workspace->boards()->where('is_archived', false)->count();
        if ($boardCount >= 10) {
            return redirect()->back()
                ->with('error', 'Workspace has reached the maximum limit of 10 boards.')
                ->withInput();
        }

        $board = Board::create([
            'workspace_id' => $request->workspace_id,
            'name' => $request->name,
            'description' => $request->description,
            'background_type' => $request->background_type ?? 'gradient',
            'background_value' => $request->background_value ?? 'blue',
        ]);

        // Create default labels for the board
        $defaultLabels = [
            ['name' => 'Green', 'color' => '#61bd4f'],
            ['name' => 'Yellow', 'color' => '#f2d600'],
            ['name' => 'Orange', 'color' => '#ff9f1a'],
            ['name' => 'Red', 'color' => '#eb5a46'],
            ['name' => 'Purple', 'color' => '#c377e0'],
            ['name' => 'Blue', 'color' => '#0079bf'],
        ];

        foreach ($defaultLabels as $labelData) {
            $board->labels()->create($labelData);
        }

        // Automatically share board with creator (unless admin)
        if (!Auth::user()->isSystemAdmin()) {
            $board->sharedUsers()->attach(Auth::id());
        }

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Board created successfully!');
    }

    /**
     * Notify all workspace + board members about an activity.
     */
    private function notifyMembers(Board $board, string $message, ?int $cardId = null, ?int $listId = null): void
    {
        $allIds = array_unique(array_merge(
            $board->workspace->users()->pluck('users.id')->toArray(),
            $board->sharedUsers()->pluck('users.id')->toArray()
        ));
        foreach (\App\Models\User::whereIn('id', $allIds)->get() as $user) {
            if ($user->id !== Auth::id()) {
                $user->notify(new BoardActivityNotification($message, $board->id, $board->name, $cardId, $listId));
            }
        }
    }

    /**
     * Notify all workspace and board members about an activity.
     */
    private function notifyAdmins(Board $board, string $message, ?int $cardId = null, ?int $listId = null): void
    {
        try {
            $allIds = array_unique(array_merge(
                $board->workspace->users()->pluck('users.id')->toArray(),
                $board->sharedUsers()->pluck('users.id')->toArray()
            ));

            $users = User::whereIn('id', $allIds)->get();
            foreach ($users as $user) {
                if ($user->id !== Auth::id()) {
                    $user->notify(new BoardActivityNotification($message, $board->id, $board->name, $cardId, $listId));
                }
            }
        } catch (\Exception $e) {
            \Log::error('notifyAdmins failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified board.
     */
    public function show(Board $board)
        {
            $user = Auth::user();

            // Check if user can edit/delete board
            $canEdit = $user->isSystemAdmin() || $board->workspace->isOwner($user->id);

            // Admin or Workspace Owner can access any board in the workspace
            if ($user->isSystemAdmin() || $board->workspace->isOwner($user->id)) {
                // Full access bypass
                $board->load(['lists.cards' => function($q) {
                    $q->with(['members', 'checklistItems', 'attachments', 'comments']);
                }]);
            } else {
                // Check if user is workspace member
                $isWorkspaceMember = $board->workspace->hasUser($user->id);

                if (!$isWorkspaceMember) {
                    $board->sharedUsers()->detach($user->id);
                    return redirect()->route('dashboard')
                        ->with('error', 'You do not have access to this board.');
                }

                // User is workspace member - NOW check if they have board access
                $hasBoardAccess = $board->sharedUsers()->where('user_id', $user->id)->exists();

                if (!$hasBoardAccess) {
                    // Workspace member but no board access - check card level access
                    $board->load(['lists' => function($query) use ($user) {
                        $query->with(['cards' => function($cardQuery) use ($user) {
                            $cardQuery->whereHas('members', function($memberQuery) use ($user) {
                                $memberQuery->where('user_id', $user->id);
                            });
                        }]);
                    }]);

                    // If user has no card access at all, deny access
                    $hasAnyCardAccess = false;
                    foreach ($board->lists as $list) {
                        if ($list->cards->count() > 0) {
                            $hasAnyCardAccess = true;
                            break;
                        }
                    }

                    if (!$hasAnyCardAccess) {
                        return redirect()->route('dashboard')
                            ->with('error', 'You do not have access to this board.');
                    }
                } else {
                    // Has board access - show all cards
                    $board->load(['lists.cards' => function($q) {
                        $q->with(['members', 'checklistItems', 'attachments', 'comments']);
                    }]);
                }
            }

            // Update last viewed
            $board->update(['last_viewed_at' => now()]);

            // Log board visit
            BoardActivity::create([
                'board_id' => $board->id,
                'user_id'  => $user->id,
                'type'     => 'board_visited',
                'data'     => [],
            ]);

            // All accessible boards for search
            $sharedBoardIds = \Illuminate\Support\Facades\DB::table('board_user')->where('user_id', $user->id)->pluck('board_id')->toArray();
            $ownerWorkspaceIds = $user->workspaces()->wherePivotIn('role', ['owner','admin'])->pluck('workspaces.id')->toArray();
            $boards = \App\Models\Board::where('is_archived', false)
                ->where(function($q) use ($sharedBoardIds, $ownerWorkspaceIds, $user) {
                    if ($user->isSystemAdmin()) { $q->whereNotNull('id'); return; }
                    $q->whereIn('id', $sharedBoardIds)->orWhereIn('workspace_id', $ownerWorkspaceIds);
                })
                ->with('workspace')
                ->get()
                ->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'workspace' => $b->workspace->name ?? '', 'list_id' => null])
                ->values()
                ->toArray();

            return view('boards.show', compact('board', 'canEdit', 'boards'));
        }

    /**
     * Store a new label for the board.
     */
    public function storeLabel(Request $request, Board $board)
    {
        // Check if user has access to board's workspace (admin bypass)
        if (!Auth::user()->isSystemAdmin() && !$board->workspace->hasUser(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $label = $board->labels()->create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'label' => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ]
        ]);
    }

    /**
     * Update an existing label.
     */
    public function updateLabel(Request $request, Board $board, $labelId)
    {
        // Check if user has access to board's workspace
        if (!$board->workspace->hasUser(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $label = $board->labels()->findOrFail($labelId);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $label->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'label' => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ]
        ]);
    }

    /**
     * Delete a label.
     */
    public function deleteLabel(Board $board, $labelId)
    {
        // Check if user has access to board's workspace
        if (!$board->workspace->hasUser(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $label = $board->labels()->findOrFail($labelId);
        $label->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get board activity log.
     */
    public function getActivities(Board $board)
    {
        $user = Auth::user();
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id) && !$board->sharedUsers()->where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false], 403);
        }

        $activities = $board->activities()->with('user')->orderBy('created_at', 'desc')->take(50)->get();

        return response()->json([
            'activities' => $activities->map(fn($a) => [
                'id'        => $a->id,
                'type'      => $a->type,
                'message'   => $a->getMessage(),
                'user_name' => $a->user->name,
                'initials'  => strtoupper(substr($a->user->name, 0, 2)),
                'diff'      => $a->created_at->diffForHumans(),
                'created_at'=> $a->created_at->format('j M Y, H:i'),
            ])
        ]);
    }

    /**
     * Get archived cards for the board.
     */
    public function getArchivedCards(Board $board)
    {
        $user = Auth::user();
        
        // Check access
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id) && !$board->workspace->hasUser($user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $archivedCards = Card::whereHas('list', function($query) use ($board) {
            $query->where('board_id', $board->id);
        })
        ->where('is_archived', true)
        ->with(['list'])
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function($card) {
            return [
                'id' => $card->id,
                'title' => $card->title,
                'list_name' => $card->list->name,
                'list_id' => $card->list->id,
                'archived_at' => $card->updated_at->diffForHumans(),
            ];
        });

        return response()->json(['success' => true, 'cards' => $archivedCards]);
    }

    /**
     * Share board with a user.
     */
    public function shareBoard(Request $request, Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can share boards
        if (!$user->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
                return response()->json(['error' => 'Only workspace owner or admin can share boards'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Check if user is active
        $targetUser = User::find($request->user_id);
        if (!$targetUser->is_active) {
            return response()->json(['error' => 'User is not active'], 422);
        }

        $workspace = $board->workspace;

        // First, add user to workspace if not already a member
        if (!$workspace->hasUser($targetUser->id)) {
            $workspace->addMember($targetUser->id, 'member');
        }

        // Then, share the board
        if (!$board->sharedUsers()->where('user_id', $request->user_id)->exists()) {
            $board->sharedUsers()->attach($request->user_id);
        }

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'member_added',
            'data'     => ['member_name' => $targetUser->name],
        ]);

        $actor = Auth::user()->name;
        $this->notifyMembers($board, "{$actor} added {$targetUser->name} to the board");

        return response()->json([
            'success' => true,
            'message' => 'Board shared successfully',
            'user' => $targetUser
        ]);
    }

    /**
     * Unshare board from a user.
     */
    public function unshareBoard(Board $board, User $user)
    {
        $currentUser = Auth::user();
      
        // Only workspace owner or admin can unshare boards
        if (!$currentUser->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $currentUser->id)->first()?->pivot;
            if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
                return response()->json(['error' => 'Only workspace owner or admin can unshare boards'], 403);
            }
        }

        $workspace = $board->workspace;
        
        // Remove from board
        $board->sharedUsers()->detach($user->id);

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'member_removed',
            'data'     => ['member_name' => $user->name],
        ]);

        $actor = Auth::user()->name;
        $this->notifyMembers($board, "{$actor} removed {$user->name} from the board");
        
        // Check if user has access to any other board in this workspace
        $hasOtherBoardAccess = $workspace->boards()
            ->whereHas('sharedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->exists();
        
        // If no other board access, remove from workspace
        if (!$hasOtherBoardAccess) {
            $workspace->removeMember($user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Board access removed'
        ]);
    }
    /**
     * Get list of users who ALREADY HAVE access to this board.
     * Excludes Admin, Owner, and the logged-in user.
     */
    public function getSharedUsers(Board $board)
    {
        $user = Auth::user();

        // Workspace owner ID
        $workspaceOwnerId = $board->workspace->users()
            ->wherePivot('role', 'owner')
            ->first()?->id;

        $sharedUsers = $board->sharedUsers()
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'admin')
            ->where('users.id', '!=', $workspaceOwnerId)
            ->get(['users.id', 'users.name', 'users.email']);

        return response()->json([
            'success' => true,
            'users' => $sharedUsers,
            'activeUsers' => $this->getActiveUsersList($board)
        ]);
    }

    /**
     * Get list of active workspace users who can be ADDED to the board.
     */
    public function getActiveUsers(Board $board)
    {
        return response()->json([
            'success' => true,
            'users' => $this->getActiveUsersList($board)
        ]);
    }

    /**
     * Internal helper to get active users available for sharing.
     * Returns all active non-admin users who don't already have board access,
     * excluding the workspace owner.
     */
    private function getActiveUsersList(Board $board)
    {
        $workspaceOwnerId = $board->workspace->users()
            ->wherePivot('role', 'owner')
            ->first()?->id;

        $sharedUserIds = $board->sharedUsers()->pluck('users.id')->toArray();

        // Exclude workspace admins/owners from the list too
        $workspaceAdminIds = $board->workspace->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->pluck('users.id')
            ->toArray();

        return User::where('is_active', true)
            ->where('role', '!=', 'admin') // exclude system admins
            ->whereNotIn('id', $workspaceAdminIds) // exclude workspace owner/admin
            ->whereNotIn('id', $sharedUserIds) // exclude already shared
            ->get(['id', 'name', 'email']);
    }

    /**
     * Update a board.
     */
    public function update(Request $request, Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can update boards
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id)) {
            return response()->json(['error' => 'Only workspace owner can update boards'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'background_type' => 'nullable|string|in:gradient,image,color',
            'background_value' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $board->update([
            'name' => $request->name,
            'description' => $request->description,
            'background_type' => $request->background_type ?? $board->background_type,
            'background_value' => $request->background_value ?? $board->background_value,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Board updated successfully',
            'board' => $board
        ]);
    }

    /**
     * Archive a board.
     */
    public function archive(Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can archive boards
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id)) {
            return response()->json(['error' => 'Only workspace owner can archive boards'], 403);
        }

        $board->update(['is_archived' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Board archived successfully'
        ]);
    }

    /**
     * Restore an archived board.
     */
    public function restore(Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can restore boards
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id)) {
            return response()->json(['error' => 'Only workspace owner can restore boards'], 403);
        }

        $board->update(['is_archived' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Board restored successfully'
        ]);
    }

    /**
     * Delete a board permanently.
     */
    public function destroy(Board $board)
    {

        $user = Auth::user();
        
        // Only workspace owner or admin can delete boards
        if (!$user->isSystemAdmin() && !$board->workspace->isOwner($user->id)) {
            return response()->json(['error' => 'Only workspace owner can delete boards'], 403);
        }

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Board deleted successfully'
        ]);
    }

    /**
     * Get all archived cards for the authenticated user (Admin/Owner only).
     */
    public function getAllArchivedCards()
    {
        $user = Auth::user();
        
        // Only Admin or Workspace Owners can see archived cards
        if (!$user->isSystemAdmin()) {
            // Get workspaces where user is owner
            $ownedWorkspaceIds = $user->workspaces()
                ->wherePivot('role', 'owner')
                ->pluck('workspaces.id');
            
            if ($ownedWorkspaceIds->isEmpty()) {
                return response()->json(['success' => true, 'cards' => []]);
            }
            
            // Get archived cards from boards in owned workspaces
            $archivedCards = Card::whereHas('list.board', function($query) use ($ownedWorkspaceIds) {
                $query->whereIn('workspace_id', $ownedWorkspaceIds);
            })
            ->where('is_archived', true)
            ->with(['list.board.workspace'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($card) {
                return [
                    'id' => $card->id,
                    'title' => $card->title,
                    'list_name' => $card->list->name,
                    'list_id' => $card->list->id,
                    'board_name' => $card->list->board->name,
                    'board_id' => $card->list->board->id,
                    'workspace_name' => $card->list->board->workspace->name,
                    'archived_at' => $card->updated_at->diffForHumans(),
                ];
            });
        } else {
            // Admin sees all archived cards
            $archivedCards = Card::where('is_archived', true)
                ->with(['list.board.workspace'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($card) {
                    return [
                        'id' => $card->id,
                        'title' => $card->title,
                        'list_name' => $card->list->name,
                        'list_id' => $card->list->id,
                        'board_name' => $card->list->board->name,
                        'board_id' => $card->list->board->id,
                        'workspace_name' => $card->list->board->workspace->name,
                        'archived_at' => $card->updated_at->diffForHumans(),
                    ];
                });
        }

        return response()->json(['success' => true, 'cards' => $archivedCards]);
    }

    /**
     * Create a shareable link for the board
     */
    public function createShareLink(Request $request, Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can create share links
        if (!$user->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
                return response()->json(['error' => 'Only workspace owner or admin can create share links'], 403);
            }
        }

        // Check if link already exists
        $existingLink = $board->shareLinks()->where('status', 'active')->first();
        if ($existingLink) {
            return response()->json([
                'success' => true,
                'link' => $existingLink,
                'share_url' => url('/share/' . $existingLink->token)
            ]);
        }

        // Create new share link
        $shareLink = $board->shareLinks()->create([
            'created_by' => $user->id,
            'token' => \App\Models\BoardShareLink::generateToken(),
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'link' => $shareLink,
            'share_url' => url('/share/' . $shareLink->token)
        ]);
    }

    /**
     * Get existing share link for a board
     */
    public function getShareLink(Board $board)
    {
        $user = Auth::user();
        
        // Check if user has access to the board
        if (!$user->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // Get existing active share link
        $shareLink = $board->shareLinks()->where('status', 'active')->first();
        
        if ($shareLink) {
            return response()->json([
                'success' => true,
                'share_url' => url('/share/' . $shareLink->token)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No active share link'
        ]);
    }

    /**
     * Delete a share link by board
     */
    public function deleteShareLinkByBoard(Board $board)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can delete share links
        if (!$user->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
                return response()->json(['error' => 'Only workspace owner or admin can delete share links'], 403);
            }
        }

        $shareLink = $board->shareLinks()->where('status', 'active')->first();
        
        if ($shareLink) {
            $shareLink->update(['status' => 'inactive']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Share link deleted'
        ]);
    }

    /**
     * Delete a share link
     */
    public function deleteShareLink(Board $board, \App\Models\BoardShareLink $shareLink)
    {
        $user = Auth::user();
        
        // Only workspace owner or admin can delete share links
        if (!$user->isSystemAdmin()) {
            $pivot = $board->workspace->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !in_array($pivot->role, ['owner', 'admin'])) {
                return response()->json(['error' => 'Only workspace owner or admin can delete share links'], 403);
            }
        }

        $shareLink->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Share link deleted'
        ]);
    }

    /**
     * Join board via share link
     */
    public function joinViaShareLink($token)
    {
        $shareLink = \App\Models\BoardShareLink::where('token', $token)
            ->where('status', 'active')
            ->first();

        if (!$shareLink) {
            return redirect()->route('login')->withErrors(['error' => 'Invalid or expired share link']);
        }

        $user = Auth::user();
        
        if (!$user) {
            // Store the token in session and redirect to login
            session(['share_link_token' => $token]);
            return redirect()->route('login');
        }

        $board = $shareLink->board;
        $workspace = $board->workspace;

        // Check if user is already a member
        if (!$workspace->hasUser($user->id)) {
            // Add user to workspace as member
            $workspace->addMember($user->id, 'member');
        }

        // Check if user already has access to board
        if ($board->sharedUsers()->where('user_id', $user->id)->exists()) {
            return redirect()->route('boards.show', $board)->with('success', 'You already have access to this board!');
        }

        // Check if join request already exists
        $existingRequest = \App\Models\BoardJoinRequest::where('board_id', $board->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'pending') {
                // Show waiting page
                return view('boards.waiting-approval', [
                    'board' => $board,
                    'boardName' => $board->name,
                    'requestId' => $existingRequest->id
                ]);
            } elseif ($existingRequest->status === 'approved') {
                // Grant access
                $board->sharedUsers()->attach($user->id);
                $existingRequest->delete();
                return redirect()->route('boards.show', $board)->with('success', 'You have been approved and added to the board!');
            }
        }

        // Create join request
        $request = \App\Models\BoardJoinRequest::create([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Notify admins about new join request
        $this->notifyAdmins($board, "{$user->name} requested to join the board via share link");

        // Show waiting page
        return view('boards.waiting-approval', [
            'board' => $board,
            'boardName' => $board->name,
            'requestId' => $request->id
        ]);
    }

    /**
     * Check if join request is approved
     */
    public function checkJoinRequestStatus(Board $board, $requestId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // First check if user already has board access (request was approved & deleted)
        if ($board->sharedUsers()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'approved' => true,
                'redirect_url' => route('boards.show', $board)
            ]);
        }

        // Check if request still exists (still pending)
        $request = \App\Models\BoardJoinRequest::where('id', $requestId)
            ->where('board_id', $board->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$request) {
            // Request deleted but no board access = rejected
            return response()->json(['approved' => false, 'rejected' => true]);
        }

        return response()->json(['approved' => false, 'rejected' => false]);
    }

    /**
     * Approve a join request
     */
    public function approveJoinRequest(Board $board, \App\Models\BoardJoinRequest $request)
    {
        $user = Auth::user();
        $isWorkspaceAdmin = $board->workspace->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->isSystemAdmin() && !$isWorkspaceAdmin) {
            abort(403, 'Unauthorized');
        }

        if ($request->board_id !== $board->id) {
            abort(404);
        }

        // Add user to board
        $board->sharedUsers()->attach($request->user_id);

        // Create activity log
        BoardActivity::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'type' => 'member_approved',
            'data' => ['member_name' => $request->user->name],
        ]);

        // Delete the request (move from pending to approved/members)
        $request->delete();

        // Notify the user
        $request->user->notify(new \App\Notifications\BoardActivityNotification(
            "Your request to join {$board->name} has been approved!",
            $board->id,
            $board->name
        ));

        return response()->json(['success' => true, 'message' => 'User approved and added to board']);
    }

    /**
     * Reject a join request
     */
    public function rejectJoinRequest(Board $board, \App\Models\BoardJoinRequest $request)
    {
        $user = Auth::user();
        $isWorkspaceAdmin = $board->workspace->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->isSystemAdmin() && !$isWorkspaceAdmin) {
            abort(403, 'Unauthorized');
        }

        if ($request->board_id !== $board->id) {
            abort(404);
        }

        // Notify the user
        $request->user->notify(new \App\Notifications\BoardActivityNotification(
            "Your request to join {$board->name} has been rejected.",
            $board->id,
            $board->name
        ));

        // Delete request
        $request->delete();

        return response()->json(['success' => true, 'message' => 'User rejected']);
    }

    public function allPendingRequests()
    {
        $user = Auth::user();
        if (!$user->isSystemAdmin()) {
            // Workspace admins see only their boards' requests
            $adminWorkspaceIds = $user->workspaces()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->pluck('workspaces.id');

            $boardIds = Board::whereIn('workspace_id', $adminWorkspaceIds)->pluck('id');

            $requests = \App\Models\BoardJoinRequest::whereIn('board_id', $boardIds)
                ->where('status', 'pending')
                ->with(['user', 'board.workspace'])
                ->latest()
                ->get();
        } else {
            $requests = \App\Models\BoardJoinRequest::where('status', 'pending')
                ->with(['user', 'board.workspace'])
                ->latest()
                ->get();
        }

        return view('admin.pending-approvals', compact('requests'));
    }

    /**
     * Get pending join requests for a board
     */
    public function getPendingRequests(Board $board)
    {
        $user = Auth::user();
        $isWorkspaceAdmin = $board->workspace->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();

        if (!$user->isSystemAdmin() && !$isWorkspaceAdmin) {
            abort(403, 'Unauthorized');
        }

        $requests = $board->joinRequests()
            ->where('status', 'pending')
            ->with('user')
            ->get();

        return response()->json(['requests' => $requests]);
    }
}
