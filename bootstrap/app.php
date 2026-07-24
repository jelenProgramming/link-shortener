<?php

use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel builds the AuthenticationException with a redirect target,
        // and resolving route('login') is what actually blew up: there is no
        // such route in a token API. Returning null keeps it a plain 401.
        $middleware->redirectGuestsTo(fn () => null);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // There is no login route to send a guest to: this is a token API with
        // no session. Without this, an unauthenticated request that does not
        // ask for JSON blows up with "Route [login] not defined" instead of
        // answering 401.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => $e->getMessage()], 401);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
