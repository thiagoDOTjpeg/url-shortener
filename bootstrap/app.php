<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies('*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Calma lá! Você está indo rápido demais. Tente novamente em alguns minutos.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null
                ], 429);
            }

            return response()->view('errors.429', [
                'message' => 'Muitas requisições. Respire fundo e aguarde.'
            ], 429);
        });
    })->create();
