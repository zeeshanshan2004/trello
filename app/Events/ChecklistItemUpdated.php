<?php

namespace App\Events;

use App\Models\Card;
use App\Models\ChecklistItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChecklistItemUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Card $card, public ChecklistItem $item)
    {
    }

    public function broadcastOn(): array
    {
        if (!$this->card->relationLoaded('list')) {
            $this->card->load('list');
        }

        return [
            new PrivateChannel('board.' . $this->card->list->board_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'card_id'      => $this->card->id,
            'id'           => $this->item->id,
            'title'        => $this->item->title,
            'is_completed' => $this->item->is_completed,
        ];
    }
}
