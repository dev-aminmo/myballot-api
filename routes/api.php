<?php

use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ListsElection\ListsElectionController;
use App\Http\Controllers\Poll\PollController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
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



/*
* auth routes
*/
Route::post("/register",[UserController::class,"register"]);
Route::post("/login",[UserController::class,"login"]);

Route::get("email/verify/{id}",[VerificationController::class,"verify"])->name("verification.verify");
//Route::get("/login",[UserController::class,"login"])->name('login');

Route::middleware('auth:api')->group(function (){
    /*
     * users routes
     */
    Route::get("/election/all",[ElectionController::class,"elections"]);

    Route::get("email/resend",[VerificationController::class,"resend"])->name("verification.resend");

    Route::group(['prefix'=>'/user'],function(){
        Route::get("",[UserController::class,"index"]);
        Route::post("/avatar/update",[UserController::class,"updateAvatar"]);
        Route::post("/logout",[UserController::class,"logout"]);
        //Route::post("add",[ReviewController::class,"addReview"]);
    });//end of users routes

    Route::get("/plurality-election/candidates/{id}",[CandidateController::class,"plurality_candidates"]);
    Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);

    /*
     * Organizer's routes
     */
    Route::middleware('role:organizer')->group(function (){
    /*
    * elections routes
    */
    Route::post("/plurality-election/create",[PluralityElectionController::class,"create"]);
    Route::post("/lists-election/create",[ListsElectionController::class,"create"]);
    Route::post("/poll/create",[PollController::class,"create"]);
    Route::post("/election/update",[ElectionController::class,"update"]);
   // Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);

    /*
     * party routes
     */
    Route::post("/party/plurality/add",[PartyController::class,"add_to_plurality"]);
    Route::post("/party/update",[PartyController::class,"update"]);
    Route::delete("/party/delete",[PartyController::class,"delete"]);

    /*
    *  voter managing routes
    */
    Route::post("/election/voter/add",[ElectionController::class,"add_voters"]);
    Route::get("/election/voter/get",[ElectionController::class,"get_voters"]);
    Route::delete("/election/voter/delete",[ElectionController::class,"delete_voter"]);

    /*
    * candidate routes
    */
    Route::post("/candidate/update",[CandidateController::class,"update"]);
  //  Route::post("/candidate/create",[CandidateController::class,"create"]);

    }); //end of organizer's routes

    Route::middleware('role:voter')->group(function (){
    /*
    *  voter routes
    */
    Route::post("/plurality-election/vote",[PluralityElectionController::class,"vote"]);
    });//end of voter's routes
});
