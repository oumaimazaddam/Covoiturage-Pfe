<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\jwtauthetication\JWTAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\TripController;
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
Route::post('register', [JWTAuthController::class, 'register']);
Route::post('login', [JWTAuthController::class, 'login']);
//
Route::get('trips', [TripController::class, 'index']); 
Route::get('trips/{id}', [TripController::class, 'show']);

//

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('user', [JWTAuthController::class, 'getUser']);
    Route::post('logout', [JWTAuthController::class, 'logout']);
    //
    
    Route::post('/create-trip', [TripController::class, 'store']);
    Route::put('trips/{id}', [TripController::class, 'update']);
    Route::delete('trips/{id}', [TripController::class, 'destroy']);
    Route::get('/search-trip', [TripController::class, 'search']);

   //
   Route::get('show-profile/{id}', [ProfileController::class, 'show']);   
   Route::put('update-profile/{id}', [ProfileController::class, 'update']);  
   Route::delete('delete-profile/{id}', [ProfileController::class, 'deleteAccount']);
});