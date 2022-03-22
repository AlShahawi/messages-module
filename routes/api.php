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

        Route::post('conversations', [ConversationsController::class, 'new'])
            ->name('v1.conversations.new');

        Route::get('conversations/search', [ConversationsController::class, 'search'])
            ->name('v1.conversations.search');

        Route::get('conversations/{conversation}/messages', [ConversationsController::class, 'messages'])
            ->name('v1.conversations.messages.index');

        Route::post('conversations/{conversation}/messages', [ConversationsController::class, 'send'])
            ->name('v1.conversations.messages.send');

        Route::post('conversations/{conversation}/messages/{message}/markAsRead', [ConversationsController::class, 'markAsRead'])
            ->name('v1.conversations.messages.markAsRead');
    });

    Route::post('login', [AuthController::class, 'login'])->name('v1.login');
});
