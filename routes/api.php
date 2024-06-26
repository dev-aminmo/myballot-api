<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Poll\AnswerController;
use App\Http\Controllers\BallotController;
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
Route::group([
    'middleware' => 'api',
    'prefix' => 'password'
], function () {
    Route::post('create', [PasswordResetController::class,"create"]);
});
Route::middleware('auth:api')->group(function (){

        /*
         * users routes
         */

    Route::get("/ballot/all",[BallotController::class, "ballots"]);
    Route::get("/ballot/{id}",[BallotController::class, "ballot"]);
    Route::get("/ballot/results/{id}",[BallotController::class, "results"]);

    Route::get("email/resend",[VerificationController::class,"resend"])->name("verification.resend");

    Route::group(['prefix'=>'/user'],function(){
        Route::get("",[UserController::class,"index"]);
        Route::post("/avatar/update",[UserController::class,"updateAvatar"]);
        Route::post("/profile/update",[UserController::class,"updateProfile"]);
        Route::post("/password/update",[UserController::class,"updatePassword"]);
        Route::post("/logout",[UserController::class,"logout"]);
    });//end of users routes
// TODO need to be grouped
    Route::get("/plurality-election/candidates/{id}",[CandidateController::class,"plurality_candidates"]);
    Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);
    Route::get("/lists-election/results/{id}",[ListsElectionController::class,"results"]);
    Route::get("/poll/results/{id}",[PollController::class,"results"]);

    Route::get("/lists-election/lists/{id}",[ListsElectionController::class,"lists"]);
    Route::get("/poll/all/{id}",[PollController::class,"get"]);


    /*
     * Organizer's routes
     */
    Route::middleware('organizer')->group(function (){
    /*
    * elections routes
    */
        Route::delete("/ballot/{id}",[BallotController::class,"delete"]);
        Route::post("/plurality-election/create",[PluralityElectionController::class,"create"]);
    Route::post("/lists-election/create",[ListsElectionController::class,"create"]);
    Route::post("/lists-election/add",[ListsElectionController::class,"add"]);
    Route::delete("/lists-election/delete/{id}",[ListsElectionController::class,"delete"]);
        Route::post("/lists-election/list/update",[ListsElectionController::class,"update"]);

        Route::post("/poll/create",[PollController::class,"create"]);
        Route::post("/question/add",[QuestionController::class,"add"]);
        Route::post("/question/update",[QuestionController::class,"update"]);
        Route::delete("/question/delete/{id}",[QuestionController::class,"delete"]);
        Route::post("/answer/add",[AnswerController::class,"add"]);
        Route::post("/answer/update",[AnswerController::class,"update"]);
        Route::delete("/answer/delete/{id}",[AnswerController::class,"delete"]);

        Route::post("/ballot/update",[BallotController::class,"update"]);
   // Route::get("/plurality-election/results/{id}",[PluralityElectionController::class,"results"]);

    /*
    *  voter managing routes
    */
    Route::post("/voter/add",[BallotController::class,"add_voters"]);
    Route::get("/voters/{id}",[BallotController::class,"get_voters"]);
    Route::post("/voter/delete",[BallotController::class,"delete_voter"]);

    /*
    * candidate routes
    */
    Route::post("/candidate/update",[CandidateController::class,"update"]);
    Route::delete("/candidate/delete/{id}",[CandidateController::class,"delete"]);
    Route::post("/candidate/plurality/add",[CandidateController::class, "add_candidates_to_plurality"]);
    Route::post("/candidate/lists/add",[CandidateController::class, "add_candidates_to_list"]);

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
