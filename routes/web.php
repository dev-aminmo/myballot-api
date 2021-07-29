<?php

use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

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
return response()->json([
    'message' => 'User does not have any of the necessary access rights.',
    'code' => 403
],403);
   // return view('welcome');
})->name("/");
Route::group([
    'middleware' => 'web'
], function () {

    Route::post('password/reset', [PasswordResetController::class,"reset"])->name("resetpassword");
    Route::get('password/find/{token}', [PasswordResetController::class,'find'])->name('find');
});
