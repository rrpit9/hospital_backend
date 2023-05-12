<?php

namespace App\Http\Controllers\Api\Admin\V1;

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

Route::group(['middleware'=> ['auth:admin-api','scope:admin']],function(){
    // Authenticated API Routes will appear here
    Route::get('profile',[AuthenticationController::class,'getUserProfile']);
    Route::get('notification',[AuthenticationController::class, 'getUserNotification']);

    Route::post('client/business/upgrade/initiate-order',[AdminController::class, 'upgradeClientBusinessPlan']);
    
    // Logout API
    Route::any('logout',[AuthenticationController::class,'logoutFromSingleDevice']);
    Route::any('logout_from_all',[AuthenticationController::class,'logoutFromAllDevice']);
});