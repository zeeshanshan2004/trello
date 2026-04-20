<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BoardAccessController extends Controller
{
    /**
     * Grant a workspace member access to all non-archived boards in the workspace.
     */
    public function grantAccess(Request $request, Workspace $workspace, User $user): JsonResponse
    {
        // Authorization: system admin or workspace admin/owner
        if (!Auth::user()->isSystemAdmin() && !$workspace->isAdmin(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to grant board access.',
            ], 403);
        }

        // Validation: target user must be a workspace member — if not, add them first
        if (!$workspace->hasUser($user->id)) {
            $workspace->addMember($user->id, 'member');
        }

        try {
            $boards = $workspace->boards()->where('is_archived', false)->get();

            foreach ($boards as $board) {
                $board->sharedUsers()->syncWithoutDetaching([$user->id]);
            }

            $count = $boards->count();

            $message = $count > 0
                ? 'Access granted successfully.'
                : 'Member already has access to all boards.';

            return response()->json([
                'success'        => true,
                'message'        => $message,
                'boards_granted' => $count,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error.',
            ], 500);
        }
    }
}
