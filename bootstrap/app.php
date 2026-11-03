<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->route()?->getActionName() !== LoginController::class.'@login'
                || (! $exception instanceof Error && ! $exception instanceof ErrorException && ! $exception instanceof QueryException)) {
                return null;
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $message = 'The account service returned incomplete account data. Please try again.';

            return $request->expectsJson()
                ? response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 422)
                : back()->withErrors(['email' => $message])->withInput($request->only('email'));
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
