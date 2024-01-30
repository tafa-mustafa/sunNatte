<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\{LoginController, RegisterController, UserController,ForgotPasswordController, TontineController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/users/login', [LoginController::class, 'login']);
Route::post('/users/register', [RegisterController::class, 'register']);
Route::post('/mobile/sendCode', [RegisterController::class, 'mobile']);
Route::post('/mobile/verify', [RegisterController::class, 'verify']);
Route::post('/users/send-otp', [ForgotPasswordController::class, 'sendResetCode']);
Route::post('/users/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::group(['middleware' => 'auth:sanctum'], function (){

Route::post('/users/logout', [UserController::class, 'logout']);
Route::post('/users/update/password', [UserController::class, 'update_pass']);
Route::post('/users/update/profile/{user}', [UserController::class, 'update']);
Route::get('/users/profile', [UserController::class, 'moi']);
Route::get('/users/{user}', [UserController::class, 'show']);

/********************************TONTINE *********************/

Route::post('tontine/create', [TontineController::class, 'store']);
Route::post('tontine/{tontine}/adhesion', [TontineController::class, 'adhesion']);
Route::get('tontine/{tontine}/detail', [TontineController::class, 'show']);
Route::get('tontine/{tontine}/tirage', [TontineController::class, 'tirage']);
Route::get('tontine/list', [TontineController::class, 'index']);


/************************MATERIEL ***************/
Route::post('tontine/materiel/post', [DocumentController::class,'materiel']);
Route::get('tontine/materiel/{materiel}/get', [TontineController::class,'show_mt']);
Route::get('tontine/materiel/get', [DocumentController::class,'all']);
Route::post('users/document', [DocumentController::class,'doc_user']);
});