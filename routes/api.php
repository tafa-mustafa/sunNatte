<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\TarifController;
use App\Http\Controllers\Api\V1\ContributionController;
use App\Http\Controllers\Api\V1\{LoginController, RegisterController, UserController,ForgotPasswordController, TontineController,TirelireController};

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
Route::get('admin/test', [AdminController::class, 'test']);
    Route::get('payment/success', [ContributionController::class, 'success'])->name('payment.success');
    Route::get('payment/error', [ContributionController::class, 'error'])->name('payment.error');



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
Route::post('/users/add', [UserController::class, 'store']);



Route::get('admin/tarifs', [TarifController::class, 'index']);
    Route::post('admin/tarifs', [TarifController::class, 'store']);
    Route::get('admin/tarifs/{tarif}', [TarifController::class, 'show']);
    Route::put('admin/tarifs/{tarif}', [TarifController::class, 'update']);
    Route::delete('admin/tarifs/{tarif}', [TarifController::class, 'destroy']);

/********************************TONTINE *********************/

Route::post('tontine/create', [TontineController::class, 'store']);
Route::post('tontine/adhesion', [TontineController::class, 'adhesion_tontine']);
Route::get('tontine/{tontine}/detail', [TontineController::class, 'show']);
Route::get('tontine/{tontine}/tirage', [TontineController::class, 'tirage']);
Route::get('tontine/listeVersements', [TontineController::class, 'listeVersements']);
Route::get('tontine/list', [TontineController::class, 'index']);
Route::get('tontine/avenir', [TontineController::class, 'list_avenir']);

Route::get('/tontines/filter', [TontineController::class, 'filterByType']);
Route::patch('/tontines/{tontine}/tirages/{tirage}/complete', [TontineController::class, 'completeTirage']);
Route::post('/tontines/{tontine}/tirages', [TontineController::class, 'programTirage']);
Route::post('/tontines/{tontine}/add-participant', [TontineController::class, 'addParticipant']);
/************************MATERIEL ***************/
Route::post('tontine/materiel/post', [DocumentController::class,'materiel']);
Route::get('tontine/materiel/{materiel}/get', [TontineController::class,'show_mt']);
Route::get('tontine/materiel/get', [DocumentController::class,'all']);
Route::post('users/document', [DocumentController::class,'doc_user']);


/****************Contribution ***********************************/

    Route::post('tontines/{tontine}/contribute', [ContributionController::class, 'contribute'])->name('tontine.contribute');
    //Route::get('tontines/{tontine}/mycontributes', [ContributionController::class, 'getContributionsByTontine']);

        Route::post('/wave/token', [ContributionController::class, 'getToken']);

        Route::post('contribution/initiate/{tontine}', [ContributionController::class, 'initiateContribution'])->name('contribution.initiate');
Route::post('contribution/confirm/{tontine}', [ContributionController::class, 'confirmPayment'])->name('contribution.confirm');

Route::get('contribution/{id}', [ContributionController::class, 'getContributionDetail'])->name('ContributionDetail.get');
Route::get('/tontines/{tontine}/mycontributes', [ContributionController::class, 'getContributionsByTontine'])
    ->name('tontines.contributions');


/****************Tirelire  ***********************************/

Route::get('tontine/expired', [TontineController::class, 'tontinesExpired']);
Route::post('/tirelire/{tirelire}/add', [TirelireController::class, 'addMoneyToTirelire']);
Route::post('/tirelire/payment/confirmed/{tirelire}', [TirelireController::class, 'paymentSuccess'])->name('payment_tirelire.success');
Route::post('/tirelire/create', [TirelireController::class, 'createTirelire']);
Route::get('/tirelires/my-tirelires', [TirelireController::class, 'myTirelires']);
Route::get('/tirelires/{id}', [TirelireController::class, 'showTirelire']);
Route::post('/tirelires/{tirelire}/retrait', [TirelireController::class, 'retirait']);
Route::post('/tirelires/{tirelire}/retraits', [TirelireController::class, 'retrait_any']);




    /*************************Admin Route *****************/

Route::post('admin/tontines/create', [AdminController::class,'store_tontine']);
Route::get('admin/tontines/all', [AdminController::class,'list_tontine']);
Route::get('admin/tontines/show/{tontine}', [AdminController::class,'show_tontine']);
Route::get('admin/tontines/open/{tontine}', [AdminController::class,'active_tontine']);
Route::post('admin/tontines/update/{id}', [AdminController::class,'update_tontine']);
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

    Route::post('admin/users', [AdminController::class, 'store']);                 // Créer un utilisateur
    Route::get('admin/users', [AdminController::class, 'list_users']);             // Lister les utilisateurs
    Route::get('admin/users/{user}', [AdminController::class, 'show_user']);       // Voir un utilisateur
    Route::put('admin/users/{user}', [AdminController::class, 'update_user']);     // Modifier un utilisateur
    Route::delete('admin/users/{user}', [AdminController::class, 'delete_user']);  // Supprimer un utilisateur
    Route::patch('admin/users/{id}/activate', [AdminController::class, 'active_user']);    // Activer un utilisateur
    Route::patch('admin/users/{id}/deactivate', [AdminController::class, 'desactive_user']); // Désactiver un utilisateur
    Route::get('admin/stats', [AdminController::class, 'statat']);                 // Créer un utilisateur
    Route::get('admin/document/{id}', [AdminController::class, ' active_document_status']);
      Route::get('admin/tontine/{id}', [AdminController::class, ' active_tontine_status']);                 // Créer un utilisateur


/**********************NOTIFICATION */
    Route::get('notifications', [UserController::class, 'list']);
    Route::get('notifications/mark-as-read', [UserController::class, 'markasRead']);
    Route::delete('notifications/{id}', [UserController::class, 'delete']);
    Route::post('notifications/read/{id}', [UserController::class, 'read']);
});
