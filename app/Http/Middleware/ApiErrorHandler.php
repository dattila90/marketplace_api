<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * API Error Handler Middleware
 * 
 * Provides a safety net for API requests by catching unhandled exceptions
 * and converting them to standardized JSON error responses.
 */
class ApiErrorHandler
{
    /**
     * Handle an incoming request and catch any unhandled exceptions
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Add trace ID for request tracking
            if (! $request->hasHeader('X-Trace-ID')) {
                $request->headers->set('X-Trace-ID', $this->generateTraceId());
            }

            // Add request start time for performance monitoring
            $startTime = microtime(true);
            $request->attributes->set('start_time', $startTime);

            $response = $next($request);

            // Add performance headers to response
            if ($response instanceof JsonResponse) {
                $this->addPerformanceHeaders($response, $request);
            }

            return $response;
        } catch (Throwable $e) {
            // Log the exception with context
            Log::error('Unhandled API exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace_id' => $request->header('X-Trace-ID'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return standardized error response
            return $this->createErrorResponse($e, $request);
        }
    }

    /**
     * Create standardized error response for unhandled exceptions
     */
    private function createErrorResponse(Throwable $e, Request $request): JsonResponse
    {
        // Determine appropriate HTTP status code
        $statusCode = $this->getHttpStatusCode($e);

        // Build error response
        $errorResponse = [
            'success' => false,
            'error' => [
                'type' => 'INTERNAL_ERROR',
                'message' => $this->getPublicMessage($e),
                'code' => $statusCode,
                'trace_id' => $request->header('X-Trace-ID'),
            ],
            'timestamp' => now()->toISOString(),
        ];

        // Add debug information in development
        if (config('app.debug')) {
            $errorResponse['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return response()->json($errorResponse, $statusCode);
    }

    /**
     * Determine HTTP status code from exception
     */
    private function getHttpStatusCode(Throwable $e): int
    {
        // If exception is an HTTP exception, use its status code
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return $e->getStatusCode();
        }

        $code = $e->getCode();

        // Map common exception codes to HTTP status codes
        return match (true) {
            $code >= 400 && $code < 600 => $code,
            $e instanceof \Illuminate\Validation\ValidationException => 422,
            $e instanceof \Illuminate\Auth\AuthenticationException => 401,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
            $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 404,
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
            default => 500,
        };
    }

    /**
     * Get user-friendly error message
     */
    private function getPublicMessage(Throwable $e): string
    {
        // In production, don't expose internal error details
        if (! config('app.debug')) {
            return match (true) {
                $e instanceof \Illuminate\Validation\ValidationException => 'Validation failed',
                $e instanceof \Illuminate\Auth\AuthenticationException => 'Authentication required',
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'Access denied',
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 'Resource not found',
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'Resource not found',
                default => 'An internal error occurred. Please try again later.',
            };
        }

        return $e->getMessage();
    }

    /**
     * Generate unique trace ID for request tracking
     */
    private function generateTraceId(): string
    {
        return 'trace_' . uniqid() . '_' . substr(md5(microtime()), 0, 8);
    }

    /**
     * Add performance headers to response
     */
    private function addPerformanceHeaders(JsonResponse $response, Request $request): void
    {
        $startTime = $request->attributes->get('start_time');
        if ($startTime) {
            $duration = round((microtime(true) - $startTime) * 1000, 2); // ms
            $response->header('X-Response-Time', $duration . 'ms');
        }

        $response->header('X-Trace-ID', $request->header('X-Trace-ID'));
    }
}
