<?php

use App\Http\Controllers\Poll\AnswerController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\ListsElection\ListsElectionController;
use App\Http\Controllers\Poll\PollController;
use App\Http\Controllers\Poll\QuestionController;
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
    Route::middleware('voter')->group(function () {
        Route::get("/papa",function(){
            return "Hello paapa test";
        });

    });
        /*
         * users routes
         */

    Route::get("/election/all",[ElectionController::class,"elections"]);

    Route::get("email/resend",[VerificationController::class,"resend"])->name("verification.resend");

    Route::group(['prefix'=>'/user'],function(){
        Route::get("",[UserController::class,"index"]);
        Route::post("/avatar/update",[UserController::class,"updateAvatar"]);
        Route::post("/profile/update",[UserController::class,"updateProfile"]);
        Route::post("/logout",[UserController::class,"logout"]);
        //Route::post("add",[ReviewController::class,"addReview"]);
    });//end of users routes

    Route::get("/plurality-election/candidates/{id}",[CandidateController::class,"plurality_candidates"]);
    Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);
    Route::get("/lists-election/results/{id}",[ListsElectionController::class,"results"]);
    Route::get("/lists-election/lists/{id}",[ListsElectionController::class,"lists"]);
    Route::get("/poll/all/{id}",[PollController::class,"get"]);


    /*
     * Organizer's routes
     */
    Route::middleware('organizer')->group(function (){
    /*
    * elections routes
    */

        Route::post("/plurality-election/create",[PluralityElectionController::class,"create"]);
    Route::post("/lists-election/create",[ListsElectionController::class,"create"]);
        Route::post("/lists-election/list/update",[ListsElectionController::class,"update"]);

        Route::post("/poll/create",[PollController::class,"create"]);
        Route::post("/poll/question/add",[QuestionController::class,"add"]);
        Route::post("/poll/question/update",[QuestionController::class,"update"]);
        Route::post("/poll/question/delete",[QuestionController::class,"delete"]);
        Route::post("/poll/answer/add",[AnswerController::class,"add"]);
        Route::post("/poll/answer/update",[AnswerController::class,"update"]);
        Route::post("/poll/answer/delete",[AnswerController::class,"delete"]);

        Route::post("/election/update",[ElectionController::class,"update"]);
   // Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);

    /*
     * party routes
     */
    Route::post("/party/plurality/add",[PartyController::class,"add_to_plurality"]);
    Route::post("/party/update",[PartyController::class,"update"]);
    Route::post("/party/delete",[PartyController::class,"delete"]);

    /*
    *  voter managing routes
    */
    Route::post("/election/voter/add",[ElectionController::class,"add_voters"]);
    Route::get("/election/voter/get/{id}",[ElectionController::class,"get_voters"]);
    Route::post("/election/voter/delete",[ElectionController::class,"delete_voter"]);

    /*
    * candidate routes
    */
    Route::post("/candidate/update",[CandidateController::class,"update"]);
    Route::post("/candidate/delete",[CandidateController::class,"delete"]);
    Route::post("/candidate/plurality/free/add",[CandidateController::class,"add_free_plurality"]);
    Route::post("/candidate/lists/free/add",[CandidateController::class,"add_free_list"]);

    }); //end of organizer's routes

    Route::middleware('voter')->group(function (){
    /*
    *  voter routes
    */
    Route::post("/plurality-election/vote",[PluralityElectionController::class,"vote"]);
    Route::post("/lists-election/vote",[ListsElectionController::class,"vote"]);
    Route::post("/poll/vote",[PollController::class,"vote"]);

    });//end of voter's routes
});
