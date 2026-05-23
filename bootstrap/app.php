<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api_v1.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\ApiVersionMiddleware::class,
            \App\Http\Middleware\RequestLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface 
                    ? $e->getStatusCode() 
                    : ($e instanceof \Illuminate\Validation\ValidationException ? 422 : 500);

                $errors = $e instanceof \Illuminate\Validation\ValidationException 
                    ? $e->errors() 
                    : new \stdClass();

                // Mask message in production if it's a 500
                $message = $e->getMessage();
                if ($status === 500 && app()->isProduction()) {
                    $message = 'Server Error';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message ?: 'An error occurred.',
                    'errors' => $errors,
                ], $status);
            }
        });
    })->create();
