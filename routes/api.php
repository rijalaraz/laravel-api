<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Privé
Route::apiResource('/project', ProjectController::class)->middleware('auth:sanctum');
Route::post('/project/search', [ProjectController::class, 'search'])->middleware('auth:sanctum');
Route::apiResource('project.task', TaskController::class)->middleware('auth:sanctum');

// Authentification PUblic
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);