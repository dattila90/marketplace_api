<?php

namespace App\Exceptions\Product;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Product Not Found Exception
 * 
 * Thrown when a requested product cannot be found in the system.
 * Provides consistent error response for missing products.
 */
class ProductNotFoundException extends Exception
{
    protected $message = 'Product not found';
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct(?string $productId = null, ?\Throwable $previous = null)
    {
        $message = $productId
            ? "Product with ID '{$productId}' not found"
            : $this->message;

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
                'type' => 'PRODUCT_NOT_FOUND',
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ],
            'timestamp' => now()->toISOString(),
        ], $this->getCode());
    }

    /**
     * Get additional context for logging
     */
    public function context(): array
    {
        return [
            'exception' => static::class,
            'message' => $this->getMessage(),
            'trace_id' => request()->header('X-Trace-ID'),
        ];
    }
}
