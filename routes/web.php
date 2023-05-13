<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['login' => true, 'register' => false, 'verify' => true, 'logout' => false]);

/** Admin Routes */
Route::group(['prefix' => 'admin','middleware' => ['auth:admin','verified']], function () {
    Route::get('dashboard', [HomeController::class, 'adminDashboard']);
});

/** Client Routes */
Route::group(['prefix' => 'client','middleware' => ['auth:client','verified']], function () {
    Route::get('dashboard', [HomeController::class, 'clientDashboard']);
});

/** Employee Routes */
Route::group(['prefix' => 'employee','middleware' => ['auth:employee','verified']], function () {
    Route::get('dashboard', [HomeController::class, 'employeeDashboard']);
});

/** Customer Routes */
Route::group(['prefix' => 'customer','middleware' => ['auth:customer','verified']], function () {
    Route::get('dashboard', [HomeController::class, 'customerDashboard']);
});

Route::any('logout', [LoginController::class, 'logout'])->name('logout');
