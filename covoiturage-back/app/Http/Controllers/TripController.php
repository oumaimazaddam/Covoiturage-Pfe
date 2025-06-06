<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\NewTripPublished;
use App\Events\NewTripToast;
use App\Events\TripUpdated;
class TripController extends Controller
{
   

    public function index()
    {
        $user = Auth::user(); 
        $trips = Trip::whereHas('drivers', function ($query) use ($user) {
            $query->where('driver_id', $user->id); 
        })->get();
    
        return response()->json($trips);
    }
    public function allTrips()
     {
    $trips = Trip::all();
    return response()->json($trips);
    }
    public function show($id)
    {
        $trip = Trip::with('drivers')->findOrFail($id);
        $driver = $trip->drivers()->first();
        return response()->json([
            'message' => 'Trip retrieved successfully',
            'trip' => $trip,
            'driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driver->name,
                // Ajoutez d'autres champs si nécessaire
            ] : null,
            'has_driver' => !is_null($driver),
        ]);
    }
    public function shows($id)
    {
        $trip = Trip::find($id);
        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }
        return response()->json($trip);
    }

    public function store(Request $request)
    {
       
        $user = Auth::user();

      
        $validator = Validator::make($request->all(), [
            'departure' => 'required|string',
            'destination' => 'required|string',
            'trip_date' => 'required|date',
            'departure_time' => 'required|date_format:H:i',
            'estimate_arrival_time' => 'required|date_format:H:i',
            'price' => 'required|numeric',
            'instant_booking' => 'required|boolean',
            'available_seats' => 'nullable|integer|min:1',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        if (strtotime($request->trip_date) < strtotime(date('Y-m-d'))) {
            return response()->json(['error' => 'La date du trajet ne peut pas être dans le passé'], 400);
        }
       
           $trip = Trip::create([
            'departure' => $request->get('departure'),
            'destination' => $request->get('destination'),
            'trip_date' => $request->get('trip_date'),
            'departure_time' =>$request->get('departure_time'),
            'estimate_arrival_time' =>$request->get('estimate_arrival_time'),
            'price' =>$request->get('price'),
            'instant_booking' =>$request->get('instant_booking'),
            'available_seats' =>$request->get('available_seats'),
            'status' => 'active',
          
        ]);
       
        $trip->drivers()->attach($user->id, ['passenger_id' => $user->id]);
        event(new NewTripPublished($trip, $user->name));
        event(new NewTripToast($trip, $user->id, $user->name));
    
        return response()->json([
            'message' => 'Trip created successfully',
            'user' => $user,
            'trip' => $trip,
    ], 201);

    }
    public function getDashboardCounts()
    {
        try {
            // Count total users
            $totalUsers = User::where('is_active', true)->count();

            // Count drivers (users with role_id = 2, assuming role_id 2 is for drivers)
            $driversCount = User::where('is_active', true)
                ->where('role_id', 2)
                ->count();

            // Count passengers (users with role_id = 3, assuming role_id 3 is for passengers)
            $passengersCount = User::where('is_active', true)
                ->where('role_id', 3)
                ->count();

            // Count total trips
            $tripsCount = Trip::count();

            return response()->json([
                'total_users' => $totalUsers,
                'drivers' => $driversCount,
                'passengers' => $passengersCount,
                'trips' => $tripsCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    

    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        $user = Auth::user();
        $validatedData = $request->validate([
            'departure' => 'nullable|string',
            'destination' => 'nullable|string',
            'trip_date' => 'nullable|date',
            'departure_time' => 'nullable|date_format:H:i',
            'estimate_arrival_time' => 'nullable|date_format:H:i',
            'price' => 'nullable|numeric',

            'instant_booking' => 'required|boolean',
            'available_seats' => 'nullable|integer|min:1',
            'status' => 'nullable|in:active,completed,canceled',
        ]);

        $trip->update($validatedData);
        event(new TripUpdated($trip, $user->id, $user->name));
        return response()->json($trip);
    }

  
    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);
        $trip->delete();
        return response()->json(['message' => 'Trip deleted successfully']);
    }
    public function search(Request $request)
    {
        $query = Trip::query();
    
        // Filtrage des paramètres de recherche
        if ($request->has('departure')) {
            $query->where('departure', 'like', '%' . $request->departure . '%');
        }
    
        if ($request->has('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }
        if ($request->has('trip_date')) {
            $query->whereDate('trip_date', $request->trip_date);
        }
    
    
        if ($request->has('price')) {
            $query->where('price', '<=', $request->price);
        }
    
        // Exécution de la requête
        $trips = $query->get();
    
        // Retour des résultats
        return response()->json($trips);
    }
    public function addPassenger($tripId, $passengerId)
    {
        if (Auth::id() && Auth::id() != $passengerId) {
        return response()->json(['message' => 'Unauthorized to reserve for another user'], 403);
    }
        $trip = Trip::find($tripId);
        $user = User::find($passengerId);
               
     
        if (!$trip || !$user) {
            return response()->json(['message' => 'Trip or Passenger not found'], 404);
        }
        if ($trip->passengers()->where('passenger_id', $passengerId)->exists()) {
            return response()->json(['message' => 'Passager déjà réservé'], 422);
        }
        if ($trip->available_seats <= 0) {
            return response()->json(['message' => 'No seats available'], 422);
        }
        $driver = $trip->drivers()->first();
        if (!$driver) {
            return response()->json(['message' => 'No driver assigned to this trip'], 422);
        }
        $driverId = $driver->id;
        $trip->available_seats -= 1;
        $trip->passengers()->attach($passengerId, ['driver_id' => $driverId]);
        $trip->save();

        return response()->json([
            'message' => 'Reservation successful',
            'trip_id' => $trip->id,
            'passenger_id' => $passengerId,
            'available_seats' => $trip->available_seats,
            'status' => $trip->status,
            
        ], 200);
    }

    // Récupérer le conducteur d'un trajet
    public function getDriver($tripId)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        // Get driver from trip_passenger table
        $driver = $trip->drivers()->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        return response()->json([
            'id' => $driver->id,
            'name' => $driver->name,
            // Add other driver fields as needed
        ], 200);
    }
    public function removePassenger($tripId, $passengerId)
{
    // Check if the authenticated user is the passenger trying to cancel
    if (Auth::id() && Auth::id() != $passengerId) {
        return response()->json(['message' => 'Non autorisé à annuler la réservation pour un autre utilisateur'], 403);
    }

    $trip = Trip::find($tripId);
    $user = User::find($passengerId);

    // Check if trip and user exist
    if (!$trip || !$user) {
        return response()->json(['message' => 'Trip or Passenger not found'], 404);
    }

    // Check if the passenger is actually reserved for the trip
    if (!$trip->passengers()->where('passenger_id', $passengerId)->exists()) {
        return response()->json(['message' => 'Passenger not reserved for this trip'], 422);
    }

    // Remove the passenger from the trip
    $trip->passengers()->detach($passengerId);

    // Increment available seats
    $trip->available_seats += 1;
    $trip->status = 'active'; // Revert to active if seats become available
    $trip->save();

    return response()->json([
        'message' => 'Reservation cancelled successfully',
        'trip_id' => $trip->id,
        'passenger_id' => $passengerId,
        'available_seats' => $trip->available_seats,
    ], 200);
}
public function getReservations()
{
    $reservations = Trip::has('passengers')
        ->with(['passengers', 'drivers'])
        ->get()
        ->flatMap(function ($trip) {
            return $trip->passengers->map(function ($passenger) use ($trip) {
                return [
                    'trip_id' => $trip->id,
                    'departure' => $trip->departure,
                    'destination' => $trip->destination,
                    'trip_date' => $trip->trip_date,
                    'departure_time' => $trip->departure_time,
                    'price' => $trip->price,
                    'passenger_name' => $passenger->name,
                    'driver_name' => $trip->drivers->first()->name ?? 'N/A',
                    'status' => $trip->status,
                ];
            });
        });

    return response()->json($reservations);
}
    
public function getReservationsByPassenger($passengerId)
{
    $reservations = Trip::has('passengers')
        ->with(['passengers' => function ($query) use ($passengerId) {
            $query->where('passenger_id', $passengerId);
        }, 'drivers'])
        ->whereHas('passengers', function ($query) use ($passengerId) {
            $query->where('passenger_id', $passengerId);
        })
        ->get()
        ->flatMap(function ($trip) {
            return $trip->passengers->map(function ($passenger) use ($trip) {
                return [
                    'trip_id' => $trip->id,
                    'departure' => $trip->departure,
                    'destination' => $trip->destination,
                    'trip_date' => $trip->trip_date,
                    'departure_time' => $trip->departure_time,
                    'price' => $trip->price,
                    'passenger_name' => $passenger->name,
                    'driver_name' => $trip->drivers->first()->name ?? 'N/A',
                    'status' => $trip->status,
                ];
            });
        });

    return response()->json($reservations);
}
public function tripsByCity(Request $request)
{
    try {
        // Récupérer tous les trajets et regrouper par ville de départ
        $tripsByCity = Trip::select('departure')
            ->groupBy('departure')
            ->get()
            ->mapWithKeys(function ($trip) {
                return [$trip->departure => Trip::where('departure', $trip->departure)->count()];
            });

        // Convertir en format adapté pour le frontend
        $cities = $tripsByCity->keys()->toArray();
        $counts = $tripsByCity->values()->toArray();
        $totalTrips = $tripsByCity->sum();

        return response()->json([
            'message' => 'Répartition des trajets par ville récupérée avec succès',
            'cities' => $cities,
            'counts' => $counts,
            'total_trips' => $totalTrips,
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la récupération des trajets par ville'], 500);
    }
}
}
