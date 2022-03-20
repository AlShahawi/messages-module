<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile'])->name('v1.profile');

        Route::get('conversations', [ConversationsController::class, 'index'])
            ->name('v1.conversations.index');
    });

    Route::post('login', [AuthController::class, 'login'])->name('v1.login');
});
