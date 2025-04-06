<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $userId;
    public $trip_id; // Changed from rideId to tripId

    public function __construct($message, $userId, $trip_id)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->trip_id = $trip_id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('trip.' . $this->trip_id); // Updated to trip
    }
    public function broadcastWith()
    {
        return [
            'message' => $this->message->load('user'),
            'user' => $this->userId
        ];
    }
}