<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorizationController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('resend', [AuthController::class, 'resend'])->name('resend');
Route::post('verify', [AuthController::class, 'verify'])->name('verify');

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::group([
    'as' => 'passport.',
    'prefix' => config('passport.path', 'oauth'),
    'namespace' => '\Laravel\Passport\Http\Controllers',
], function () {

    Route::get('/authorize', [AuthorizationController::class, 'authorize'])->name('authorizations.authorize');
    Route::post('/token', [AuthorizationController::class, 'issueToken'])->middleware('throttle')->name('token');

    Route::middleware(['auth:api'])->group(function () {
        Route::post('/token/refresh', [
            'uses' => 'TransientTokenController@refresh',
            'as' => 'token.refresh',
        ]);

        Route::post('/authorize', [AuthorizationController::class, 'approve'])->name('authorizations.approve');

        Route::delete('/authorize', [
            'uses' => 'DenyAuthorizationController@deny',
            'as' => 'authorizations.deny',
        ]);

        Route::get('/tokens', [
            'uses' => 'AuthorizedAccessTokenController@forUser',
            'as' => 'tokens.index',
        ]);

        Route::delete('/tokens/{token_id}', [
            'uses' => 'AuthorizedAccessTokenController@destroy',
            'as' => 'tokens.destroy',
        ]);

        Route::get('/clients', [
            'uses' => 'ClientController@forUser',
            'as' => 'clients.index',
        ]);

        Route::post('/clients', [
            'uses' => 'ClientController@store',
            'as' => 'clients.store',
        ]);

        Route::put('/clients/{client_id}', [
            'uses' => 'ClientController@update',
            'as' => 'clients.update',
        ]);

        Route::delete('/clients/{client_id}', [
            'uses' => 'ClientController@destroy',
            'as' => 'clients.destroy',
        ]);

        Route::get('/scopes', [
            'uses' => 'ScopeController@all',
            'as' => 'scopes.index',
        ]);

        Route::get('/personal-access-tokens', [
            'uses' => 'PersonalAccessTokenController@forUser',
            'as' => 'personal.tokens.index',
        ]);

        Route::post('/personal-access-tokens', [
            'uses' => 'PersonalAccessTokenController@store',
            'as' => 'personal.tokens.store',
        ]);

        Route::delete('/personal-access-tokens/{token_id}', [
            'uses' => 'PersonalAccessTokenController@destroy',
            'as' => 'personal.tokens.destroy',
        ]);
    });
});
