<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{ use HasFactory;

    protected $fillable = [
    'user_id',
    'date_trajet',
    'heure_depart',
    'ville_depart',
    'heure_arrivee',
    'ville_arrivee',
    'prix_total',
    'nombre_passagers',
];

// Relation avec le conducteur (User)
public function users()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
