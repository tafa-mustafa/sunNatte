<?php

use App\Http\Controllers\Admin\BackofficeAuthController;
use App\Http\Controllers\Admin\BackofficeCatalogController;
use App\Http\Controllers\Admin\BackofficeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WellKnownController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/docs', [\App\Http\Controllers\OpenApiController::class, '__invoke']);
Route::get('/docs/{file}', [\App\Http\Controllers\OpenApiController::class, '__invoke'])
    ->where('file', 'api-docs\\.json|api-docs\\.yaml');
Route::get('/openapi.json', [\App\Http\Controllers\OpenApiController::class, '__invoke']);
Route::get('/a-propos', [PageController::class, 'aPropos'])->name('a-propos');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/mentions-legales', [PageController::class, 'mentionsLegales'])->name('mentions-legales');
Route::get('/join/{code}', [PageController::class, 'join'])->name('join');
Route::get('/.well-known/assetlinks.json', [WellKnownController::class, 'android']);
Route::get('/.well-known/apple-app-site-association', [WellKnownController::class, 'apple']);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [BackofficeAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [BackofficeAuthController::class, 'login'])->name('login.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [BackofficeAuthController::class, 'logout'])->name('logout');
        Route::get('/', [BackofficeController::class, 'dashboard'])->name('dashboard');
        Route::get('adhesions', [BackofficeController::class, 'adhesions'])->name('adhesions');
        Route::post('adhesions/{adhesion}/review', [BackofficeController::class, 'review'])->name('adhesions.review');
        Route::get('tontines', [BackofficeCatalogController::class, 'tontines'])->name('tontines');
        Route::post('tontines', [BackofficeCatalogController::class, 'storeTontine'])->name('tontines.store');
        Route::get('tontines/{tontine}', [BackofficeCatalogController::class, 'showTontine'])->name('tontines.show');
        Route::post('tontines/{tontine}', [BackofficeCatalogController::class, 'updateTontine'])->name('tontines.update');
        Route::post('tontines/{tontine}/toggle', [BackofficeCatalogController::class, 'toggleTontine'])->name('tontines.toggle');
        Route::post('tontines/{tontine}/adhere', [BackofficeCatalogController::class, 'adhere'])->name('tontines.adhere');
        Route::get('users', [BackofficeCatalogController::class, 'users'])->name('users');
        Route::get('users/{user}', [BackofficeCatalogController::class, 'showUser'])->name('users.show');
        Route::post('users/{user}', [BackofficeCatalogController::class, 'updateUser'])->name('users.update');
        Route::post('users/{user}/toggle', [BackofficeCatalogController::class, 'toggleUser'])->name('users.toggle');
        Route::get('mutuelles', [BackofficeCatalogController::class, 'mutuelles'])->name('mutuelles');
        Route::get('tarifs', [BackofficeCatalogController::class, 'tarifs'])->name('tarifs');
        Route::post('tarifs', [BackofficeCatalogController::class, 'storeTarif'])->name('tarifs.store');
        Route::delete('tarifs/{tarif}', [BackofficeCatalogController::class, 'destroyTarif'])->name('tarifs.destroy');
    });
});
