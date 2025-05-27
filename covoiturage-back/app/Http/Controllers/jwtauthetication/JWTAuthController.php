<?php

namespace App\Http\Controllers\jwtauthetication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class JWTAuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birthday' => 'required|date',
            'role_id' => 'required|integer|exists:roles,id',
            'car_id' => 'nullable|string|max:50',
            'drivingLicence' => 'nullable|string|max:50',

        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),

            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'phone_number' => $request->get('phone_number'),
            'birthday' => $request->get('birthday'),
            'role_id' => $request->get('role_id'),
            'car_id' => $request->get('car_id'),
            'drivingLicence' => $request->get('drivingLicence'),
            'is_active' => $request->get('role_id') == 1 ? true : false,
        ]);

        $token = JWTAuth::fromUser($user);
        

        return response()->json(compact('user','token'), 201);
    }

   
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $access_token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

          
            $user = Auth::user();
            if (!$user->is_active) {
                return response()->json(['error' => 'Votre compte est en attente de validation. Contactez un administrateur.'], 403);
            }

         
            $access_token = JWTAuth::claims(['role_id' => $user->role_id])->fromUser($user);
            $access_token = JWTAuth::claims(['name' => $user->name])->fromUser($user);

            return response()->json([
                'access_token' => $access_token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role_id
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
           
            return response()->json($user, 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }
    }
    
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(Request $request)
   {
     try {
        $token = JWTAuth::refresh(JWTAuth::getToken());
      } catch (JWTException $e) {
        return response()->json(['error' => 'could_not_refresh_token'], 500);
     }

     return response()->json(compact('token'));
    }
    // Supprimer un utilisateur
public function deleteUser(Request $request, $id)
{
    try {
        
        $user = JWTAuth::parseToken()->authenticate();

       
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403); 
        }

        
        $userToDelete = User::find($id);

       
        if (!$userToDelete) {
            return response()->json(['error' => 'User not found'], 404);
        }

       
        $userToDelete->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);

    } catch (JWTException $e) {
        return response()->json(['error' => 'Invalid token'], 400);
    }
}
public function updateUser(Request $request, $id)
{
    try {
     
        $user = JWTAuth::parseToken()->authenticate();

       
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403); // Accès interdit si l'utilisateur n'est pas administrateur
        }

     
        $userToUpdate = User::find($id);

       
        if (!$userToUpdate) {
            return response()->json(['error' => 'User not found'], 404);
        }

       
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id, // Exclure l'email actuel de la validation
            'phone_number' => 'nullable|string|max:20',
            'birthday' => 'nullable|date',
            'role_id' => 'nullable|integer|exists:roles,id',
            'car_id' => 'nullable|string|max:50',
            'drivingLicence' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

      
        $userToUpdate->update([
            'name' => $request->get('name', $userToUpdate->name),
            'email' => $request->get('email', $userToUpdate->email),
            'phone_number' => $request->get('phone_number', $userToUpdate->phone_number),
            'birthday' => $request->get('birthday', $userToUpdate->birthday),
            'role_id' => $request->get('role_id', $userToUpdate->role_id),
            'car_id' => $request->get('car_id', $userToUpdate->car_id),
            'drivingLicence' => $request->get('drivingLicence', $userToUpdate->drivingLicence),
        ]);

        return response()->json(['message' => 'User updated successfully', 'user' => $userToUpdate], 200);

    } catch (JWTException $e) {
        return response()->json(['error' => 'Invalid token'], 400);
    }
}
public function activateUser($id)
{
    try {
        
        $admin = JWTAuth::parseToken()->authenticate();

       
        if ($admin->role_id !== 1) {
            return response()->json(['error' => 'Accès interdit'], 403); // Accès refusé si ce n'est pas un administrateur
        }

        
        $user = User::findOrFail($id);

        
        if ($user->is_active) {
            return response()->json(['message' => 'L\'utilisateur est déjà actif'], 400); // L'utilisateur est déjà actif
        }

        
        $user->is_active = true;
        $user->save();

        return response()->json(['message' => 'Utilisateur activé avec succès']);

    } catch (JWTException $e) {
        return response()->json(['error' => 'Erreur interne'], 500);
    }
}
 
 public function getUsers()
 {
     try {
        
         $admin = JWTAuth::parseToken()->authenticate();
         if ($admin->role_id !== 1) {
             return response()->json(['error' => 'Accès interdit'], 403); 
         }

        
         $users = User::where('is_active', false)->get();

         return response()->json($users, 200); 
     } catch (\Exception $e) {
         return response()->json(['error' => 'Erreur interne'], 500);
     }
 }
 public function getAdmin()
{
    try {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id // Vérifie que ceci est bien inclus
            ]
        ], 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Invalid token'], 400);
    }
}





   
}
