<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // Valider les données de la requête
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Récupérer le trajet
        $trip = Trip::findOrFail($request->trip_id);

       // Verify user is a passenger
       if (!$trip->passengers()->where('passenger_id', Auth::id())->exists()) {
        return response()->json(['message' => 'You are not a passenger of this trip'], 403);
    }

        // Empêcher les avis en double
       // Prevent duplicate reviews
       if (Review::where('trip_id', $request->trip_id)->where('passenger_id', Auth::id())->exists()) {
        return response()->json(['message' => 'You have already reviewed this trip'], 400);
    }

       // Get driver_id from trip_passenger pivot table
       $driver = $trip->drivers()->first();
       if (!$driver) {
           return response()->json(['message' => 'No driver assigned to this trip'], 400);
       }

        // Créer l'avis
        $review = Review::create([
            'trip_id' => $request->trip_id,
            'passenger_id' => Auth::id(),
            'driver_id' => $driver->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Avis soumis avec succès',
            'review' => $review
        ], 201);
    }

    // Les méthodes index et indexAdmin restent inchangées
    public function index($driverId)
    {
        $reviews = Review::where('driver_id', $driverId)
            ->with('passenger')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'passenger_name' => $review->passenger->name,
                    'created_at' => $review->created_at->format('d/m/Y'),
                ];
            });

        $averageRating = $reviews->avg('rating') ?: 0;

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 2),
        ]);
    }

    public function indexAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        $reviews = Review::with(['trip', 'passenger', 'driver'])
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'trip_id' => $review->trip_id,
                    'departure' => $review->trip->departure,
                    'destination' => $review->trip->destination,
                    'passenger_name' => $review->passenger->name,
                    'driver_name' => $review->driver->name,
                    'comment' => $review->comment ?? 'Aucun commentaire',
                    'rating' => $review->rating, // Corrigé ici, remplacé 'review' par 'rating'
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($reviews);
    }
}