<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public function driverTripReviews(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un conducteur (role_id = 2)
        if (!$user || $user->role_id !== 2) {
            return response()->json(['message' => 'Non autorisé. Seuls les conducteurs peuvent voir leurs avis.'], 403);
        }

        // Récupérer les trip_id associés au conducteur depuis la table trip_passenger
        $tripIds = DB::table('trip_passenger')
            ->where('driver_id', $user->id)
            ->pluck('trip_id')
            ->unique();

        // Si aucun trajet n'est trouvé
        if ($tripIds->isEmpty()) {
            return response()->json([
                'message' => 'Aucun trajet trouvé pour ce conducteur.',
                'reviews' => []
            ], 200);
        }

        // Récupérer les avis pour les trajets du conducteur
        $reviews = Review::whereIn('trip_id', $tripIds)
            ->with(['trip', 'passenger'])
            ->get()
            ->map(function ($review) {
                return [
                    'review_id' => $review->id,
                    'trip_id' => $review->trip_id,
                    'departure' => $review->trip->departure,
                    'destination' => $review->trip->destination,
                    'trip_date' => $review->trip->trip_date,
                    'passenger_name' => $review->passenger->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment ?? 'Aucun commentaire',
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Calculer la note moyenne
        $averageRating = $reviews->avg('rating') ?: 0;

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 2),
            'total_reviews' => $reviews->count(),
        ], 200);
    }
    public function allReviewsSummary(Request $request)
{
    try {
        // Récupérer tous les avis
        $reviews = Review::select('rating')
            ->get();

        // Compter les avis par catégorie
        $ratingsCount = [
            'tresSatisfait' => 0, // 4-5 étoiles
            'satisfait' => 0,     // 3 étoiles
            'insatisfait' => 0,   // 1-2 étoiles
            'total_reviews' => $reviews->count(),
        ];

        foreach ($reviews as $review) {
            if ($review->rating >= 4) {
                $ratingsCount['tresSatisfait'] += 1;
            } elseif ($review->rating === 3) {
                $ratingsCount['satisfait'] += 1;
            } else {
                $ratingsCount['insatisfait'] += 1;
            }
        }

        return response()->json([
            'message' => 'Résumé des avis récupéré avec succès',
            'ratings' => [
                'tres_satisfait' => $ratingsCount['tresSatisfait'],
                'satisfait' => $ratingsCount['satisfait'],
                'insatisfait' => $ratingsCount['insatisfait'],
            ],
            'total_reviews' => $ratingsCount['total_reviews'],
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la récupération du résumé des avis'], 500);
    }
}
}