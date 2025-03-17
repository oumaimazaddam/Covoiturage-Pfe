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
    // User registration
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
            
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'), 201);
    }

    // User login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $access_token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Get the authenticated user.
            $user = Auth::user();

            // (optional) Attach the role to the token.
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

    // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    // User logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
