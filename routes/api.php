<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\ContributionController;
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

Route::post('admin/login', [AdminController::class, 'login_user']);
Route::get('tontine/contribution/error', [ContributionController::class, 'success'])->name('payment.success');
Route::get('tontine/contribution/success', [ContributionController::class, 'error'])->name('payment.error');

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
Route::post('tontine/adhesion', [TontineController::class, 'adhesion_tontine']);
Route::get('tontine/{tontine}/detail', [TontineController::class, 'show']);
Route::get('tontine/{tontine}/tirage', [TontineController::class, 'tirage']);
Route::get('tontine/list', [TontineController::class, 'index']);
Route::get('tontine/avenir', [TontineController::class, 'list_avenir']);
Route::get('tontine/expired', [TontineController::class, 'tontinesExpired']);


/************************MATERIEL ***************/
Route::post('tontine/materiel/post', [DocumentController::class,'materiel']);
Route::get('tontine/materiel/{materiel}/get', [TontineController::class,'show_mt']);
Route::get('tontine/materiel/get', [DocumentController::class,'all']);
Route::post('users/document', [DocumentController::class,'doc_user']);


/****************Contribution ***********************************/

    Route::post('tontine/contribution/{tontine}', [ContributionController::class, 'contribute']);
    

    /*************************Admin Route *****************/

Route::post('admin/tontines/create', [AdminController::class,'store_tontine']);
Route::get('admin/tontines/all', [AdminController::class,'list_tontine']);
Route::get('admin/tontines/show/{tontine}', [AdminController::class,'show_tontine']);
Route::get('admin/tontines/open/{tontine}', [AdminController::class,'active_tontine']);
Route::post('admin/tontines/update/{tontine}', [AdminController::class,'update_tontine']);
Route::get('admin/tontines/close/{tontine}', [AdminController::class,'desactive_tontine']);
Route::get('admin/users/all', [AdminController::class,'list_users']);
Route::get('admin/users/show/{user}', [AdminController::class,'show_user']);
Route::get('admin/users/active/{id}', [AdminController::class,'active_user']);
Route::get('admin/users/documents/{user}', [AdminController::class,'list_documents']);
Route::get('admin/users/desactive/{id}', [AdminController::class,'desactive_user']);
Route::post('admin/users/update/{user}', [AdminController::class,'update_user']);
Route::delete('admin/users/delete/{user}', [AdminController::class,'delete_user']);
Route::post('admin/tontine/adhesion/{tontine}', [UserController::class,'demande']);
Route::post('admin/tontine/adhere_user/{tontine}', [AdminController::class,'adherer_user']);


/**********************NOTIFICATION */
    Route::get('notifications', [UserController::class, 'list']);
    Route::get('notifications/mark-as-read', [UserController::class, 'markasRead']);
    Route::delete('notifications/{id}', [UserController::class, 'delete']);
    Route::post('notifications/read/{id}', [UserController::class, 'read']);
});