<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Card $card)
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
            'card_id'     => $this->card->id,
            'title'       => $this->card->title,
            'description' => $this->card->description,
            'list_id'     => $this->card->list_id,
            'updated_at'  => $this->card->updated_at->toISOString(),
        ];
    }
}
