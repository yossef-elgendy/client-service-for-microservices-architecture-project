<?php

use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


///////////////////////// Wituout Auth //////////////////////////
Route::post('/register', [ClientController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware'=> ['auth:sanctum']], function(){
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('children', ChildController::class)->parameters(['children' => 'child']);

    Route::apiResource('reservations', ReservationController::class)->except(['destroy']);

    Route::apiResource("mediafiles", MediafileController::class)
    ->except(['show']);

    Route::apiResource('clients', ClientController::class)->except(['show', 'store']);

    Route::get('/notifications', function(Request $request) {
        return response()->json($request->user()->notifications);
    });
});










///////////////////////// Social Auth //////////////////////////
Route::group(['prefix' => 'auth'], function() {
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirectToProvider']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
});

///////////////////////// Email Verification //////////////////////////
// Route::group(['prefix' => 'email'], function() {
//     Route::get('/verify', [EmailVerificationController::class, 'notice'])
//       ->name('verification.notice');

//     Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
//       ->middleware(['auth:sanctum', 'signed'])
//       ->name('verification.verify');

//     Route::post('/verification-notification', [EmailVerificationController::class, 'send'])
//       ->middleware(['auth:sanctum', 'throttle:6,1'])
//       ->name('verification.send');
// });


