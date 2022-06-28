<?php

use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PayMobController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SubscriptionController;
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



Route::apiResource('children', ChildController::class)->parameters(['children' => 'child']);

Route::apiResource('reservations', ReservationController::class)->except(['destroy']);
Route::get('/reservation/child/{id}', [ReservationController::class , 'reservationByChild']);
Route::get('/reservations/child/{id}', [ReservationController::class , 'reservationsByChild']);


Route::apiResource("mediafiles", MediafileController::class)
->except(['show']);

Route::apiResource("reviews", ReviewController::class);

Route::apiResource('clients', ClientController::class)->except(['show']);
Route::get('/clients/profile', [ClientController::class, 'show']);

Route::get('/notifications', function(Request $request) {
    return response()->json($request->user()->notifications);
});

Route::group(['prefix' => 'payment'], function() {
    Route::post('{orderId}/pay/redirect/{payment_method}', [PayMobController::class, 'checkingOut']);
    Route::get('pay/callback', [PayMobController::class, 'processedCallback']); // for card only
});

Route::apiResource('subscriptions', SubscriptionController::class)->except(['store']);
Route::get('/subscription/child/{id}', [SubscriptionController::class , 'subscriptionByChild']);
Route::get('/subscriptions/child/{id}', [SubscriptionController::class , 'subscriptionsByChild']);





