<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'image_url',
    ];

    /**
     * The users that belong to the workspace.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class , 'workspace_user')
            ->withPivot('role', 'can_comment')
            ->withTimestamps();
    }

    /**
     * Get the boards for the workspace.
     */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    /**
     * Get the icon for workspace
     */
    public function getDisplayIconAttribute()
    {
        if ($this->image_url) {
            return '<img src="' . asset('storage/' . $this->image_url) . '" alt="' . $this->name . '" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">';
        }
        return $this->icon ?: strtoupper(substr($this->name, 0, 1));
    }

    /**
     * Check if user is a member of this workspace
     */
    public function hasUser($userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's role in this workspace
     */
    public function getUserRole($userId): ?string
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user ? $user->pivot->role : null;
    }

    /**
     * Check if user is owner of this workspace
     */
    public function isOwner($userId): bool
    {
        return $this->getUserRole($userId) === 'owner';
    }

    /**
     * Check if user is admin or owner of this workspace
     */
    public function isAdmin($userId): bool
    {
        $role = $this->getUserRole($userId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Add user to workspace
     */
    public function addMember($userId, $role = 'member'): void
    {
        if (!$this->hasUser($userId)) {
            $this->users()->attach($userId, ['role' => $role]);
        }
    }

    /**
     * Remove user from workspace
     */
    public function removeMember($userId): void
    {
        $this->users()->detach($userId);
    }

    /**
     * Update user's role in workspace
     */
    public function updateMemberRole($userId, $role): void
    {
        $this->users()->updateExistingPivot($userId, ['role' => $role]);
    }

    /**
     * Get owners of this workspace
     */
    public function owners()
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get admins of this workspace
     */
    public function admins()
    {
        return $this->users()->wherePivotIn('role', ['owner', 'admin']);
    }

    /**
     * Get total member count
     */
    public function getMemberCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Get total board count
     */
    public function getBoardCountAttribute(): int
    {
        return $this->boards()->where('is_archived', false)->count();
    }
}
