<?php

namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageToast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $tripId;
    public $recipientId;

    public function __construct($message, $tripId, $recipientId)
    {
        $this->message = $message;
        $this->tripId = $tripId;
        $this->recipientId = $recipientId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('trip.' . $this->tripId . '.user.' . $this->recipientId);
    }

    public function broadcastAs()
    {
        return 'new-toast';
    }
}
//Test