<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $tripId;

    public function __construct($messageId, $tripId)
    {
        $this->messageId = $messageId;
        $this->tripId = $tripId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('trip.' . $this->tripId);
    }
}