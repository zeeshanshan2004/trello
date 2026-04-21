<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'background_type',
        'background_value',
        'is_archived',
        'is_starred',
        'last_viewed_at',
        'client_id',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_starred' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Get the workspace that owns the board.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the lists for the board.
     */
    public function lists()
    {
        return $this->hasMany(ListModel::class, 'board_id')
                    ->where('is_archived', false)
                    ->orderBy('position');
    }

    /**
     * Get labels associated with the board.
     */
    public function labels()
    {
        return $this->hasMany(\App\Models\Label::class)->orderBy('id');
    }

    /**
     * Get users who have been shared this board.
     */
    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'board_user')
                    ->withTimestamps();
    }

    /**
     * Get the activity log for the board.
     */
    public function activities()
    {
        return $this->hasMany(BoardActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get share links for the board.
     */
    public function shareLinks()
    {
        return $this->hasMany(BoardShareLink::class);
    }

    /**
     * Get join requests for the board.
     */
    public function joinRequests()
    {
        return $this->hasMany(BoardJoinRequest::class);
    }

    /**
     * Get the client associated with the board.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
