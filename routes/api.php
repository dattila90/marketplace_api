<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Product API Routes
Route::prefix('v1')->group(function () {

    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('search', [ProductController::class, 'search']);
        Route::get('featured', [ProductController::class, 'featured']);
        Route::get('{id}', [ProductController::class, 'product']);
    });
});
