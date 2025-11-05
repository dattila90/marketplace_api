<?php

namespace App\Exceptions\Elasticsearch;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Elasticsearch Connection Exception
 * 
 * Thrown when Elasticsearch service is unreachable or connection fails.
 * Provides fallback recommendations and retry information.
 */
class ElasticsearchConnectionException extends Exception
{
    protected $code = Response::HTTP_SERVICE_UNAVAILABLE;
    private string $host;
    private ?string $lastError;

    public function __construct(
        string $host = 'unknown',
        ?string $lastError = null,
        ?\Throwable $previous = null
    ) {
        $this->host = $host;
        $this->lastError = $lastError;

        $message = "Elasticsearch connection failed to {$host}";
        if ($lastError) {
            $message .= ": {$lastError}";
        }

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
                'type' => 'ELASTICSEARCH_CONNECTION_ERROR',
                'message' => 'Search service temporarily unavailable. Using fallback data source.',
                'code' => $this->getCode(),
                'details' => [
                    'service' => 'elasticsearch',
                    'status' => 'degraded',
                    'fallback_active' => true,
                    'retry_after' => 60,
                ],
            ],
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_OK); // Return 200 since we have fallback
    }

    /**
     * Get context for logging
     */
    public function context(): array
    {
        return [
            'exception' => static::class,
            'elasticsearch_host' => $this->host,
            'last_error' => $this->lastError,
            'fallback_available' => true,
            'trace_id' => request()->header('X-Trace-ID'),
        ];
    }

    /**
     * Get the host that failed
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
