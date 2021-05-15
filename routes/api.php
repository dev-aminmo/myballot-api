<?php

use App\Http\Controllers\ListsElection\ListsElectionController;
use App\Http\Controllers\Poll\PollController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\PartyController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\PluralityElection\PluralityElectionController;
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
    Route::post("/plurality-election/create",[PluralityElectionController::class,"create"]);
    Route::post("/plurality-election/party/add",[PluralityElectionController::class,"add_party"]);



    Route::post("/lists-election/create",[ListsElectionController::class,"create"]);
    Route::post("/party/create",[PartyController::class,"create"]);
    Route::post("/candidate/create",[CandidateController::class,"create"]);
    Route::post("/poll/create",[PollController::class,"create"]);
    });
});
