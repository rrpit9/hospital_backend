<?php

namespace App\Http\Controllers\Api\Customer\V1;

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
Route::post('login',[AuthenticationController::class,'login']);

Route::group(['middleware'=> ['auth:customer-api','scope:customer']],function(){
    // Authenticated API Routes will appear here
    Route::get('profile',[AuthenticationController::class,'getUserProfile']);
    Route::get('notification',[AuthenticationController::class, 'getUserNotification']);
    Route::post('update-profile',[AuthenticationController::class, 'updateUserProfile']);
    Route::post('change-pin',[AuthenticationController::class, 'updateUserLoginPin']);
    Route::post('change-password',[AuthenticationController::class, 'updateUserPassword']);

    // Logout API
    Route::any('logout',[AuthenticationController::class,'logoutFromSingleDevice']);
    Route::any('logout_from_all',[AuthenticationController::class,'logoutFromAllDevice']);
});