<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSystemAdmin()) {
            return $this->adminDashboard($user);
        }

        return $this->userDashboard($user);
    }

    private function adminDashboard($user)
    {
        $workspaces = Workspace::with('boards')->get();

        $ownedWorkspaces = $user->workspaces()->wherePivotIn('role', ['owner', 'admin'])->get();
        $memberWorkspaces = Workspace::whereNotIn('id', $ownedWorkspaces->pluck('id'))->get();

        $recentBoards = Board::where('is_archived', false)
            ->orderBy('last_viewed_at', 'desc')
            ->take(4)
            ->get();

        $workspaceBoards = [];
        foreach ($workspaces as $workspace) {
            $workspaceBoards[$workspace->id] = $workspace->boards()
                ->where('is_archived', false)
                ->orderBy('is_starred', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('dashboard', [
            'user'                      => $user,
            'workspaces'                => $workspaces,
            'ownedWorkspaces'           => $ownedWorkspaces,
            'memberWorkspaces'          => $memberWorkspaces,
            'canCreateBoardWorkspaces'  => $workspaces,
            'recentBoards'              => $recentBoards,
            'workspaceBoards'           => $workspaceBoards,
            'users'                     => User::all(),
            'myWorkspaceIds'            => $workspaces->pluck('id')->toArray(),
            'canCreateBoardWorkspaceIds'=> $workspaces->pluck('id')->toArray(),
            'allCards'                  => $this->getAllAdminCards(),
        ]);
    }

    private function userDashboard($user)
    {
        // Boards this user has been explicitly granted access to
        $sharedBoardIds = DB::table('board_user')
            ->where('user_id', $user->id)
            ->pluck('board_id')
            ->toArray();

        // Workspaces user is a member of
        $allUserWorkspaces = $user->workspaces()->with('users')->get();

        // Workspaces where user is owner/admin
        $ownerAdminWorkspaceIds = $allUserWorkspaces
            ->filter(fn($ws) => in_array($ws->pivot->role, ['owner', 'admin']))
            ->pluck('id')
            ->toArray();

        // Split into "My Workspaces" vs "Member Of"
        $ownedWorkspaces = $allUserWorkspaces->filter(function ($ws) use ($user) {
            if (in_array($ws->pivot->role, ['owner', 'admin'])) return true;
            $owner = $ws->users()->wherePivot('role', 'owner')->first();
            return $owner && !$owner->isSystemAdmin();
        })->values();

        $memberWorkspaces = $allUserWorkspaces->filter(function ($ws) use ($user) {
            if (in_array($ws->pivot->role, ['owner', 'admin'])) return false;
            $owner = $ws->users()->wherePivot('role', 'owner')->first();
            return $owner && $owner->isSystemAdmin();
        })->values();

        // Build workspace list (only workspaces that have visible boards for this user)
        $workspaces = $allUserWorkspaces->collect();

        // Group boards per workspace — owners see all, members see only shared
        $workspaceBoards = [];
        foreach ($workspaces as $workspace) {
            $pivotRole = optional($workspace->pivot)->role ?? 'member';
            $isOwnerAdmin = in_array($pivotRole, ['owner', 'admin']);

            $boards = Board::where('workspace_id', $workspace->id)
                ->where('is_archived', false)
                ->when(!$isOwnerAdmin, fn($q) => $q->whereIn('id', $sharedBoardIds))
                ->orderBy('is_starred', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Only include workspace if it has at least one visible board
            if ($boards->isNotEmpty()) {
                $workspaceBoards[$workspace->id] = $boards;
            }
        }

        // Recent boards: owners see all in their workspaces, members see only shared
        $recentBoards = Board::where('is_archived', false)
            ->where(function ($q) use ($user, $sharedBoardIds, $ownerAdminWorkspaceIds) {
                $q->whereIn('id', $sharedBoardIds)
                  ->orWhereIn('workspace_id', $ownerAdminWorkspaceIds);
            })
            ->orderBy('last_viewed_at', 'desc')
            ->take(4)
            ->get();

        $visibleWorkspaceIds = array_keys($workspaceBoards);

        // Filter workspace lists to only show workspaces with visible boards
        $ownedWorkspaces   = $ownedWorkspaces->filter(fn($ws) => in_array($ws->id, $visibleWorkspaceIds))->values();
        $memberWorkspaces  = $memberWorkspaces->filter(fn($ws) => in_array($ws->id, $visibleWorkspaceIds))->values();
        $workspaces        = $workspaces->filter(fn($ws) => in_array($ws->id, $visibleWorkspaceIds))->values();

        $canCreateBoardWorkspaceIds = $allUserWorkspaces
            ->filter(fn($ws) => in_array($ws->pivot->role, ['owner', 'admin']))
            ->pluck('id')
            ->toArray();

        return view('dashboard', [
            'user'                      => $user,
            'workspaces'                => $workspaces,
            'ownedWorkspaces'           => $ownedWorkspaces,
            'memberWorkspaces'          => $memberWorkspaces,
            'canCreateBoardWorkspaces'  => $workspaces->filter(fn($ws) => in_array($ws->id, $canCreateBoardWorkspaceIds)),
            'recentBoards'              => $recentBoards,
            'workspaceBoards'           => $workspaceBoards,
            'users'                     => collect(),
            'myWorkspaceIds'            => $allUserWorkspaces->pluck('id')->toArray(),
            'canCreateBoardWorkspaceIds'=> $canCreateBoardWorkspaceIds,
            'allCards'                  => $this->getAllUserCards($user, $sharedBoardIds, $ownerAdminWorkspaceIds),
        ]);
    }

    private function getAllUserCards($user, $sharedBoardIds, $ownerAdminWorkspaceIds)
    {
        $cards = \App\Models\Card::where('is_archived', false)
            ->whereHas('list.board', function($q) use ($sharedBoardIds, $ownerAdminWorkspaceIds, $user) {
                if ($user->isSystemAdmin()) {
                    $q->whereNotNull('id');
                } else {
                    $q->where(function($subQ) use ($sharedBoardIds, $ownerAdminWorkspaceIds) {
                        $subQ->whereIn('id', $sharedBoardIds)
                             ->orWhereIn('workspace_id', $ownerAdminWorkspaceIds);
                    });
                }
            })
            ->with(['list' => function($q) { $q->select('id', 'name', 'board_id'); }])
            ->with(['list.board' => function($q) { $q->select('id', 'name'); }])
            ->select('id', 'title', 'list_id')
            ->limit(100)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->title,
                'list_id' => $c->list_id,
                'list_name' => $c->list->name ?? '',
                'board_id' => $c->list->board_id ?? '',
                'board_name' => $c->list->board->name ?? '',
                'type' => 'card'
            ])
            ->toArray();

        return $cards;
    }


    private function getAllAdminCards()    {
        $cards = \App\Models\Card::where('is_archived', false)
            ->with(['list' => function($q) { $q->select('id', 'name', 'board_id'); }])
            ->with(['list.board' => function($q) { $q->select('id', 'name'); }])
            ->select('id', 'title', 'list_id')
            ->limit(100)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->title,
                'list_id' => $c->list_id,
                'list_name' => $c->list->name ?? '',
                'board_id' => $c->list->board_id ?? '',
                'board_name' => $c->list->board->name ?? '',
                'type' => 'card'
            ])
            ->toArray();

        return $cards;
    }

}