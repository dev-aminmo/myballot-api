<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionController;
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

Route::post("/register",[UserController::class,"register"]);
Route::post("/login",[UserController::class,"login"]);
//Route::get("/login",[UserController::class,"login"])->name('login');
Route::middleware('auth:api')->group(function (){
    Route::group(['prefix'=>'/user'],function(){
        Route::get("",[UserController::class,"index"]);
        Route::post("/avatar/update",[UserController::class,"updateAvatar"]);
        Route::post("/logout",[UserController::class,"logout"]);
        //Route::post("add",[ReviewController::class,"addReview"]);
    });
    Route::middleware('role:organizer')->group(function (){
    Route::post("/election/create",[ElectionController::class,"create"]);
    });

});
