<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Every API response (including errors) is JSON.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Token tidak valid atau tidak disertakan.',
                ], 401);
            }
        });

        // Keep abort(403), 404, 405, 429, ... responses clean and JSON-only,
        // even when APP_DEBUG=true. Missing models become 404 with a generic
        // message instead of exposing a stack trace / SQL detail.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*')) {
                $message = match ($e->getStatusCode()) {
                    404 => 'Data atau endpoint yang diminta tidak ditemukan.',
                    default => $e->getMessage() ?: 'Terjadi kesalahan pada permintaan.',
                };

                return response()->json([
                    'message' => $message,
                ], $e->getStatusCode(), $e->getHeaders());
            }
        });
    })->create();
