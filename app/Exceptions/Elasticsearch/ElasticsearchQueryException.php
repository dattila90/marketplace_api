<?php

namespace App\Exceptions\Elasticsearch;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Elasticsearch Query Exception
 * 
 * Thrown when Elasticsearch queries are malformed or fail to execute.
 * Provides query debugging information and suggestions.
 */
class ElasticsearchQueryException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    private array $query;
    private ?string $elasticsearchError;

    public function __construct(
        array $query = [],
        ?string $elasticsearchError = null,
        string $message = 'Invalid search query',
        ?\Throwable $previous = null
    ) {
        $this->query = $query;
        $this->elasticsearchError = $elasticsearchError;

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
                'type' => 'INVALID_SEARCH_QUERY',
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
            'suggestions' => [
                'Check search parameters format',
                'Verify price ranges are numeric',
                'Ensure category IDs are valid',
                'Review sort field options',
            ],
        ];

        if (config('app.debug')) {
            $details['query_debug'] = $this->query;
            if ($this->elasticsearchError) {
                $details['elasticsearch_error'] = $this->elasticsearchError;
            }
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
            'query' => $this->query,
            'elasticsearch_error' => $this->elasticsearchError,
            'trace_id' => request()->header('X-Trace-ID'),
        ];
    }

    /**
     * Get the problematic query
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Get the Elasticsearch error message
     */
    public function getElasticsearchError(): ?string
    {
        return $this->elasticsearchError;
    }
}
