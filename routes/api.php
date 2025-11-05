<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Marketplace API routes with error handling and rate limiting middleware.
| All routes are automatically prefixed with '/api' by Laravel.
|
*/

// Product API Routes with Rate Limiting
Route::prefix('v1')->group(function () {

    // Product routes with specific rate limits
    Route::prefix('products')->group(function () {
        // Search endpoint - moderate rate limit due to complex queries
        Route::get('search', [ProductController::class, 'search'])
            ->middleware('api.rate_limit:search')
            ->name('api.products.search');

        // Featured products - higher rate limit for homepage usage
        Route::get('featured', [ProductController::class, 'featured'])
            ->middleware('api.rate_limit:featured')
            ->name('api.products.featured');

        // Single product - highest rate limit for frequent access
        Route::get('{id}', [ProductController::class, 'product'])
            ->middleware('api.rate_limit:product')
            ->name('api.products.show')
            ->where('id', '[0-9a-f\-]{36}'); // UUID pattern
    });
});

// Health check endpoint (no rate limiting)
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'services' => [
            'database' => 'healthy',
            'elasticsearch' => app(\App\Clients\ElasticsearchClient::class)->ping() ? 'healthy' : 'degraded',
            'cache' => 'healthy',
        ],
    ]);
})->name('api.health');

// API documentation endpoint
Route::get('docs', function () {
    return response()->json([
        'name' => 'Marketplace API',
        'version' => '1.0.0',
        'description' => 'RESTful API for marketplace product search and retrieval',
        'endpoints' => [
            'GET /api/v1/products/search' => 'Search products with filters',
            'GET /api/v1/products/featured' => 'Get featured products',
            'GET /api/v1/products/{id}' => 'Get single product by ID',
            'GET /api/health' => 'API health status',
        ],
        'rate_limits' => [
            'search' => '100 requests/minute',
            'featured' => '200 requests/minute',
            'product' => '500 requests/minute',
        ],
        'documentation' => url('/api/docs'),
    ]);
})->name('api.docs');
