<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Clients\ElasticsearchClient;
use App\QueryBuilders\ElasticsearchQueryBuilder;
use App\Services\ProductService;

/**
 * Product Service Provider
 * 
 * Handles all product-related dependency injection bindings.
 * Separates product domain dependencies from general app services.
 */
class ProductServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ProductRepositoryInterface::class => ProductRepository::class,
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        ElasticsearchClient::class => ElasticsearchClient::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register QueryBuilder (fresh instance for each usage)
        $this->app->bind(ElasticsearchQueryBuilder::class, function (Application $app) {
            return new ElasticsearchQueryBuilder();
        });

        // Register ProductService with dependency injection
        $this->app->bind(ProductService::class, function (Application $app) {
            return new ProductService(
                $app->make(ProductRepositoryInterface::class)
            );
        });

        // Register ProductRepository with dependency injection
        $this->app->bind(ProductRepository::class, function (Application $app) {
            return new ProductRepository(
                $app->make(ElasticsearchClient::class)
            );
        });

        // Register conditional bindings based on environment
        $this->registerConditionalBindings();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model observers, event listeners, etc.
        $this->bootProductObservers();

        // Register custom validation rules
        $this->bootValidationRules();
    }

    /**
     * Register environment-specific bindings
     */
    private function registerConditionalBindings(): void
    {
        // Development: Enable query logging for Elasticsearch
        if ($this->app->environment('local', 'development')) {
            $this->app->extend(ElasticsearchClient::class, function ($client, $app) {
                // Could add development-specific configurations
                return $client;
            });
        }

        // Testing: Could register mock implementations
        if ($this->app->environment('testing')) {
            // Example: Register test doubles
            // $this->app->bind(ElasticsearchClient::class, MockElasticsearchClient::class);
        }

        // Production: Optimize for performance
        if ($this->app->environment('production')) {
            // Could add production-specific optimizations
            $this->app->extend(ProductService::class, function ($service, $app) {
                // Example: Add production monitoring
                return $service;
            });
        }
    }

    /**
     * Boot model observers for products
     */
    private function bootProductObservers(): void
    {
        // Example: Auto-index products in Elasticsearch when created/updated
        // Product::observe(ProductObserver::class);
    }

    /**
     * Boot custom validation rules
     */
    private function bootValidationRules(): void
    {
        // Example: Custom validation for product search criteria
        // Validator::extend('valid_sort_field', function ($attribute, $value, $parameters, $validator) {
        //     return in_array($value, ['price', 'rating', 'popularity', 'date']);
        // });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ProductRepositoryInterface::class,
            ProductRepository::class,
            ProductService::class,
            ElasticsearchClient::class,
            ElasticsearchQueryBuilder::class,
        ];
    }
}
