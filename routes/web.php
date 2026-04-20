<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BoardAccessController;
use Laravel\Socialite\Facades\Socialite;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- | | Here is where you can register web routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "web" middleware group. Make something great! | */


Route::get('/clearcache', function () {

Artisan::call('storage:link ');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');

    return "Cache Cleared";
}); 



Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class , 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class , 'login']);
Route::get('/register', [AuthController::class , 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class , 'register']);
Route::post('/logout', [AuthController::class , 'logout'])->name('logout');

// Google icon
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/user/accessible-boards', function() {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->isSystemAdmin()) return response()->json(['boards' => []]);
        $sharedBoardIds = \Illuminate\Support\Facades\DB::table('board_user')->where('user_id', $user->id)->pluck('board_id')->toArray();
        $ownerWorkspaceIds = $user->workspaces()->wherePivotIn('role', ['owner','admin'])->pluck('workspaces.id')->toArray();
        $boards = \App\Models\Board::where('is_archived', false)
            ->where(function($q) use ($sharedBoardIds, $ownerWorkspaceIds) {
                $q->whereIn('id', $sharedBoardIds)->orWhereIn('workspace_id', $ownerWorkspaceIds);
            })->pluck('id')->toArray();
        return response()->json(['board_ids' => $boards]);
    })->name('user.accessible-boards');

    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Workspace Routes
    Route::resource('workspaces', WorkspaceController::class);
    Route::post('/workspaces/{workspace}/members', [WorkspaceController::class , 'addMember'])->name('workspaces.members.add');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceController::class , 'removeMember'])->name('workspaces.members.remove');
    Route::put('/workspaces/{workspace}/members/{user}/role', [WorkspaceController::class , 'updateMemberRole'])->name('workspaces.members.role');
    Route::post('/workspaces/{workspace}/members/{user}/board-access', [BoardAccessController::class, 'grantAccess'])->name('workspaces.members.board-access');

    // Board Routes
    Route::get('/boards/create', [BoardController::class , 'create'])->name('boards.create');
    Route::post('/boards', [BoardController::class , 'store'])->name('boards.store');
    Route::get('/boards/{board}', [BoardController::class , 'show'])->name('boards.show');
    Route::put('/boards/{board}', [BoardController::class , 'update'])->name('boards.update');
    Route::delete('/boards/{board}', [BoardController::class , 'destroy'])->name('boards.destroy');
    Route::post('/boards/{board}/archive', [BoardController::class , 'archive'])->name('boards.archive');
    Route::post('/boards/{board}/restore', [BoardController::class , 'restore'])->name('boards.restore');
    Route::get('/boards/{board}/activities', [BoardController::class, 'getActivities'])->name('boards.activities');
    Route::get('/boards/{board}/archived-cards', [BoardController::class , 'getArchivedCards'])->name('boards.archived-cards');
    Route::get('/archived-cards/all', [BoardController::class , 'getAllArchivedCards'])->name('archived-cards.all');
    
    // Board Sharing Routes
    Route::post('/boards/{board}/share', [BoardController::class , 'shareBoard'])->name('boards.share');
    Route::delete('/boards/{board}/unshare/{user}', [BoardController::class , 'unshareBoard'])->name('boards.unshare');
    Route::get('/boards/{board}/shared-users', [BoardController::class , 'getSharedUsers'])->name('boards.shared-users');
    Route::get('/boards/{board}/active-users', [BoardController::class , 'getActiveUsers'])->name('boards.active-users');
    
    // Board Share Link Routes
    Route::post('/boards/{board}/share-link', [BoardController::class, 'createShareLink'])->name('boards.share-link.create');
    Route::get('/boards/{board}/share-link', [BoardController::class, 'getShareLink'])->name('boards.share-link.get');
    Route::delete('/boards/{board}/share-link', [BoardController::class, 'deleteShareLinkByBoard'])->name('boards.share-link.delete-by-board');
    Route::delete('/boards/{board}/share-link/{shareLink}', [BoardController::class, 'deleteShareLink'])->name('boards.share-link.delete');
    Route::get('/share/{token}', [BoardController::class, 'joinViaShareLink'])->name('boards.join-via-link');

    // Board Join Request Routes
    Route::get('/boards/{board}/pending-requests', [BoardController::class, 'getPendingRequests'])->name('boards.pending-requests');
    Route::post('/boards/{board}/join-requests/{request}/approve', [BoardController::class, 'approveJoinRequest'])->name('boards.join-requests.approve');
    Route::post('/boards/{board}/join-requests/{request}/reject', [BoardController::class, 'rejectJoinRequest'])->name('boards.join-requests.reject');
    Route::get('/boards/{board}/join-requests/{requestId}/check-status', [BoardController::class, 'checkJoinRequestStatus'])->name('boards.join-requests.check-status');

    // Admin Pending Approvals Panel
    Route::get('/admin/pending-approvals', [BoardController::class, 'allPendingRequests'])->name('admin.pending-approvals');
    Route::post('/admin/pending-approvals/{request}/approve', [BoardController::class, 'approveJoinRequest'])->name('admin.join-requests.approve');
    Route::post('/admin/pending-approvals/{request}/reject', [BoardController::class, 'rejectJoinRequest'])->name('admin.join-requests.reject');

    // List Routes
    Route::post('/boards/{board}/lists', [ListController::class , 'store'])->name('lists.store');
    Route::put('/boards/{board}/lists/{list}', [ListController::class , 'update'])->name('lists.update');
    Route::post('/boards/{board}/lists/reorder', [ListController::class , 'reorder'])->name('lists.reorder');
    Route::delete('/boards/{board}/lists/{list}', [ListController::class , 'destroy'])->name('lists.destroy');

    // Card Routes
    Route::get('/boards/{board}/cards/{card}', [CardController::class, 'showByCardId'])->name('cards.show.by-id');
    Route::get('/boards/{board}/lists/{list}/cards/{card}', [CardController::class , 'show'])->name('cards.show');
    Route::post('/boards/{board}/lists/{list}/cards', [CardController::class , 'store'])->name('cards.store');
    Route::put('/boards/{board}/lists/{list}/cards/{card}', [CardController::class , 'update'])->name('cards.update');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/labels', [CardController::class , 'updateLabels'])->name('cards.labels.update');
    Route::put('/boards/{board}/lists/{list}/cards/{card}/labels', [CardController::class , 'updateLabels'])->name('cards.labels.update.put');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/move', [CardController::class , 'move'])->name('cards.move');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/archive', [CardController::class , 'archive'])->name('cards.archive');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/restore', [CardController::class , 'restore'])->name('cards.restore');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}', [CardController::class , 'destroy'])->name('cards.destroy');

    // Label Routes
    Route::post('/boards/{board}/labels', [BoardController::class , 'storeLabel'])->name('boards.labels.store');
    Route::put('/boards/{board}/labels/{label}', [BoardController::class , 'updateLabel'])->name('boards.labels.update');
    Route::delete('/boards/{board}/labels/{label}', [BoardController::class , 'deleteLabel'])->name('boards.labels.delete');

    // Checklist Routes
    Route::post('/boards/{board}/lists/{list}/cards/{card}/checklist-items', [CardController::class , 'storeChecklistItem'])->name('cards.checklist.store');
    Route::put('/boards/{board}/lists/{list}/cards/{card}/checklist-items/{itemId}', [CardController::class , 'updateChecklistItem'])->name('cards.checklist.update');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}/checklist-items/{itemId}', [CardController::class , 'destroyChecklistItem'])->name('cards.checklist.destroy');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}/checklist', [CardController::class, 'destroyChecklist'])->name('cards.checklist.destroy_all');

    // Card Members Routes
    Route::post('/boards/{board}/lists/{list}/cards/{card}/members', [CardController::class , 'addMember'])->name('cards.members.add');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}/members/{user}', [CardController::class , 'removeMember'])->name('cards.members.remove');

    // Attachment Routes
    Route::post('/boards/{board}/lists/{list}/cards/{card}/attachments', [CardController::class , 'storeAttachment'])->name('cards.attachments.store');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}/attachments/{attachment}', [CardController::class , 'destroyAttachment'])->name('cards.attachments.destroy');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/attachments/{attachment}/cover', [CardController::class , 'makeAttachmentCover'])->name('cards.attachments.cover');
    Route::post('/boards/{board}/lists/{list}/cards/{card}/upload-cover', [CardController::class , 'uploadCover'])->name('cards.upload-cover');
    // Comment Routes
    Route::post('/boards/{board}/lists/{list}/cards/{card}/comments', [CardController::class , 'storeComment'])->name('cards.comments.store');
    Route::put('/boards/{board}/lists/{list}/cards/{card}/comments/{comment}', [CardController::class, 'updateComment'])->name('cards.comments.update');
    Route::delete('/boards/{board}/lists/{list}/cards/{card}/comments/{comment}', [CardController::class , 'destroyComment'])->name('cards.comments.destroy');
    Route::get('/boards/{board}/lists/{list}/cards/{card}/poll', [CardController::class, 'poll'])->name('cards.poll');
    Route::get('/boards/{board}/lists/{list}/cards/{card}/comments/poll', [CardController::class , 'pollComments'])->name('cards.comments.poll');
    Route::get('/boards/{board}/lists/{list}/cards/{card}/activities', [CardController::class, 'getActivities'])->name('cards.activities');
    Route::post('/boards/{board}/permissions', [CardController::class , 'updateMemberPermission'])->name('boards.permissions.update');
    Route::post('/boards/{board}/permissions/toggle-all', [CardController::class , 'toggleAllPermissions'])->name('boards.permissions.toggle-all');

    // Reports Routes
    Route::get('/reports/results', [ReportsController::class , 'results'])->name('reports.results');
    Route::post('/reports/fetch-match-results', [ReportsController::class , 'fetchMatchResults'])->name('reports.fetch-match-results');

    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class , 'updatePassword'])->name('profile.password.update');

    // Admin Routes
    Route::get('/admin/users', [UserManagementController::class , 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserManagementController::class , 'store'])->name('admin.users.store');
    Route::post('/admin/users/{user}/toggle', [UserManagementController::class , 'toggleStatus'])->name('admin.users.toggle');
    Route::delete('/admin/users/{user}', [UserManagementController::class , 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/password', [UserManagementController::class , 'changePassword'])->name('admin.users.password');

});
