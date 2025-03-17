<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 


class ProfileController extends Controller
{
    
    public function show($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json(['user' => $user], 200);
}

    
public function update(Request $request, $id)
{
    $user = User::find($id);
    //dd($request->all());  
    // dd($request->name);
    //return response()->json([
       // 'name' => $request->name  // Retourne la valeur de 'name' dans une réponse JSON
    //]);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
        'phone_number' => 'sometimes|string|max:20',
        'photo_profile' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'birthday' => 'sometimes|date',
        'role_id' => 'sometimes|integer|exists:roles,id',
        'car_id' => 'sometimes|string|max:50',
        'drivingLicence' => 'sometimes|string|max:50',
        'password' => 'sometimes|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    
    if ($request->has('name')) {
        $user->name = $request->name;
    }

    if ($request->has('email')) {
        $user->email = $request->email;
    }

    if ($request->has('phone_number')) {
        $user->phone_number = $request->phone_number;
    }

    if ($request->has('birthday')) {
        $user->birthday = $request->birthday;
    }

    if ($request->has('role_id')) {
        $user->role_id = $request->role_id;
    }

    if ($request->has('car_id')) {
        $user->car_id = $request->car_id;
    }

    if ($request->has('drivingLicence')) {
        $user->drivingLicence = $request->drivingLicence;
    }

    if ($request->has('password')) {
        $user->password = Hash::make($request->password);
    }

    if ($request->hasFile('photo_profile')) {
        $photo = $request->file('photo_profile');
        $photoPath = $photo->store('profile_pictures', 'public');
        $user->photo_profile = $photoPath;
    }
   
    $user->save();
   

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user
    ], 200);
}


 
public function deleteAccount($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    try {
        $user->delete();
        return response()->json(['message' => 'Account deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Something went wrong'], 500);
    }
}

}
