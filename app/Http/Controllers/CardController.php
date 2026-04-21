<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\ListModel;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\BoardActivityNotification;
use App\Notifications\MentionNotification;
use Illuminate\Support\Facades\Storage;
use App\Events\CardUpdated;
use App\Events\CardLabelsUpdated;
use App\Events\CardCoverUpdated;
use App\Events\CardMoved;
use App\Events\CardArchived;
use App\Events\CardMemberAdded;
use App\Events\CardMemberRemoved;
use App\Events\CommentPosted;
use App\Events\CommentDeleted;
use App\Events\ChecklistItemCreated;
use App\Events\ChecklistItemUpdated;
use App\Events\ChecklistItemDeleted;
use App\Events\ChecklistCleared;
use App\Models\CardActivity;
use App\Models\BoardActivity;

class CardController extends Controller
{
    /**
     * Notify all workspace members about a board activity.
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
     * Store a newly created card in storage.
     */
    public function store(Request $request, Board $board, ListModel $list)
    {
        $user = Auth::user();
        
        // Admin or Workspace Owner or Workspace Member check
        $isOwner = $board->workspace->isOwner($user->id);
        $isMember = $board->workspace->hasUser($user->id);

        if (!$user->isSystemAdmin() && !$isOwner && !$isMember) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Get the highest position in this list
        $maxPosition = $list->cards()->max('position') ?? -1;

        $card = Card::create([
            'list_id' => $list->id,
            'title' => $request->title,
            'client_id' => $request->client_id,
            'position' => $maxPosition + 1,
        ]);

        // Log activity
        CardActivity::create([
            'card_id' => $card->id,
            'user_id' => Auth::id(),
            'type'    => 'created',
            'data'    => ['list_name' => $list->name],
        ]);

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'card_created',
            'data'     => ['card_title' => $card->title, 'list_name' => $list->name],
        ]);

        $actor = Auth::user()->name;
        $this->notifyAdmins($board, "{$actor} added card \"{$card->title}\" to {$list->name}", $card->id, $list->id);

        return response()->json([
            'success' => true,
            'card' => $card
        ]);
    }

    /**
     * Display the specific card.
     */
     
    public function show(Board $board, ListModel $list, Card $card)
    {
        // return 1;
     // Verify card belongs to the list
            if ($card->list_id !== $list->id) {
                abort(404, 'Card not found in this list');
            }

            // Verify list belongs to the board
            if ($list->board_id !== $board->id) {
                abort(404, 'List not found in this board');
            }

            // Load relationships
            $card->load(['checklistItems', 'members', 'attachments.user', 'comments.user']);
            $board->load(['labels', 'lists']);

            // Get workspace members with pivot data (excluding admin)
            $workspaceOwnerId = $board->workspace->users()->wherePivot('role', 'owner')->first()?->id;
            $workspaceMembers = $board->workspace->users()
                ->withPivot('role', 'can_comment')
                ->where('users.role', '!=', 'admin')
                ->get();

            // Board shared users excluding admin
            $boardMembers = $board->sharedUsers()
                ->where('users.is_active', true)
                ->where('users.role', '!=', 'admin')
                ->get();

            // Workspace Members / System Users (Admin list logic)
            // Fetches all active users in the workspace NOT already on the board
            $workspaceMembersNotOnBoard = $board->workspace->users()
                ->where('users.is_active', true)
                ->where('users.role', '!=', 'admin')
                ->whereNotIn('users.id', $boardMembers->pluck('id'))
                ->get();

            // Standard active users list (all users in workspace)
            $allActiveUsers = $board->workspace->users()
                ->where('users.is_active', true)
                ->where('users.role', '!=', 'admin')
                ->get();

            // Check current user permissions
            $currentUser = auth()->user();
            $isAdmin = $currentUser->isSystemAdmin();
            $isOwner = $board->workspace->isOwner($currentUser->id);
            $canDelete = $isAdmin || $isOwner;

            return view('cards.show', [
                'board' => $board,
                'list' => $list,
                'card' => $card,
                'workspaceMembers' => $workspaceMembers, 
                'boardMembers' => $boardMembers, 
                'workspaceMembersNotOnBoard' => $workspaceMembersNotOnBoard,
                'allActiveUsers' => $allActiveUsers,
                'boardLabels' => $board->labels,
                'canDelete' => $canDelete,
                'clients' => \App\Models\Client::all(),
            ]);
        }

    public function showByCardId(Board $board, Card $card)
    {
        // Verify card belongs to the board (through its list)
        $list = $card->list;
        if (!$list || $list->board_id !== $board->id) {
            abort(404, 'Card not found in this board');
        }

        // Load relationships
        $card->load(['checklistItems', 'members', 'attachments.user', 'comments.user']);
        $board->load(['labels', 'lists']);

        // Get workspace members with pivot data (excluding admin)
        $workspaceOwnerId = $board->workspace->users()->wherePivot('role', 'owner')->first()?->id;
        $workspaceMembers = $board->workspace->users()
            ->withPivot('role', 'can_comment')
            ->where('users.role', '!=', 'admin')
            ->get();

        // Board shared users excluding admin
        $boardMembers = $board->sharedUsers()
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'admin')
            ->get();

        // Workspace Members / System Users (Admin list logic)
        $workspaceMembersNotOnBoard = $board->workspace->users()
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'admin')
            ->whereNotIn('users.id', $boardMembers->pluck('id'))
            ->get();

        // Standard active users list (all users in workspace)
        $allActiveUsers = $board->workspace->users()
            ->where('users.is_active', true)
            ->where('users.role', '!=', 'admin')
            ->get();

        // Check current user permissions
        $currentUser = auth()->user();
        $isAdmin = $currentUser->isSystemAdmin();
        $isOwner = $board->workspace->isOwner($currentUser->id);
        $canDelete = $isAdmin || $isOwner;

        return view('cards.show', [
            'board' => $board,
            'list' => $list,
            'card' => $card,
            'workspaceMembers' => $workspaceMembers, 
            'boardMembers' => $boardMembers, 
            'workspaceMembersNotOnBoard' => $workspaceMembersNotOnBoard,
            'allActiveUsers' => $allActiveUsers,
            'boardLabels' => $board->labels,
            'canDelete' => $canDelete,
            'clients' => \App\Models\Client::all(),
        ]);
    }

    /**
     * Update card details.
     */
    public function update(Request $request, Board $board, ListModel $list, Card $card)
    {
        $data = $request->only(['title', 'description', 'due_date', 'start_date', 'cover', 'labels']);
        
        // Track changes for activity log
        $oldTitle       = $card->title;
        $oldDescription = $card->description;
        $oldDueDate     = $card->due_date;
        $oldCover       = $card->cover;

        if (isset($data['due_date']) && empty($data['due_date'])) $data['due_date'] = null;
        if (isset($data['start_date']) && empty($data['start_date'])) $data['start_date'] = null;
        
        $card->update($data);
        $card->refresh();

        $userId = Auth::id();

        if (array_key_exists('description', $data) && $data['description'] !== $oldDescription) {
            CardActivity::create(['card_id' => $card->id, 'user_id' => $userId, 'type' => 'description_changed', 'data' => []]);
            BoardActivity::create(['board_id' => $board->id, 'user_id' => $userId, 'type' => 'card_desc_updated', 'data' => ['card_title' => $card->title]]);
            // Description preview — strip HTML, take first 100 chars
            $preview = mb_substr(strip_tags($data['description'] ?? ''), 0, 100);
            $msg = Auth::user()->name . " updated description of \"{$card->title}\"" . ($preview ? ": \"{$preview}\"" : '');
            $this->notifyAdmins($board, $msg, $card->id, $list->id);
        }
        if (array_key_exists('title', $data) && $data['title'] !== $oldTitle) {
            BoardActivity::create(['board_id' => $board->id, 'user_id' => $userId, 'type' => 'card_title_updated', 'data' => ['old_title' => $oldTitle, 'new_title' => $card->title]]);
            $this->notifyAdmins($board, Auth::user()->name . " renamed card \"{$oldTitle}\" to \"{$card->title}\"", $card->id, $list->id);
        }
        if (array_key_exists('due_date', $data)) {
            if (is_null($data['due_date']) && !is_null($oldDueDate)) {
                CardActivity::create(['card_id' => $card->id, 'user_id' => $userId, 'type' => 'due_date_removed', 'data' => []]);
            } elseif (!is_null($data['due_date']) && $data['due_date'] != $oldDueDate) {
                CardActivity::create(['card_id' => $card->id, 'user_id' => $userId, 'type' => 'due_date_set', 'data' => ['due_date' => $card->due_date?->format('M d, Y')]]);
            }
        }
        if (array_key_exists('cover', $data)) {
            if (empty($data['cover']) && !empty($oldCover)) {
                CardActivity::create(['card_id' => $card->id, 'user_id' => $userId, 'type' => 'cover_removed', 'data' => []]);
            } elseif (!empty($data['cover']) && $data['cover'] !== $oldCover) {
                CardActivity::create(['card_id' => $card->id, 'user_id' => $userId, 'type' => 'cover_changed', 'data' => []]);
            }
        }

        try {
            broadcast(new CardUpdated($card))->toOthers();
            if (isset($data['labels'])) {
                broadcast(new CardLabelsUpdated($card))->toOthers();
            }
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'card' => $card]);
    }

    /**
     * Update card labels.
     */
    public function updateLabels(Request $request, Board $board, ListModel $list, Card $card)
    {
        $request->validate([
            'labels' => 'nullable|array'
        ]);

        $card->update(['labels' => $request->labels ?? []]);
        $card->refresh();

        try {
            broadcast(new CardLabelsUpdated($card))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'card' => $card, 'labels' => $card->labels]);
    }

    /**
     * Update card cover.
     */
    public function updateCover(Request $request, Board $board, ListModel $list, Card $card)
    {
        $card->update(['cover' => $request->cover]);
        try {
            broadcast(new CardCoverUpdated($card))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }
        return response()->json(['success' => true, 'cover' => $card->cover]);
    }

    /**
     * Move card between lists or positions.
     */
    public function move(Request $request, Board $board, ListModel $list, Card $card)
    {
        $request->validate([
            'list_id' => 'required|integer|exists:lists,id',
            'position' => 'nullable|integer'
        ]);
        
        $oldListId = $card->list_id;
        $oldList   = \App\Models\ListModel::find($oldListId);
        $card->update([
            'list_id' => $request->list_id,
            'position' => $request->position ?? 0
        ]);
        $newList = \App\Models\ListModel::find($request->list_id);
        CardActivity::create([
            'card_id' => $card->id,
            'user_id' => Auth::id(),
            'type'    => 'moved',
            'data'    => ['from_list' => $oldList->name ?? 'Unknown', 'to_list' => $newList->name ?? 'Unknown'],
        ]);
        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'card_moved',
            'data'     => ['card_title' => $card->title, 'from_list' => $oldList->name ?? 'Unknown', 'to_list' => $newList->name ?? 'Unknown'],
        ]);

        $actor = Auth::user()->name;
        $this->notifyAdmins($board, "{$actor} moved \"{$card->title}\" from {$oldList->name} to {$newList->name}", $card->id, $request->list_id);        broadcast(new CardMoved($card, $oldListId))->toOthers();
        return response()->json(['success' => true]);
    }

    /**
     * Archive the card.
     */
    public function archive(Request $request, Board $board, ListModel $list, Card $card)
    {
        $user = Auth::user();
        
        // Admin or Workspace Owner or Board Shared User check
        $isOwner = $board->workspace->isOwner($user->id);
        $isBoardSharedUser = $board->sharedUsers->contains($user->id);
        $isWorkspaceMember = $board->workspace->hasUser($user->id);

        if (!$user->isSystemAdmin() && !$isOwner && !$isBoardSharedUser && !$isWorkspaceMember) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $result = $card->update(['is_archived' => true]);
            
            if (!$result) {
                \Log::error('Card archive failed', ['card_id' => $card->id]);
                return response()->json(['success' => false, 'error' => 'Failed to archive card'], 500);
            }
            
            // Verify the update
            $card->refresh();
            if (!$card->is_archived) {
                \Log::error('Card archive verification failed', ['card_id' => $card->id]);
                return response()->json(['success' => false, 'error' => 'Archive verification failed'], 500);
            }
            
            broadcast(new CardArchived($card))->toOthers();
            CardActivity::create(['card_id' => $card->id, 'user_id' => Auth::id(), 'type' => 'archived', 'data' => []]);
            BoardActivity::create(['board_id' => $board->id, 'user_id' => Auth::id(), 'type' => 'card_archived', 'data' => ['card_title' => $card->title]]);
            $this->notifyAdmins($board, Auth::user()->name . " archived card \"{$card->title}\"");
            return response()->json(['success' => true, 'archived' => true]);
        } catch (\Exception $e) {
            \Log::error('Card archive exception', ['card_id' => $card->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete the card.
     */
    public function destroy(Board $board, ListModel $list, Card $card)
        {
            try {
                // Authorization check - only owner and admin can delete
                $currentUser = auth()->user();
                $isAdmin = $currentUser->isSystemAdmin();
                $isOwner = $board->workspace->isOwner($currentUser->id);

                if (!$isAdmin && !$isOwner) {
                    return response()->json(['success' => false, 'error' => 'Unauthorized. Only workspace owner and admin can delete cards.'], 403);
                }

                $cardId    = $card->id;
                $cardTitle = $card->title;
                $listName  = $card->list->name ?? 'Unknown';
                $card->delete();

                if (Card::find($cardId)) {
                    return response()->json(['success' => false, 'error' => 'Deletion verification failed'], 500);
                }

                BoardActivity::create([
                    'board_id' => $board->id,
                    'user_id'  => Auth::id(),
                    'type'     => 'card_deleted',
                    'data'     => ['card_title' => $cardTitle, 'list_name' => $listName],
                ]);
                $this->notifyAdmins($board, Auth::user()->name . " deleted card \"{$cardTitle}\" from {$listName}");

                return response()->json(['success' => true, 'deleted' => true]);
            } catch (\Exception $e) {
                \Log::error('Card deletion exception', ['card_id' => $card->id, 'error' => $e->getMessage()]);
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }

    /**
     * Restore an archived card.
     */
    public function restore(Request $request, Board $board, ListModel $list, Card $card)
    {
        $user = Auth::user();
        
        // Authorization check
        $isOwner = $board->workspace->isOwner($user->id);
        $isBoardSharedUser = $board->sharedUsers->contains($user->id);
        $isWorkspaceMember = $board->workspace->hasUser($user->id);

        if (!$user->isSystemAdmin() && !$isOwner && !$isBoardSharedUser && !$isWorkspaceMember) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        try {
            $result = $card->update(['is_archived' => false]);
            
            if (!$result) {
                \Log::error('Card restore failed', ['card_id' => $card->id]);
                return response()->json(['success' => false, 'error' => 'Failed to restore card'], 500);
            }
            
            // Verify the update
            $card->refresh();
            if ($card->is_archived) {
                \Log::error('Card restore verification failed', ['card_id' => $card->id]);
                return response()->json(['success' => false, 'error' => 'Restore verification failed'], 500);
            }
            CardActivity::create(['card_id' => $card->id, 'user_id' => Auth::id(), 'type' => 'restored', 'data' => []]);
            BoardActivity::create(['board_id' => $board->id, 'user_id' => Auth::id(), 'type' => 'card_restored', 'data' => ['card_title' => $card->title]]);
            $this->notifyAdmins($board, Auth::user()->name . " sent \"{$card->title}\" back to the board");
            return response()->json(['success' => true, 'archived' => false]);
        } catch (\Exception $e) {
            \Log::error('Card restore exception', ['card_id' => $card->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add member to card.
     */
    public function addMember(Request $request, Board $board, ListModel $list, Card $card)
    {
        $userId = $request->user_id;
        $workspace = $board->workspace;
        
        // Auto-share with board if not already
        if (!$board->sharedUsers()->where('user_id', $userId)->exists()) {
            $board->sharedUsers()->attach($userId);
        }

        // Add to workspace if not already a member
        if (!$workspace->hasUser($userId)) {
            $workspace->users()->attach($userId, ['role' => 'member', 'can_comment' => false]);
        }

        // Add to card members
        $card->members()->syncWithoutDetaching([$userId]);
        
        $user = User::find($userId);
        
        CardActivity::create([
            'card_id' => $card->id,
            'user_id' => Auth::id(),
            'type'    => 'member_added',
            'data'    => ['member_name' => $user->name],
        ]);

        $actor = Auth::user()->name;
        $this->notifyAdmins($board, "{$actor} added {$user->name} to \"{$card->title}\"", $card->id, $list->id);
        // Notify the added user too
        if ($user->id !== Auth::id()) {
            $user->notify(new BoardActivityNotification(
                "{$actor} added you to card \"{$card->title}\"",
                $board->id, $board->name, $card->id, $list->id, 'member_added'
            ));
        }

        broadcast(new CardMemberAdded($card, $user))->toOthers();
        return response()->json(['success' => true, 'user' => $user]);
    }

    /**     * Remove member from card.     */
    
    public function removeMember(Board $board, ListModel $list, Card $card, User $user)
    {
        $card->members()->detach($user->id);
        CardActivity::create([
            'card_id' => $card->id,
            'user_id' => Auth::id(),
            'type'    => 'member_removed',
            'data'    => ['member_name' => $user->name],
        ]);
        broadcast(new CardMemberRemoved($card, $user))->toOthers();
        return response()->json(['success' => true]);
    }

    /**
     * Store a comment on the card.
     */
    public function storeComment(Request $request, Board $board, ListModel $list, Card $card)
        {
            $currentUser = auth()->user();
            $workspace = $board->workspace;

            // Check if user has comment permission
            $isAdmin = $currentUser->isSystemAdmin();
            $isOwner = $workspace->isOwner($currentUser->id);

            // Get workspace member with pivot data
            $workspaceMember = $workspace->users()
                ->withPivot('can_comment')
                ->where('users.id', $currentUser->id)
                ->first();

            $canComment = $isAdmin || $isOwner || ($workspaceMember && $workspaceMember->pivot->can_comment);

            if (!$canComment) {
                return response()->json(['success' => false, 'error' => 'You do not have permission to comment'], 403);
            }

            $comment = $card->comments()->create([
                'content' => $request->content,
                'user_id' => Auth::id()
            ]);
            broadcast(new CommentPosted($card, $comment))->toOthers();

            // Notify all workspace members with comment preview
            $commentPreview = mb_substr(strip_tags($request->content), 0, 80);
            $notifMsg = "{$currentUser->name} commented on \"{$card->title}\"" . ($commentPreview ? ": \"{$commentPreview}\"" : '');
            $this->notifyAdmins($board, $notifMsg, $card->id, $list->id);

            // Detect @mentions and notify mentioned users
            $plainText = strip_tags($request->content);
            preg_match_all('/@([\w\s\.]+?)(?=\s|$|[^a-zA-Z0-9\s\.])/u', $plainText, $matches);
            if (!empty($matches[1])) {
                $boardUserIds = $board->sharedUsers()->pluck('users.id')->toArray();
                foreach ($matches[1] as $mentionedName) {
                    $mentionedName = trim($mentionedName);
                    $mentionedUser = User::whereIn('id', $boardUserIds)
                        ->where('name', 'like', "%{$mentionedName}%")
                        ->first();
                    if ($mentionedUser && $mentionedUser->id !== Auth::id()) {
                        $commentPreview = mb_substr(strip_tags($request->content), 0, 100);
                        $mentionedUser->notify(new MentionNotification(
                            $currentUser->name, $card->title,
                            $board->id, $board->name, $card->id, $list->id,
                            $commentPreview
                        ));
                    }
                }
            }

            return response()->json(['success' => true, 'comment' => $comment->load('user')]);
        }

    /**
     * Update a comment.
     */
    public function updateComment(Request $request, Board $board, ListModel $list, Card $card, $commentId)
    {
        $comment = $card->comments()->with('user')->findOrFail($commentId);
        $currentUser = auth()->user();

        if ($comment->user_id !== $currentUser->id && !$currentUser->isSystemAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->update([
            'content'   => $request->content,
            'is_edited' => true,
        ]);

        // Notify workspace members about edited comment
        $preview = mb_substr(strip_tags($request->content), 0, 80);
        $msg = "{$currentUser->name} edited a comment on \"{$card->title}\"" . ($preview ? ": \"{$preview}\"" : '');
        $this->notifyAdmins($board, $msg, $card->id, $list->id);

        return response()->json(['success' => true, 'comment' => $comment->load('user')]);
    }

    /**
     * Delete a comment.
     */
    public function destroyComment(Board $board, ListModel $list, Card $card, $commentId)
    {
     
        $comment = $card->comments()->with('user')->findOrFail($commentId);
        $currentUser = auth()->user();
        
        $isAdmin = $currentUser->isSystemAdmin();
        $isOwner = $board->workspace->isOwner($currentUser->id);
        $isAuthor = $comment->user_id === $currentUser->id;
        $commentAuthorIsAdmin = $comment->user && $comment->user->isSystemAdmin();

        // $canDelete = $isAdmin || $isAuthor || ($isOwner && !$commentAuthorIsAdmin);
$canDelete = $isAdmin || $isAuthor || ($isOwner && (!$commentAuthorIsAdmin || $isAdmin));
        if (!$canDelete) {
            return response()->json(['error' => 'Unauthorized to delete this comment'], 403);
        }
        
        $comment->delete();
        broadcast(new CommentDeleted($card, (int) $commentId))->toOthers();
        return response()->json(['success' => true]);
    }

    /**
     * Poll card state for real-time sync (no Pusher needed).
     */
    public function poll(Board $board, ListModel $list, Card $card)
    {
        $card->load(['checklistItems', 'members', 'attachments' => fn($q) => $q->orderBy('created_at', 'desc'), 'comments.user', 'list']);

        return response()->json([
            'card_id'        => $card->id,
            'title'          => $card->title,
            'description'    => $card->description,
            'cover'          => $card->cover,
            'labels'         => $card->labels ?? [],
            'is_archived'    => $card->is_archived,
            'list_id'        => $card->list_id,
            'list_name'      => $card->list->name ?? '',
            'updated_at'     => $card->updated_at?->toISOString(),
            'checklist_items' => $card->checklistItems->map(fn($i) => [
                'id'           => $i->id,
                'title'        => $i->title,
                'is_completed' => (bool) $i->is_completed,
            ]),
            'attachments' => $card->attachments->map(fn($a) => [
                    'id'         => $a->id,
                    'name'       => $a->name,
                    'file_path'  => $a->file_path,
                    'file_url'   => \Storage::url(str_replace(['/storage/', 'storage/'], '', $a->file_path ?? '')),
                    'is_image'   => (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $a->name ?? ''),
                    'created_at' => $a->created_at->diffForHumans(),
                ]),
            'members' => $card->members->map(fn($m) => [
                'id'   => $m->id,
                'name' => $m->name,
            ]),
            'comments' => $card->comments->map(fn($c) => [
                'id'              => $c->id,
                'content'         => $c->content,
                'user_id'         => $c->user_id,
                'created_at_human'=> $c->created_at->diffForHumans(),
                'user'            => [
                    'id'       => $c->user->id,
                    'name'     => $c->user->name,
                    'is_admin' => $c->user->isSystemAdmin(),
                ],
            ]),
        ]);
    }

    /**
     * Get card activity log.
     */
    public function getActivities(Board $board, ListModel $list, Card $card)
    {
        $activities = $card->activities()->with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'activities' => $activities->map(fn($a) => [
                'id'         => $a->id,
                'type'       => $a->type,
                'message'    => $a->getMessage(),
                'user_name'  => $a->user->name,
                'initials'   => strtoupper(substr($a->user->name, 0, 2)),
                'created_at' => $a->created_at->toISOString(),
                'diff'       => $a->created_at->diffForHumans(),
            ])
        ]);
    }

    /**
     * Poll for new comments.
     */
    public function pollComments(Board $board, ListModel $list, Card $card)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->isSystemAdmin();

        // Admin sees all including soft-deleted, others see only active
        $query = $isAdmin
            ? $card->comments()->withTrashed()->with('user')
            : $card->comments()->with('user');

        $comments = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'comments' => $comments->map(function($c) use ($isAdmin) {
                $isDeleted = !is_null($c->deleted_at);
                return [
                    'id'               => $c->id,
                    'content'          => $isDeleted ? null : $c->content,
                    'is_deleted'       => $isDeleted,
                    'is_edited'        => (bool) $c->is_edited,
                    'user_id'          => $c->user_id,
                    'created_at'       => $c->created_at->toISOString(),
                    'created_at_human' => $c->created_at->diffForHumans(),
                    'user'             => [
                        'id'       => $c->user->id,
                        'name'     => $c->user->name,
                        'is_admin' => $c->user->isSystemAdmin(),
                    ],
                ];
            })
        ]);
    }

    /**
     * Store a checklist item.
     */
    // public function storeChecklistItem(Request $request, Board $board, ListModel $list, Card $card)
    // {
    //     $item = $card->checklistItems()->create([
    //         'title' => $request->title ?? $request->content,
    //         'is_completed' => false
    //     ]);
    //     broadcast(new ChecklistItemCreated($card, $item))->toOthers();
    //     return response()->json(['success' => true, 'item' => $item]);
    // }
    public function storeChecklistItem(Request $request, Board $board, ListModel $list, Card $card)
    {
        $request->validate(['title' => 'required_without:content']);

        $item = $card->checklistItems()->create([
            'title'        => $request->title ?? $request->content,
            'is_completed' => false,
        ]);

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'checklist_item_added',
            'data'     => ['item_title' => $item->title, 'card_title' => $card->title],
        ]);
        try {
            $this->notifyAdmins($board, Auth::user()->name . " added \"{$item->title}\" to checklist on \"{$card->title}\"", $card->id, $list->id);
        } catch (\Exception $e) {
            \Log::error('Notification failed: ' . $e->getMessage());
        }

        try {
            broadcast(new ChecklistItemCreated($card, $item))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'item' => $item]);
    }
    /**
     * Update a checklist item.
     */
    public function updateChecklistItem(Request $request, Board $board, ListModel $list, Card $card, $itemId)
    {
        $item = $card->checklistItems()->findOrFail($itemId);
        $wasCompleted = $item->is_completed;
        $item->update($request->only(['title', 'is_completed']));

        if ($request->has('is_completed') && $request->is_completed != $wasCompleted) {
            CardActivity::create([
                'card_id' => $card->id,
                'user_id' => Auth::id(),
                'type'    => $request->is_completed ? 'checklist_item_completed' : 'checklist_item_uncompleted',
                'data'    => ['item_title' => $item->title],
            ]);
        }

        try {
            broadcast(new ChecklistItemUpdated($card, $item))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }
        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Delete a checklist item.
     */
    public function destroyChecklistItem(Board $board, ListModel $list, Card $card, $itemId)
    {
        $item = $card->checklistItems()->findOrFail($itemId);
        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'checklist_item_deleted',
            'data'     => ['item_title' => $item->title, 'card_title' => $card->title],
        ]);
        $this->notifyAdmins($board, Auth::user()->name . " removed \"{$item->title}\" from checklist on \"{$card->title}\"", $card->id, $list->id);
        $item->delete();
        try {
            broadcast(new ChecklistItemDeleted($card, (int) $itemId))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }
        return response()->json(['success' => true]);
    }

    /**
     * Delete all checklist items for a card.
     */
    public function destroyChecklist(Board $board, ListModel $list, Card $card)
    {
        $card->checklistItems()->delete();
        try {
            broadcast(new ChecklistCleared($card))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }
        return response()->json(['success' => true]);
    }

    public function storeAttachment(Request $request, Board $board, ListModel $list, Card $card)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('attachments', 'public');
            
            $attachment = $card->attachments()->create([
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'user_id' => Auth::id()
            ]);
            CardActivity::create(['card_id' => $card->id, 'user_id' => Auth::id(), 'type' => 'attachment_added', 'data' => ['file_name' => $file->getClientOriginalName()]]);
            return response()->json(['success' => true, 'attachment' => $attachment->load('user')]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * Upload cover directly (no attachment).
     */
    public function uploadCover(Request $request, Board $board, ListModel $list, Card $card)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('covers', 'public');
            
            // Store relative path in database for better portability
            $card->update(['cover' => $path]);
            
            $url = Storage::disk('public')->url($path);
            
            CardActivity::create(['card_id' => $card->id, 'user_id' => Auth::id(), 'type' => 'cover_changed', 'data' => []]);
            
            try {
                broadcast(new CardCoverUpdated($card))->toOthers();
            } catch (\Exception $e) {
                \Log::error("Broadcasting failed: " . $e->getMessage());
            }

            return response()->json(['success' => true, 'cover' => $url]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * Destroy attachment.
     */
    public function destroyAttachment(Board $board, ListModel $list, Card $card, $attachmentId)
    {
        $attachment = $card->attachments()->findOrFail($attachmentId);
        CardActivity::create(['card_id' => $card->id, 'user_id' => Auth::id(), 'type' => 'attachment_removed', 'data' => ['file_name' => $attachment->name]]);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Set attachment as card cover.
     */
    public function makeAttachmentCover(Board $board, ListModel $list, Card $card, $attachmentId)
    {
        $attachment = $card->attachments()->findOrFail($attachmentId);
        $url = Storage::disk('public')->url($attachment->file_path);
        $card->update(['cover' => $url]);
        try {
            broadcast(new CardCoverUpdated($card))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed: " . $e->getMessage());
        }
        return response()->json(['success' => true, 'cover' => $url]);
    }

    /**
     * Update member permission in workspace.
     */
    public function updateMemberPermission(Request $request, Board $board)
    {
        $workspace = $board->workspace;
        $userId = $request->user_id;
        $canComment = (bool)$request->can_comment;

        if ($workspace->hasUser($userId)) {
            $workspace->users()->updateExistingPivot($userId, ['can_comment' => $canComment]);
        } else {
            $workspace->users()->attach($userId, ['role' => 'member', 'can_comment' => $canComment]);
        }

        // Auto-add to board if granting permission
        if ($canComment && !$board->sharedUsers()->where('user_id', $userId)->exists()) {
            $board->sharedUsers()->attach($userId);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle all member permissions.
     */
    public function toggleAllPermissions(Request $request, Board $board)
    {
        $canComment = (bool)$request->can_comment;
        $workspace = $board->workspace;
        
        $users = $workspace->users()->wherePivot('role', '!=', 'owner')->get();
        foreach ($users as $user) {
            $workspace->users()->updateExistingPivot($user->id, ['can_comment' => $canComment]);
            if ($canComment && !$board->sharedUsers()->where('user_id', $user->id)->exists()) {
                $board->sharedUsers()->attach($user->id);
            }
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Update the client associated with the card.
     */
    public function updateClient(Request $request, Board $board, ListModel $list, Card $card)
    {
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $card->update(['client_id' => $request->client_id]);

        return response()->json(['success' => true, 'message' => 'Card client updated successfully']);
    }
}