<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class TripController extends Controller
{
   

    public function index()
    {
        $trips = Trip::all();
        return response()->json($trips);
    }
    public function show($id)
    {
        $trip = Trip::findOrFail($id);
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
       
          $driverId = $user->id;

          
        
           $trip = Trip::create([
            'departure' => $request->get('departure'),
            'destination' => $request->get('destination'),
            'trip_date' => $request->get('trip_date'),
            'departure_time' =>$request->get('departure_time'),
            'estimate_arrival_time' =>$request->get('estimate_arrival_time'),
            'price' =>$request->get('price'),
            'instant_booking' =>$request->get('instant_booking'),
            'available_seats' =>$request->get('available_seats'),
            'driver_id' =>$driverId,
        ]);
        
       
        return response()->json([
            'user' => $user,
            'trip' => $trip,
    ], 201);

    }

    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        $validatedData = $request->validate([
            'departure' => 'nullable|string',
            'destination' => 'nullable|string',
            'trip_date' => 'nullable|date',
            'departure_time' => 'nullable|date_format:H:i',
            'estimate_arrival_time' => 'nullable|date_format:H:i',
            'price' => 'nullable|numeric',
            
           'instant_booking' => 'required|boolean',
            'available_seats' => 'nullable|integer|min:1',
        ]);

        $trip->update($validatedData);
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
        $trip = Trip::find($tripId);
        $user = User::find($passengerId);

        if (!$trip || !$user) {
            return response()->json(['message' => 'Trip or Passenger not found'], 404);
        }

        // Ajouter le passager au trajet
        $trip->passengers()->attach($user->id);

        return response()->json(['message' => 'Passenger added to trip'], 200);
    }
//Test
    // Récupérer le conducteur d'un trajet
    public function getDriver($tripId)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        $driver = $trip->driver;  // Récupérer le conducteur du trajet

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        return response()->json($driver);
    }
    
    
}
