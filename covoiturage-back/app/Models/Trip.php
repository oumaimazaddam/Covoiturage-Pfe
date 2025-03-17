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
        'departure_time',
        'estimate_arrival_time',
        'price',
        'driver_id',
        'rating',
        'instant_booking',
        'available_seats',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'driver_id');
    }
   
    
}
