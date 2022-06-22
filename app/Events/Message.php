<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Message
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private $chat;
    public $isGroup;

    /**
     * Message constructor.
     * @param string $user_type
     * @param \App\Models\User|\App\Models\User $user
     * @param \App\Models\Message $message
     */

    public function __construct($user, $chat, $isGroup = false)
    {
        $this->user = $user;
        $this->chat = $chat;
        $this->isGroup = $isGroup;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $id = $this->isGroup === true ? $this->chat->group_id : $this->user->id;
        return new Channel((string) $id);
    }
}
