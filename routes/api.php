<?php

use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Auth\AuthController;
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



Route::group(['middleware'=> ['auth:sanctum']], function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/client', [AuthController::class, 'index']);
    Route::apiResource('children', ChildController::class)->parameters(['children' => 'child']);
});
Route::post('/register', [AuthController::class ,'register']);
Route::post('/login', [AuthController::class, 'login']);


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
