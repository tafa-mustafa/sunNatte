<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{LoginController, RegisterController, UserController,ForgotPasswordController};

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

Route::group(['middleware' => 'auth:sanctum'], function (){

Route::post('/users/logout', [UserController::class, 'logout']);
Route::post('/users/update/password', [UserController::class, 'update_pass']);
Route::post('/users/update/profile/{user}', [UserController::class, 'update']);
Route::get('/users/profile', [UserController::class, 'moi']);
Route::post('/users/send-otp', [ForgotPasswordController::class, 'sendResetCode']);
Route::post('/users/reset-password', [ForgotPasswordController::class, 'resetPassword']);



});