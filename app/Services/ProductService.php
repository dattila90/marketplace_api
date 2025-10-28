<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Product Service
 * 
 * This service layer orchestrates business logic for products.
 * It coordinates between repositories, applies business rules,
 * handles caching, and transforms data for controllers.
 */
class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Search products with business logic
     */
    public function searchProducts(array $criteria): array
    {
        // Apply business rules and validation
        $criteria = $this->sanitizeSearchCriteria($criteria);

        // Generate cache key for performance
        $cacheKey = $this->generateCacheKey('product_search', $criteria);

        // Try cache first (5 minute TTL)
        return Cache::remember($cacheKey, 300, function () use ($criteria) {
            try {
                $results = $this->productRepository->search($criteria);
                return $this->transformSearchResults($results);
            } catch (Exception $e) {
                Log::error('Product search failed:', [
                    'criteria' => $criteria,
                    'error' => $e->getMessage()
                ]);

                // Return empty results on failure
                return [
                    'products' => [],
                    'total' => 0,
                    'pagination' => $this->buildPaginationMeta($criteria, 0),
                    'filters' => $this->getAvailableFilters(),
                    'error' => 'Search temporarily unavailable'
                ];
            }
        });
    }

    /**
     * Sanitize and validate search criteria
     */
    private function sanitizeSearchCriteria(array $criteria): array
    {
        return [
            'search' => trim($criteria['search'] ?? ''),
            'category_id' => $criteria['category_id'] ?? null,
            'min_price' => max(0, (float) ($criteria['min_price'] ?? 0)),
            'max_price' => $criteria['max_price'] ? max(0, (float) $criteria['max_price']) : null,
            'sort_by' => in_array($criteria['sort_by'] ?? '', ['price', 'rating', 'popularity', 'date'])
                ? $criteria['sort_by'] : 'relevance',
            'sort_direction' => in_array($criteria['sort_direction'] ?? '', ['asc', 'desc'])
                ? $criteria['sort_direction'] : 'desc',
            'page' => max(1, (int) ($criteria['page'] ?? 1)),
            'per_page' => min(50, max(1, (int) ($criteria['per_page'] ?? 15))),
        ];
    }

    /**
     * Transform search results for API response
     */
    private function transformSearchResults(array $results): array
    {
        return [
            'products' => $this->transformProductList($results['data']),
            'total' => $results['total'],
            'took' => $results['took'],
            'pagination' => $this->buildPaginationMeta($results, $results['total']),
            'filters' => $this->getAvailableFilters()
        ];
    }

    /**
     * Transform product list with computed fields
     */
    private function transformProductList(array $products): array
    {
        return array_map([$this, 'transformProduct'], $products);
    }

    /**
     * Transform single product with business logic
     */
    private function transformProduct(array $product): array
    {
        return [
            'id' => $product['id'],
            'title' => $product['title'],
            'brand' => $product['brand'],
            'price' => [
                'amount' => (float) $product['price'],
                'currency' => $product['currency'] ?? 'USD',
                'formatted' => $this->formatPrice($product['price'], $product['currency'] ?? 'USD')
            ],
            'rating' => (float) $product['rating'],
            'stock_status' => $this->getStockStatus($product['stock']),
            'availability' => $product['stock'] > 0,
            'popularity_score' => $product['popularity'] ?? 0,
            'category_id' => $product['category_id'],
            'seller_id' => $product['seller_id'],
            'attributes' => $product['attributes'] ?? [],
            'created_at' => $product['created_at'] ?? null
        ];
    }

    private function getStockStatus(int $stock): string
    {
        if ($stock <= 0) return 'out_of_stock';
        if ($stock <= 5) return 'low_stock';
        if ($stock <= 20) return 'limited_stock';
        return 'in_stock';
    }

    private function formatPrice(float $price, string $currency): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . number_format($price, 2);
    }

    private function generateCacheKey(string $prefix, array $data): string
    {
        return $prefix . '_' . md5(serialize($data));
    }

    private function buildPaginationMeta(array $criteria, int $total): array
    {
        return [];
    }
    private function getAvailableFilters(): array
    {
        return [];
    }
}
