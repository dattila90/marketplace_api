<?php

namespace App\Exceptions\Product;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Product Search Exception
 * 
 * Thrown when product search operations fail due to service issues,
 * invalid criteria, or external service unavailability.
 */
class ProductSearchException extends Exception
{
    protected $code = Response::HTTP_SERVICE_UNAVAILABLE;
    private array $searchCriteria;
    private ?string $serviceError;

    public function __construct(
        string $message = 'Product search service unavailable',
        array $searchCriteria = [],
        ?string $serviceError = null,
        ?\Throwable $previous = null
    ) {
        $this->searchCriteria = $searchCriteria;
        $this->serviceError = $serviceError;

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
                'type' => 'SEARCH_SERVICE_ERROR',
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
                'details' => $this->getErrorDetails(),
            ],
            'timestamp' => now()->toISOString(),
        ], $this->getCode());
    }

    /**
     * Get detailed error information
     */
    private function getErrorDetails(): array
    {
        $details = [
            'service' => 'product_search',
            'retry_after' => 30, // seconds
        ];

        if (config('app.debug') && $this->serviceError) {
            $details['service_error'] = $this->serviceError;
            $details['search_criteria'] = $this->searchCriteria;
        }

        return $details;
    }

    /**
     * Get context for logging
     */
    public function context(): array
    {
        return [
            'exception' => static::class,
            'search_criteria' => $this->searchCriteria,
            'service_error' => $this->serviceError,
            'trace_id' => request()->header('X-Trace-ID'),
        ];
    }

    /**
     * Get search criteria that caused the error
     */
    public function getSearchCriteria(): array
    {
        return $this->searchCriteria;
    }

    /**
     * Get the underlying service error
     */
    public function getServiceError(): ?string
    {
        return $this->serviceError;
    }
}
