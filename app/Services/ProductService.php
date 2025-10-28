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
        $page = $criteria['page'] ?? 1;
        $perPage = $criteria['per_page'] ?? 15;
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $from = $total > 0 ? (($page - 1) * $perPage) + 1 : null;
        $to = $total > 0 ? min($page * $perPage, $total) : null;

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
            'has_more_pages' => $page < $lastPage,
            'links' => [
                'first' => $this->buildPageUrl(1, $criteria),
                'last' => $this->buildPageUrl($lastPage, $criteria),
                'prev' => $page > 1 ? $this->buildPageUrl($page - 1, $criteria) : null,
                'next' => $page < $lastPage ? $this->buildPageUrl($page + 1, $criteria) : null,
            ],
            'path' => request()->url(),
            'query_params' => $this->buildQueryParams($criteria)
        ];
    }

    /**
     * Build URL for pagination links
     */
    private function buildPageUrl(int $page, array $criteria): string
    {
        $params = $this->buildQueryParams($criteria);
        $params['page'] = $page;

        return request()->url() . '?' . http_build_query($params);
    }

    /**
     * Build query parameters for pagination
     */
    private function buildQueryParams(array $criteria): array
    {
        $params = [];

        if (!empty($criteria['search'])) {
            $params['search'] = $criteria['search'];
        }

        if (!empty($criteria['category_id'])) {
            $params['category_id'] = $criteria['category_id'];
        }

        if (!empty($criteria['min_price'])) {
            $params['min_price'] = $criteria['min_price'];
        }

        if (!empty($criteria['max_price'])) {
            $params['max_price'] = $criteria['max_price'];
        }

        if (!empty($criteria['sort_by']) && $criteria['sort_by'] !== 'relevance') {
            $params['sort_by'] = $criteria['sort_by'];
        }

        if (!empty($criteria['sort_direction']) && $criteria['sort_direction'] !== 'desc') {
            $params['sort_direction'] = $criteria['sort_direction'];
        }

        if (!empty($criteria['per_page']) && $criteria['per_page'] !== 15) {
            $params['per_page'] = $criteria['per_page'];
        }

        return $params;
    }
    private function getAvailableFilters(): array
    {
        return [
            'categories' => [
                ['id' => 'electronics', 'name' => 'Electronics', 'count' => 150],
                ['id' => 'clothing', 'name' => 'Clothing', 'count' => 80],
                ['id' => 'books', 'name' => 'Books', 'count' => 45],
                ['id' => 'home', 'name' => 'Home & Garden', 'count' => 92],
            ],
            'brands' => [
                ['name' => 'Apple', 'count' => 45],
                ['name' => 'Samsung', 'count' => 38],
                ['name' => 'Nike', 'count' => 22],
                ['name' => 'Adidas', 'count' => 18],
            ],
            'price_ranges' => [
                ['min' => 0, 'max' => 25, 'label' => 'Under $25', 'count' => 120],
                ['min' => 25, 'max' => 50, 'label' => '$25 - $50', 'count' => 85],
                ['min' => 50, 'max' => 100, 'label' => '$50 - $100', 'count' => 65],
                ['min' => 100, 'max' => 200, 'label' => '$100 - $200', 'count' => 40],
                ['min' => 200, 'max' => null, 'label' => 'Over $200', 'count' => 25],
            ],
            'ratings' => [
                ['min' => 4, 'label' => '4+ Stars', 'count' => 180],
                ['min' => 3, 'label' => '3+ Stars', 'count' => 250],
                ['min' => 2, 'label' => '2+ Stars', 'count' => 300],
            ],
            'availability' => [
                ['key' => 'in_stock', 'label' => 'In Stock', 'count' => 285],
                ['key' => 'out_of_stock', 'label' => 'Out of Stock', 'count' => 15],
            ]
        ];
    }
}
