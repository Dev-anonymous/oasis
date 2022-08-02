<?php

use App\Http\Controllers\api\ArticleController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CategorieArticleController;
use App\Http\Controllers\api\CommandeController;
use App\Http\Controllers\api\CommentaireController;
use App\Http\Controllers\api\MessageController;
use App\Http\Controllers\api\PanierController;
use App\Http\Controllers\api\PayementController;
use App\Http\Controllers\api\PublicationController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


#==========   USER AUTH  =======#
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
#######################################################


Route::group(['middleware' => ['auth:sanctum']], function () {

    ############# PUBLICATIONS
    Route::get('/publication', [PublicationController::class, 'index']);
    Route::get('/publication/{id}', [PublicationController::class, 'show']);
    Route::post('/publication', [PublicationController::class, 'store']);
    Route::delete('/publication/{id}', [PublicationController::class, 'destroy']);

    ############# COMMENTAIRE
    Route::post('/commentaire', [CommentaireController::class, 'store']);
    Route::get('/commentaire/{publication_id}', [CommentaireController::class, 'show']);
    Route::delete('/commentaire/{id}', [CommentaireController::class, 'destroy']);

    ############# CATEGORIE ARTICLE
    Route::post('/categorie-article', [CategorieArticleController::class, 'store']);
    Route::get('/categorie-article', [CategorieArticleController::class, 'index']);
    Route::delete('/categorie-article/{id}', [CategorieArticleController::class, 'destroy']);

    ############# ARTICLE
    Route::get('/article', [ArticleController::class, 'index']);
    Route::get('/article/{id}', [ArticleController::class, 'show']);
    Route::post('/article', [ArticleController::class, 'store']);
    Route::post('/article/maj', [ArticleController::class, 'update']);
    Route::delete('/article/{id}', [ArticleController::class, 'destroy']);

    ########### LISTE ARTICLE de l'UTILISATEUR
    Route::get('/user/article', [ArticleController::class, 'userArticles']);

    ########### SOLDE & TRANSFERT
    Route::get('/solde/{devise?}', [PayementController::class, 'solde']); //liste solde user
    Route::post('/appro', [PayementController::class, 'appro']); //approvisionner mon compte
    Route::post('/transfert', [PayementController::class, 'transfert']); //transfert argent vers un compte
    Route::get('/transaction/{limite?}', [PayementController::class, 'transaction']); //liste transaction
    Route::get('/numero-compte', [PayementController::class, 'numero_compte']); //affiche le numero de compte du user

    ############# MESSAGE
    Route::get('/chat', [MessageController::class, 'index']); // liste conversations recentes
    Route::get('/message/{with_uid}', [MessageController::class, 'show']); // liste message avec un user
    Route::post('/message', [MessageController::class, 'store']); //envoyer un message
    Route::delete('/message/{id}', [MessageController::class, 'destroy']); // #######

    #==========   PANIER   =======#
    Route::resource('panier', PanierController::class); // php artisan route:list
    #######################################################

    #==========   Commande   =======#
    Route::get('/commande/{id?}', [CommandeController::class, 'commande']); // liste commandes / detail commande
    Route::post('/commande', [CommandeController::class, 'passerCommande']); // passer la commande
    #######################################################

    #==========   Users   =======#
    Route::get('/users', [UserController::class, 'index']); //liste des utilisateurs
    Route::post('/users', [UserController::class, 'update']); //update
    Route::get('/users/me', [UserController::class, 'me']); //update
});

########### DEVISE & OPERATEUR
Route::get('/devise', [PayementController::class, 'devise']);
Route::get('/operateur', [PayementController::class, 'operateur']);
