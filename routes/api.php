<?php

use App\Http\Controllers\api\ArticleController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CategorieArticleController;
use App\Http\Controllers\api\CommentaireController;
use App\Http\Controllers\api\PayementController;
use App\Http\Controllers\api\PublicationController;
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

    Route::middleware('marchand.mdlw')->group(function () {
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

        ########### LISTE ARTICLE UTILISATEUR
        Route::get('/user/article', [ArticleController::class, 'userArticles']);

        ########### SOLDE
        Route::get('/solde/{deivse?}', [PayementController::class, 'solde']);
        Route::post('/appro', [PayementController::class, 'appro']);
    });
});
