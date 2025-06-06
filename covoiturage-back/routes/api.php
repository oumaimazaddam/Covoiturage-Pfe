<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\jwtauthetication\JWTAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Broadcast; // Add this line
use App\Http\Controllers\ChatController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ReviewController;
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


Route::get('/search-trip', [TripController::class, 'search']);
Route::get('trips', [TripController::class, 'index']);
Route::get('/all-trips', [TripController::class, 'allTrips']); 
Route::get('shows-trip/{id}', [TripController::class, 'shows']);
//
Route::get('trips', [TripController::class, 'index']); 
Route::get('trips/{id}', [TripController::class, 'show']);
Route::get('trips/{tripId}/driver', [TripController::class, 'getDriver']);

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
    Route::get('/user', [JWTAuthController::class, 'getUser']);
    //Trip
    Route::post('/create-trip', [TripController::class, 'store']);
    Route::put('trips/{id}', [TripController::class, 'update']);
    Route::delete('trips/{id}', [TripController::class, 'destroy']);
    Route::get('show-trip/{id}', [TripController::class, 'show']);
    Route::get('/trips-by-city', [TripController::class, 'tripsByCity']);
    Route::get('trips', [TripController::class, 'index']);
    Route::get('/all-trips', [TripController::class, 'allTrips']); 
    Route::get('shows-trip/{id}', [TripController::class, 'shows']);
    Route::get('/dashboard/counts', [TripController::class, 'getDashboardCounts']);
    
    //Reservation
    Route::post('trips/{tripId}/passenger/{passengerId}', [TripController::class, 'addPassenger']);
   Route::get('trips/{id}/driver', [TripController::class, 'getDriver']);
   Route::delete('/trips/{tripId}/passengers/{passengerId}', [TripController::class, 'removePassenger']);
   Route::get('/reservations', [TripController::class, 'getReservations']);
   Route::get('/reservations/passenger/{passengerId}', [TripController::class, 'getReservationsByPassenger']);

//test
   //Profile
   Route::get('show-profile/{id}', [ProfileController::class, 'show']);   
   Route::put('update-profile/{id}', [ProfileController::class, 'update']);  
   Route::delete('delete-profile/{id}', [ProfileController::class, 'deleteProfile']);
   Route::get('/user-profile', [ProfileController::class, 'getCurrentUser']);
   //Messages
   Route::get('/chat/messages/{tripId}', [ChatController::class, 'fetchMessages']);
   Route::post('/create-message', [ChatController::class, 'sendMessage']);
   Route::delete('/chat/messages/{messageId}', [ChatController::class, 'deleteMessage']);
   //Route::get('/messages/{userId}', [ChatController::class, 'index']);

//Reviews
Route::post('/reviews', [ReviewController::class, 'store']); // Submit a review
Route::get('/drivers/{driverId}/reviews', [ReviewController::class, 'index']);
Route::get('/all-reviews', [ReviewController::class, 'indexAdmin']);
Route::get('/driver-reviews', [ReviewController::class, 'driverTripReviews']);
Route::get('/reviews-summary', [ReviewController::class, 'allReviewsSummary']);

//
Route::post('/broadcasting/auth', function (Request $request) {
    try {
        // Authenticate the user using the JWT token from the Authorization header
        $user = JWTAuth::parseToken()->authenticate();
        
        // Delegate to Laravel's BroadcastController to authorize the channel
        return app(\Illuminate\Broadcasting\BroadcastController::class)->authenticate($request);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['error' => 'Token expired'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Token invalid'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
})->middleware('auth:api');
});