<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias custom — dipakai di routes/web.php dan routes/api.php sebagai `role:...`
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Sanctum stateful: dibutuhkan kalau SPA/Blade frontend mau pakai cookie auth.
        // Untuk Postman / mobile (token Bearer murni), tidak wajib tapi aman diaktifkan
        // supaya kedua mode jalan.
        $middleware->statefulApi();

        // Pastikan request ke /api/* mengembalikan JSON 401 saat unauthenticated,
        // bukan redirect HTML ke /login (default Laravel). Tanpa ini, client API
        // bisa kebingungan kenapa dapat HTML.
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            return route('login');
        });

        $middleware->web(append: [
            \Spatie\Csp\AddCspHeaders::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();