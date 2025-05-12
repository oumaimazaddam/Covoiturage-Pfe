<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTripPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trip;
    public $driverName;

    public function __construct(Trip $trip, string $driverName)
    {
        $this->trip = $trip;
        $this->driverName = $driverName;
    }

    public function broadcastOn()
    {
        return new Channel('trips');
    }

    public function broadcastAs()
    {
        return 'trip.published';
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
                'estimate_arrival_time' => $this->trip->estimate_arrival_time,
                'price' => $this->trip->price,
                'available_seats' => $this->trip->available_seats,
                'instant_booking' => $this->trip->instant_booking,
                'status' => $this->trip->status,
                'driver_name' => $this->driverName,
            ]
        ];
    }
}