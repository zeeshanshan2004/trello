<?php

namespace App\Events;

use App\Models\Card;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardMemberRemoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Card $card, public User $user)
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
            'card_id' => $this->card->id,
            'user_id' => $this->user->id,
        ];
    }
}
