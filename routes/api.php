<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Authentification PUblic
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Privé
Route::group([

    'middleware' => 'auth:api',

], function($router) {

    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('/project', ProjectController::class);
    Route::post('/project/search', [ProjectController::class, 'search']);
    Route::apiResource('project.task', TaskController::class);

    Route::post('/logout', [AuthController::class, 'logout']);

});


