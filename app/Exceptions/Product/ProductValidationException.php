<?php

namespace App\Exceptions\Product;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Product Validation Exception
 * 
 * Thrown when product data fails validation rules.
 * Provides detailed validation error information.
 */
class ProductValidationException extends Exception
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;
    private array $validationErrors;
    private string $field;

    public function __construct(
        array $validationErrors,
        string $field = '',
        string $message = 'Product validation failed',
        ?\Throwable $previous = null
    ) {
        $this->validationErrors = $validationErrors;
        $this->field = $field;

        parent::__construct($message, $this->code, $previous);
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'type' => 'VALIDATION_ERROR',
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
                'validation_errors' => $this->validationErrors,
            ],
            'timestamp' => now()->toISOString(),
        ], $this->getCode());
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Get the field that failed validation
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get context for logging
     */
    public function context(): array
    {
        return [
            'exception' => static::class,
            'validation_errors' => $this->validationErrors,
            'field' => $this->field,
            'trace_id' => request()->header('X-Trace-ID'),
        ];
    }
}
