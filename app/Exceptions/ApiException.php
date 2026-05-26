<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    protected array $errors;

    public function __construct(string $message, int $statusCode = 400, array $errors = [], ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($statusCode, $message, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
