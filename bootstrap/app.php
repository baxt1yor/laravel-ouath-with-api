<?php

use App\DTO\AppResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: base_path('routes/api.php'),
        commands: base_path('routes/console.php'),
        health: '/up',
        then: function () {
            Route::get('/', fn() => AppResponse::success(['message' => 'App successfully running!']));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api([
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
