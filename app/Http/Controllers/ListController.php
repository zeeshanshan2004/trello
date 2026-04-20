<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListModel;
use App\Models\Board;
use App\Models\BoardActivity;
use App\Notifications\BoardActivityNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ListController extends Controller
{
    /**
     * Store a newly created list in storage.
     */
    private function notifyMembers(Board $board, string $message): void
    {
        $allIds = array_unique(array_merge(
            $board->workspace->users()->pluck('users.id')->toArray(),
            $board->sharedUsers()->pluck('users.id')->toArray()
        ));
        foreach (\App\Models\User::whereIn('id', $allIds)->get() as $user) {
            if ($user->id !== Auth::id()) {
                $user->notify(new BoardActivityNotification($message, $board->id, $board->name));
            }
        }
    }

    public function store(Request $request, Board $board)
    {
        // Check if user has access to board's workspace (Admin and Workspace Owner bypass)
        $user = Auth::user();
        if (!$user->isSystemAdmin() && !$board->workspace->hasUser($user->id) && !$board->workspace->isOwner($user->id)) {
            abort(403, 'You do not have access to this board.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the highest position
        $maxPosition = $board->lists()->max('position') ?? -1;

        $list = ListModel::create([
            'board_id' => $board->id,
            'name' => $request->name,
            'position' => $maxPosition + 1,
        ]);

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'list_created',
            'data'     => ['list_name' => $list->name],
        ]);

        $this->notifyMembers($board, Auth::user()->name . " added list \"{$list->name}\"");

        return redirect()->route('boards.show', $board)
            ->with('success', 'List created successfully!');
    }

    /**
     * Update the specified list.
     */
    public function update(Request $request, Board $board, ListModel $list)
    {
        // Check if user has access to board's workspace (Admin and Workspace Owner bypass)
        $user = Auth::user();
        if (!$user->isSystemAdmin() && !$board->workspace->hasUser($user->id) && !$board->workspace->isOwner($user->id)) {
            abort(403, 'You do not have access to this board.');
        }

        // Check if list belongs to board
        if ($list->board_id !== $board->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $oldName = $list->name;
        $list->update(['name' => $request->name]);

        if ($oldName !== $request->name) {
            BoardActivity::create([
                'board_id' => $board->id,
                'user_id'  => Auth::id(),
                'type'     => 'list_renamed',
                'data'     => ['old_name' => $oldName, 'new_name' => $request->name],
            ]);
            $this->notifyMembers($board, Auth::user()->name . " renamed list \"{$oldName}\" to \"{$request->name}\"");
        }

        return redirect()->back()->with('success', 'List updated successfully!');
    }

    /**
     * Reorder lists for a board.
     */
    public function reorder(Request $request, Board $board)
    {
        // Check if user has access to board's workspace (Admin and Workspace Owner bypass)
        $user = Auth::user();
        if (!$user->isSystemAdmin() && !$board->workspace->hasUser($user->id) && !$board->workspace->isOwner($user->id)) {
            return response()->json(['success' => false, 'error' => 'You do not have access to this board.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'list_ids' => 'required|array',
            'list_ids.*' => 'required|integer|exists:lists,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => 'Invalid list IDs.'], 400);
        }

        $listIds = $request->list_ids;

        // Verify all lists belong to this board
        $lists = ListModel::whereIn('id', $listIds)
            ->where('board_id', $board->id)
            ->get();

        if ($lists->count() !== count($listIds)) {
            return response()->json(['success' => false, 'error' => 'Some lists do not belong to this board.'], 400);
        }

        // Update positions based on the order in the array
        foreach ($listIds as $index => $listId) {
            ListModel::where('id', $listId)
                ->where('board_id', $board->id)
                ->update(['position' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified list.
     */
    public function destroy(Board $board, ListModel $list)
    {
        // Check if user has access to board's workspace (Admin and Workspace Owner bypass)
        $user = Auth::user();
        if (!$user->isSystemAdmin() && !$board->workspace->hasUser($user->id) && !$board->workspace->isOwner($user->id)) {
            abort(403, 'You do not have access to this board.');
        }

        // Check if list belongs to board
        if ($list->board_id !== $board->id) {
            abort(404);
        }

        BoardActivity::create([
            'board_id' => $board->id,
            'user_id'  => Auth::id(),
            'type'     => 'list_deleted',
            'data'     => ['list_name' => $list->name],
        ]);

        $this->notifyMembers($board, Auth::user()->name . " deleted list \"{$list->name}\"");
        $list->delete();

        return redirect()->back()->with('success', 'List deleted successfully!');
    }
}
