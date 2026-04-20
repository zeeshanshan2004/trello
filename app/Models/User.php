<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The workspaces that belong to the user.
     */
    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class , 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all boards from user's workspaces
     */
    public function boards()
    {
        return Board::whereHas('workspace', function ($query) {
            $query->whereHas('users', function ($q) {
                    $q->where('users.id', $this->id);
                }
                );
            });
    }

    /**
     * Get user's role in a specific workspace
     */
    public function getRoleInWorkspace($workspaceId): ?string
    {
        $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        return $workspace ? $workspace->pivot->role : null;
    }

    /**
     * Check if user owns a workspace
     */
    public function ownsWorkspace($workspaceId): bool
    {
        return $this->getRoleInWorkspace($workspaceId) === 'owner';
    }

    /**
     * Check if user is admin or owner of a workspace
     */
    public function isAdminOfWorkspace($workspaceId): bool
    {
        $role = $this->getRoleInWorkspace($workspaceId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Get workspaces where user is owner
     */
    public function ownedWorkspaces()
    {
        return $this->workspaces()->wherePivot('role', 'owner');
    }

    /**
     * Check if user is system admin
     */
    public function isSystemAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get boards that have been shared with this user
     */
    public function sharedBoards()
    {
        return $this->belongsToMany(Board::class, 'board_user')
                    ->withTimestamps();
    }
}
