<?php

namespace App\Events;

use App\Models\Card;
use App\Models\CardComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Card $card, public CardComment $comment)
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
        if (!$this->comment->relationLoaded('user')) {
            $this->comment->load('user');
        }

        return [
            'card_id'          => $this->card->id,
            'id'               => $this->comment->id,
            'content'          => $this->comment->content,
            'created_at_human' => $this->comment->created_at->diffForHumans(),
            'user'             => [
                'id'   => $this->comment->user->id,
                'name' => $this->comment->user->name,
            ],
        ];
    }
}
