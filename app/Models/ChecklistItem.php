<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

   protected $fillable = ['title', 'is_completed', 'card_id'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    /**
     * Get the card that owns the checklist item.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
