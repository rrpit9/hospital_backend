<?php

namespace App\Http\Controllers\Api\Client\V1;

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

Route::group(['middleware'=> ['auth:client-api','scope:client']],function(){
    // Authenticated API Routes will appear here
    Route::get('profile',[AuthenticationController::class,'getUserProfile']);
    Route::post('update-profile',[AuthenticationController::class, 'updateUserProfile']);
    Route::post('change-password',[AuthenticationController::class, 'updateUserPassword']);
    
    /** Business Routing */
    Route::get('business',[ClientController::class, 'businessList']);
    Route::post('business/create',[ClientController::class, 'storeBusiness']);
    Route::post('business/{id}/update',[ClientController::class, 'updateBusiness']);
    Route::post('business/{id}/delete',[ClientController::class, 'deleteBusiness']);

    /** Employee Routing */
    Route::get('employee',[ClientController::class, 'employeeList']);
    Route::post('employee/create',[ClientController::class, 'storeEmployee']);
    Route::post('employee/{id}/update',[ClientController::class, 'updateEmployee']);
    Route::post('employee/{id}/delete',[ClientController::class, 'deleteEmployee']);

    /** Notification Routing */
    Route::get('notification',[AuthenticationController::class, 'getUserNotification']);

    // Logout API
    Route::any('logout',[AuthenticationController::class,'logoutFromSingleDevice']);
    Route::any('logout_from_all',[AuthenticationController::class,'logoutFromAllDevice']);
});