<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // Afficher toutes les réservations
    public function index()
    {
        return response()->json(Reservation::with('users')->get());
    }

    // Créer une nouvelle réservation
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_trajet' => 'required|date',
            'heure_depart' => 'required',
            'ville_depart' => 'required|string',
            'heure_arrivee' => 'required',
            'ville_arrivee' => 'required|string',
            'prix_total' => 'required|numeric',
            'nombre_passagers' => 'required|integer|min:1',
        ]);

        $reservation = Reservation::create($request->all());

        return response()->json($reservation, 201);
    }

    // Afficher une réservation spécifique
    public function show($id)
    {
        $reservation = Reservation::with('users')->get();;
        return response()->json($reservation);
    }

    // Mettre à jour une réservation
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $request->validate([
            'date_trajet' => 'sometimes|date',
            'heure_depart' => 'sometimes',
            'ville_depart' => 'sometimes|string',
            'heure_arrivee' => 'sometimes',
            'ville_arrivee' => 'sometimes|string',
            'prix_total' => 'sometimes|numeric',
            'nombre_passagers' => 'sometimes|integer|min:1',
        ]);

        $reservation->update($request->all());

        return response()->json($reservation);
    }

    // Supprimer une réservation
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Réservation supprimée avec succès']);
    }
}
