<?php

use App\Http\Controllers\AddressUserController;
use App\Http\Controllers\AddressTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PhoneTypeController;
use App\Http\Controllers\PhoneUserController;
use App\Http\Controllers\UserController;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::middleware('auth:sanctum')->get('/user/{user}', [UserController::class,'show']);
Route::get('/auth', AuthController::class);
//Route::get('/auth', [AuthController::class,'show']);
Route::get('/countries', [CountryController::class, 'index']);

Route::get('/address-types', [AddressTypeController::class, 'index']);

Route::get('/phone-types', [PhoneTypeController::class, 'index']);

Route::get('/users', [UserController::class, 'index']);

Route::post('/store-address/user/{user}',[AddressUserController::class,'store']);
Route::post('/update-address/user/{id}/address/{address}',[AddressUserController::class,'update']);
Route::delete('/delete-address/user/{user}/address/{address}',[AddressUserController::class,'destroy']);
Route::get('/available-address-types/user/{id}', [AddressUserController::class, 'getAvailableAddressTypes']);

Route::post('/store-phone/user/{user}',[PhoneUserController::class,'store']);
Route::post('/update-phone/user/{id}/phone/{phone}',[PhoneUserController::class,'update']);
Route::delete('/delete-phone/user/{user}/phone/{phone}',[PhoneUserController::class,'destroy']);
Route::get('/available-phone-types/user/{id}', [PhoneUserController::class, 'getAvailablePhoneTypes']);

Route::get('/user-addresses/{id}',[UserController::class,'getUserAddresses']);
Route::get('/user-phones/{id}',[UserController::class,'getUserPhones']);
Route::get('/user-roles/{id}',[UserController::class,'getUserRoles']);
