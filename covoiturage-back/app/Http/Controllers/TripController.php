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
            'departure_time' => 'required|date_format:H:i',
            'estimate_arrival_time' => 'required|date_format:H:i',
            'price' => 'required|numeric',
            'instant_booking' => 'nullable|boolean',
            'available_seats' => 'nullable|integer|min:1',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
       
          $driverId = $user->id;

          
        
           $trip = Trip::create([
            'departure' => $request->get('departure'),
            'destination' => $request->get('destination'),
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
            'departure_time' => 'nullable|date_format:H:i',
            'estimate_arrival_time' => 'nullable|date_format:H:i',
            'price' => 'nullable|numeric',
            
            'instant_booking' => 'nullable|boolean',
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
    
        if ($request->has('price')) {
            $query->where('price', '<=', $request->price);
        }
    
        // Exécution de la requête
        $trips = $query->get();
    
        // Retour des résultats
        return response()->json($trips);
    }
    
}
