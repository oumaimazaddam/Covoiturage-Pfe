<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure',
        'destination',
        'trip_date',
        'departure_time',
        'estimate_arrival_time',
        'price',
        'instant_booking',
        'available_seats',
    ];


    public function passengers()
    {
        return $this->belongsToMany(User::class, 'trip_passenger', 'trip_id', 'passenger_id')
                    ->withTimestamps();
    }

    // Relation avec le conducteur
    public function drivers()
    {
        return $this->belongsToMany(User::class, 'trip_passenger', 'trip_id', 'driver_id')
        ->withTimestamps();
    }
    public function users()
    {
        return $this->belongsTo(User::class,'driver_id');
    }
    
   
    
}
