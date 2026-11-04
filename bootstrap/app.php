<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tunnel atau reverse proxy di mesin yang sama: ikuti skema HTTPS dari header-nya.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Halaman error aplikasi. Permintaan JSON (scan, polling) tetap menerima JSON.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (! in_array($status, [401, 403, 404, 419, 429, 500, 503], true) || $request->expectsJson()) {
                return $response;
            }

            // Saat debug aktif, 500 tetap menampilkan detail untuk pengembang.
            if ($status >= 500 && $status !== 503 && config('app.debug')) {
                return $response;
            }

            // Hanya pesan 403 yang ditulis sendiri oleh aplikasi; pesan lain bisa membocorkan detail teknis.
            $message = $status === 403 && $exception->getMessage() !== 'This action is unauthorized.'
                ? $exception->getMessage()
                : null;

            return Inertia::render('errors/index', [
                'status' => $status,
                'message' => $message,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
