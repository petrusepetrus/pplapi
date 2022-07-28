<?php

use App\Http\Controllers\AddressUserController;
use App\Http\Controllers\AddressTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PhoneTypeController;
use App\Http\Controllers\PhoneUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTypeController;
use App\Http\Controllers\UserTypeStatusController;
use App\Http\Controllers\UserUserTypeController;
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
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/auth', AuthController::class);

    Route::get('/user/{user}', [UserController::class, 'show']);

    Route::get('/countries', [CountryController::class, 'index']);

    Route::get('/address-types', [AddressTypeController::class, 'index']);

    Route::get('/phone-types', [PhoneTypeController::class, 'index']);

    Route::get('/user-types', [UserTypeController::class, 'index']);

    Route::get('/user-type-status', [UserTypeStatusController::class, 'index']);


    Route::get('/available-address-types/user/{id}', [AddressUserController::class, 'getAvailableAddressTypes']);
    Route::get('/available-phone-types/user/{id}', [PhoneUserController::class, 'getAvailablePhoneTypes']);
    Route::get('/available-user-types/user/{id}', [UserUserTypeController::class, 'getAvailableUserTypes']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user-types/user/{id}', [UserController::class, 'getUserUserTypes']);
    Route::post('/update-user-types/user/{id}', [UserController::class, 'updateUserTypes']);
    Route::get('/user-addresses/{id}',[UserController::class,'getUserAddresses']);
    Route::get('/user-phones/{id}',[UserController::class,'getUserPhones']);
    Route::get('/user-roles/{id}',[UserController::class,'getUserRoles']);
    Route::get('/user-permissions/{id}',[UserController::class,'getUserPermissions']);
    Route::get('/user/{id}',[UserController::class,'show']);
    Route::put('/user/{id}',[UserController::class,'update']);
});

