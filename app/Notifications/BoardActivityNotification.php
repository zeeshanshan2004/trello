<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BoardActivityNotification extends Notification
{
    public function __construct(
        public string $message,
        public int    $boardId,
        public string $boardName,
        public ?int   $cardId   = null,
        public ?int   $listId   = null,
        public string $type     = 'activity'
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message'    => $this->message,
            'board_id'   => $this->boardId,
            'board_name' => $this->boardName,
            'card_id'    => $this->cardId,
            'list_id'    => $this->listId,
            'type'       => $this->type,
        ];
    }
}
