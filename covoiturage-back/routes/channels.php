<?php

use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('trip.{tripId}', function ($user, $tripId) {
    return \App\Models\Trip::where('id', $tripId)
        ->where(function ($query) use ($user) {
            $query->whereHas('driver', fn($q) => $q->where('id', $user->id)) // Driver
                  ->orWhereHas('passengers', fn($q) => $q->where('id', $user->id)); // Passengers
        })->exists();
});

// In routes/channels.php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return $user->id === $userId;
  });