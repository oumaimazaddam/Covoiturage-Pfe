<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\jwtauthetication\JWTAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\TripController;

use App\Http\Controllers\ChatController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
//Utilisateur
Route::post('register', [JWTAuthController::class, 'register']);
Route::post('login', [JWTAuthController::class, 'login']);
Route::post('logout', [JWTAuthController::class, 'logout']);
Route::post('refresh', [JWTAuthController::class, 'refresh']);
Route::delete('user/{id}', [JWTAuthController::class, 'deleteUser']);
Route::put('user/{id}', [JWTAuthController::class, 'updateUser']);
Route::post('/admin/activate-user/{id}', [JWTAuthController::class, 'activateUser']);

//
Route::get('trips', [TripController::class, 'index']); 
Route::get('trips/{id}', [TripController::class, 'show']);
//
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('users', [JWTAuthController::class, 'getUser']);
    Route::post('refresh', [JWTAuthController::class, 'refresh']);
    Route::post('logout', [JWTAuthController::class, 'logout']);
    Route::delete('user/{id}', [JWTAuthController::class, 'deleteUser']);
    Route::put('user/{id}', [JWTAuthController::class, 'updateUser']);
    Route::get('/admin/users', [JWTAuthController::class, 'getUsers']);
    Route::put('/admin/activate-user/{id}', [JWTAuthController::class, 'activateUser']);
    Route::get('/admins/users', [JWTAuthController::class, 'getAdmin']);
    //Trip
    Route::post('/create-trip', [TripController::class, 'store']);
    Route::put('trips/{id}', [TripController::class, 'update']);
    Route::delete('trips/{id}', [TripController::class, 'destroy']);
    Route::get('show-trip/{id}', [TripController::class, 'show']);
    Route::get('/search-trip', [TripController::class, 'search']);
    Route::get('trips', [TripController::class, 'index']); 
    //Reservation
    Route::post('trips/{tripId}/passenger/{passengerId}', [TripController::class, 'addPassenger']);
   Route::get('trips/{tripId}/driver', [TripController::class, 'getDriver']);
   //Profile
   Route::get('show-profile/{id}', [ProfileController::class, 'show']);   
   Route::put('update-profile/{id}', [ProfileController::class, 'update']);  
   Route::delete('delete-profile/{id}', [ProfileController::class, 'deleteAccount']);
   Route::get('/user-profile', [ProfileController::class, 'getCurrentUser']);
   //Messages
   Route::get('/chat/messages/{tripId}', [ChatController::class, 'fetchMessages']);
   Route::post('/create-message', [ChatController::class, 'sendMessage']);
   Route::delete('/chat/messages/{messageId}', [ChatController::class, 'deleteMessage']);
   //Route::get('/messages/{userId}', [ChatController::class, 'index']);

});