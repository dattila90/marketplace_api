<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register API middleware
        $middleware->api(prepend: [
            \App\Http\Middleware\ApiErrorHandler::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'api.rate_limit' => \App\Http\Middleware\ApiRateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle custom product exceptions
        $exceptions->render(function (\App\Exceptions\Product\ProductNotFoundException $e) {
            return $e->render();
        });

        $exceptions->render(function (\App\Exceptions\Product\ProductSearchException $e) {
            return $e->render();
        });

        $exceptions->render(function (\App\Exceptions\Product\ProductValidationException $e) {
            return $e->render();
        });

        // Handle Elasticsearch exceptions
        $exceptions->render(function (\App\Exceptions\Elasticsearch\ElasticsearchConnectionException $e) {
            return $e->render();
        });

        $exceptions->render(function (\App\Exceptions\Elasticsearch\ElasticsearchQueryException $e) {
            return $e->render();
        });

        // Log context for all exceptions
        $exceptions->context(function () {
            return [
                'trace_id' => request()->header('X-Trace-ID', 'unknown'),
                'user_id' => request()->user()?->id ?? 'guest',
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ];
        });
    })->create();
