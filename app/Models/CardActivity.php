<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardActivity extends Model
{
    protected $fillable = ['card_id', 'user_id', 'type', 'data'];

    protected $casts = ['data' => 'array'];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns a human-readable activity message.
     */
    public function getMessage(): string
    {
        $d = $this->data ?? [];

        return match($this->type) {
            'created'                   => "added this card to {$d['list_name']}",
            'moved'                     => "moved this card from {$d['from_list']} to {$d['to_list']}",
            'member_added'              => "added {$d['member_name']} to this card",
            'member_removed'            => "removed {$d['member_name']} from this card",
            'label_added'               => "added the label \"{$d['label_name']}\"",
            'label_removed'             => "removed the label \"{$d['label_name']}\"",
            'due_date_set'              => "set the due date to {$d['due_date']}",
            'due_date_removed'          => "removed the due date",
            'description_changed'       => "updated the description",
            'cover_changed'             => "updated the cover",
            'cover_removed'             => "removed the cover",
            'archived'                  => "archived this card",
            'restored'                  => "sent this card to the board",
            'checklist_item_completed'  => "completed \"{$d['item_title']}\"",
            'checklist_item_uncompleted'=> "marked \"{$d['item_title']}\" incomplete",
            'attachment_added'          => "attached {$d['file_name']}",
            'attachment_removed'        => "removed the attachment {$d['file_name']}",
            default                     => $this->type,
        };
    }
}
