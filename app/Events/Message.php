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

    private $user_type = "";
    private $user = null;
    public $message = null;

    /**
     * Message constructor.
     * @param string $user_type
     * @param \App\Models\User|\App\Models\User $user
     * @param \App\Models\Message $message
     */

    public function __construct($user_type, $user, $message)
    {
        $this->user_type = $user_type;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $user_typee = '';
        if ($this->user_type == "App\Models\User") {
            $user_typee = 'user';
        }
        return new Channel($user_typee . '.' . $this->user->id);
    }
}
