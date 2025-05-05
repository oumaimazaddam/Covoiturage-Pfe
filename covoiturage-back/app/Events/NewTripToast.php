<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTripToast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trip;
    public $userId; // To exclude the creator from receiving the toast

    public function __construct(Trip $trip, $userId)
    {
        $this->trip = $trip;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('trips-toast');
    }

    public function broadcastAs()
    {
        return 'new-trip-toast';
    }

    public function broadcastWith()
    {
        return [
            'trip' => [
                'id' => $this->trip->id,
                'departure' => $this->trip->departure,
                'destination' => $this->trip->destination,
                'trip_date' => $this->trip->trip_date,
                'departure_time' => $this->trip->departure_time,
                'price' => $this->trip->price,
                'available_seats' => $this->trip->available_seats,
            ],
            'user_id' => $this->userId, // Include to filter on frontend if needed
        ];
    }
}