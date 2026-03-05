<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\ActualiteController as PublicActualiteController;
use App\Http\Controllers\Public\ActiviteController as PublicActiviteController;
use App\Http\Controllers\Public\InscriptionController as PublicInscriptionController;
use App\Http\Controllers\Public\CalendrierController as PublicCalendrierController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ParametreController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ActualiteController;
use App\Http\Controllers\Admin\ActiviteController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\InscriptionController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\CalendrierController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Routes Publiques (sans authentification)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Actualités publiques
Route::get('/actualites', [PublicActualiteController::class, 'index']);
Route::get('/actualites/{id}', [PublicActualiteController::class, 'show']);

// Activités publiques
Route::get('/activites', [PublicActiviteController::class, 'index']);
Route::get('/activites/{slug}', [PublicActiviteController::class, 'show']);

// Inscriptions (formulaire public)
Route::post('/inscriptions', [PublicInscriptionController::class, 'store']);

// Calendrier scolaire public
Route::get('/calendrier', [PublicCalendrierController::class, 'index']);

// Formulaire de contact
Route::post('/contact', [ContactController::class, 'store']);

// Paramètres publics (mot du directeur, horaires, etc.)
Route::get('/parametres', [ParametreController::class, 'index']);
Route::get('/parametres/{cle}', [ParametreController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Routes Admin (authentification requise)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // === ACTUALITÉS ===
    Route::apiResource('actualites', ActualiteController::class);
    Route::patch('/actualites/{actualite}/statut', [ActualiteController::class, 'updateStatut']);

    // === ACTIVITÉS ===
    Route::apiResource('activites', ActiviteController::class);

    // === MÉDIAS ===
    Route::get('/medias', [MediaController::class, 'index']);
    Route::post('/medias', [MediaController::class, 'store']);
    Route::delete('/medias/{media}', [MediaController::class, 'destroy']);

    // === INSCRIPTIONS ===
    Route::get('/inscriptions', [InscriptionController::class, 'index']);
    Route::get('/inscriptions/{inscription}', [InscriptionController::class, 'show']);
    Route::patch('/inscriptions/{inscription}/statut', [InscriptionController::class, 'updateStatut']);
    Route::get('/inscriptions/{inscription}/pdf', [InscriptionController::class, 'exportPdf']);
    Route::get('/inscriptions/export/excel', [InscriptionController::class, 'exportExcel']);

    // === MESSAGES DE CONTACT ===
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::patch('/messages/{message}/lu', [MessageController::class, 'marquerLu']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    // === CALENDRIER ===
    Route::apiResource('calendrier', CalendrierController::class);

    // === UTILISATEURS & RÔLES (Super Admin uniquement) ===
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });

    // === PARAMÈTRES ===
    Route::get('/parametres', [ParametreController::class, 'adminIndex']);
    Route::put('/parametres/{cle}', [ParametreController::class, 'update']);
    
    // ROUTE SPÉCIALE POUR LA PHOTO DU DIRECTEUR
    Route::post('/parametres/photo-directeur', [ParametreController::class, 'uploadPhotoDirecteur']);
    // Route alternative si votre frontend utilise le pattern existant
    Route::post('/parametres/photo_directeur', [ParametreController::class, 'uploadPhotoDirecteur']);
});
