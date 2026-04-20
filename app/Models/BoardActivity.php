<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardActivity extends Model
{
    protected $fillable = ['board_id', 'user_id', 'type', 'data'];
    protected $casts    = ['data' => 'array'];

    public function board(): BelongsTo { return $this->belongsTo(Board::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }

    public function getMessage(): string
    {
        $d = $this->data ?? [];

        // Match ke saath hamesha curly braces {} use karein
        return match($this->type) {
            'board_visited'      => "viewed this board",
            'card_created'       => "added card \"{$d['card_title']}\" to {$d['list_name']}",
            'card_deleted'       => "deleted card \"{$d['card_title']}\" from {$d['list_name']}",
            'card_moved'         => "moved \"{$d['card_title']}\" from {$d['from_list']} to {$d['to_list']}",
            'card_archived'      => "archived card \"{$d['card_title']}\"",
            'card_restored'      => "sent \"{$d['card_title']}\" back to the board",
            'card_title_updated' => "renamed card \"{$d['old_title']}\" to \"{$d['new_title']}\"",
            'card_desc_updated'  => "updated description of \"{$d['card_title']}\"",
            'list_created'       => "added list \"{$d['list_name']}\"",
            'list_deleted'       => "deleted list \"{$d['list_name']}\"",
            'list_renamed'       => "renamed list \"{$d['old_name']}\" to \"{$d['new_name']}\"",
            'member_added'       => "added {$d['member_name']} to the board",
            'member_removed'     => "removed {$d['member_name']} from the board",
            'board_updated'      => "updated board settings",
            'checklist_item_added'   => "added \"{$d['item_title']}\" to checklist on \"{$d['card_title']}\"",
            'checklist_item_deleted' => "removed \"{$d['item_title']}\" from checklist on \"{$d['card_title']}\"",
            default              => $this->type,
        };
    }
}