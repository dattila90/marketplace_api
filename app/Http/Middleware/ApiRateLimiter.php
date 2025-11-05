<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * API Rate Limiter Middleware
 * 
 * Implements rate limiting for API endpoints to prevent abuse
 * and ensure fair usage of the marketplace API resources.
 */
class ApiRateLimiter
{
    /**
     * Rate limit configurations per endpoint type
     */
    private array $rateLimits = [
        'search' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'featured' => ['requests' => 200, 'window' => 60], // 200 requests per minute
        'product' => ['requests' => 500, 'window' => 60], // 500 requests per minute
        'default' => ['requests' => 60, 'window' => 60], // 60 requests per minute
    ];

    /**
     * Handle an incoming request with rate limiting
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $clientKey = $this->getClientKey($request);
        $rateLimitKey = "rate_limit:{$type}:{$clientKey}";

        $config = $this->rateLimits[$type] ?? $this->rateLimits['default'];
        $maxRequests = $config['requests'];
        $windowSeconds = $config['window'];

        // Get current request count
        $currentRequests = Cache::get($rateLimitKey, 0);

        // Check if limit exceeded
        if ($currentRequests >= $maxRequests) {
            Log::warning('Rate limit exceeded', [
                'client_key' => $clientKey,
                'endpoint_type' => $type,
                'current_requests' => $currentRequests,
                'max_requests' => $maxRequests,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->createRateLimitResponse($maxRequests, $windowSeconds);
        }

        // Increment request count
        if ($currentRequests === 0) {
            // First request in window - set with TTL
            Cache::put($rateLimitKey, 1, $windowSeconds);
        } else {
            // Increment existing counter (preserve TTL)
            Cache::increment($rateLimitKey);
        }

        // Process request
        $response = $next($request);

        // Add rate limit headers to response
        if ($response instanceof JsonResponse) {
            $this->addRateLimitHeaders($response, $currentRequests + 1, $maxRequests, $rateLimitKey);
        }

        return $response;
    }

    /**
     * Generate unique client identifier
     */
    private function getClientKey(Request $request): string
    {
        // Priority order: API key > User ID > IP address
        if ($apiKey = $request->header('X-API-Key')) {
            return 'api_key:' . substr(md5($apiKey), 0, 16);
        }

        if ($userId = $request->user()?->id) {
            return 'user:' . $userId;
        }

        return 'ip:' . $request->ip();
    }

    /**
     * Create rate limit exceeded response
     */
    private function createRateLimitResponse(int $maxRequests, int $windowSeconds): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'type' => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Too many requests. Please slow down.',
                'code' => Response::HTTP_TOO_MANY_REQUESTS,
                'details' => [
                    'max_requests' => $maxRequests,
                    'window_seconds' => $windowSeconds,
                    'retry_after' => $windowSeconds,
                ],
            ],
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_TOO_MANY_REQUESTS)
            ->header('Retry-After', $windowSeconds)
            ->header('X-RateLimit-Limit', $maxRequests)
            ->header('X-RateLimit-Remaining', 0)
            ->header('X-RateLimit-Reset', now()->addSeconds($windowSeconds)->timestamp);
    }

    /**
     * Add rate limit headers to successful responses
     */
    private function addRateLimitHeaders(
        JsonResponse $response,
        int $currentRequests,
        int $maxRequests,
        string $rateLimitKey
    ): void {
        $remaining = max(0, $maxRequests - $currentRequests);
        $resetTime = Cache::get($rateLimitKey . '_reset', now()->addMinute()->timestamp);

        $response->header('X-RateLimit-Limit', $maxRequests);
        $response->header('X-RateLimit-Remaining', $remaining);
        $response->header('X-RateLimit-Reset', $resetTime);
    }

    /**
     * Get rate limit configuration for endpoint type
     */
    public function getRateLimitConfig(string $type): array
    {
        return $this->rateLimits[$type] ?? $this->rateLimits['default'];
    }

    /**
     * Check if client is currently rate limited
     */
    public function isRateLimited(Request $request, string $type = 'default'): bool
    {
        $clientKey = $this->getClientKey($request);
        $rateLimitKey = "rate_limit:{$type}:{$clientKey}";

        $config = $this->rateLimits[$type] ?? $this->rateLimits['default'];
        $currentRequests = Cache::get($rateLimitKey, 0);

        return $currentRequests >= $config['requests'];
    }
}
