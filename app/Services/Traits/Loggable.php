<?php

declare(strict_types=1);

namespace App\Services\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    /**
     * Log the start of an operation.
     */
    protected function logStart(string $operation, array $context = []): void
    {
        Log::channel('app')->info("Starting: {$operation}", $context);
    }

    /**
     * Log the successful completion of an operation.
     */
    protected function logSuccess(string $operation, array $context = []): void
    {
        Log::channel('app')->info("Success: {$operation}", $context);
    }

    /**
     * Log the failure of an operation.
     */
    protected function logFailure(string $operation, \Throwable $exception, array $context = []): void
    {
        Log::channel('app')->error("Failed: {$operation}", array_merge($context, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]));
    }
}
