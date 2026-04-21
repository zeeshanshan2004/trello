<?php

namespace App\Models;

use App\Models\CardComment;
use App\Models\CardActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'title',
        'description',
        'cover',
        'labels',
        'position',
        'user_id',
        'is_archived',
        'start_date',
        'due_date',
        'client_id',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'labels' => 'array',
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Get the list that owns the card.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ListModel::class , 'list_id');
    }

    /**
     * Get the checklist items for the card.
     */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position');
    }

    /**
     * Get the members assigned to the card.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class , 'card_user')->withTimestamps();
    }

    /**
     * Get the attachments for the card.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the comments for the card.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the activity log for the card.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CardActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the client associated with the card.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
