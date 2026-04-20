<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MentionNotification extends Notification
{
    public function __construct(
        public string  $mentionedBy,
        public string  $cardTitle,
        public int     $boardId,
        public string  $boardName,
        public int     $cardId,
        public int     $listId,
        public string  $commentText = ''
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $message = "{$this->mentionedBy} mentioned you in \"{$this->cardTitle}\"";
        if ($this->commentText) {
            $preview = mb_strlen($this->commentText) > 50 
                ? mb_substr($this->commentText, 0, 50) . '...' 
                : $this->commentText;
            $message .= ": \"{$preview}\"";
        }

        return [
            'message'    => $message,
            'board_id'   => $this->boardId,
            'board_name' => $this->boardName,
            'card_id'    => $this->cardId,
            'list_id'    => $this->listId,
            'type'       => 'mention',
        ];
    }
}
