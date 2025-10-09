<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


// Authentification PUblic
Route::group([

    'namespace' => 'App\Http\Controllers\Auth',
    'middleware' => 'guest:api'

], function() {

    Route::post('/register', RegisterController::class);
    Route::get('email/verify/{user}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    Route::post('/login', [LoginController::class, 'login']);

    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset');

});


// Privé
Route::group([

    'middleware' => 'auth:api',

], function($router) {

    Route::get('/user', [LoginController::class, 'me']);

    Route::apiResource('/project', ProjectController::class);
    Route::post('/project/search', [ProjectController::class, 'search']);
    Route::apiResource('project.task', TaskController::class);

    Route::post('/logout', [LoginController::class, 'logout']);

});


