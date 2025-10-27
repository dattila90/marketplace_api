<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Clients\ElasticsearchClient;
use App\QueryBuilders\ElasticsearchQueryBuilder;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Product Repository Implementation
 * 
 * Handles data access using multiple sources:
 * - Database for CRUD operations
 * - Elasticsearch for search
 * - Can add cache layer later
 */
class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private ElasticsearchClient $elasticsearchClient
    ) {}

    /**
     * Find product by ID (Database)
     */
    public function find(string $id): ?array
    {
        $product = Product::find($id);
        return $product ? $product->toArray() : null;
    }

    /**
     * Search products (Elasticsearch with DB fallback)
     */
    public function search(array $criteria): array
    {
        try {
            // Try Elasticsearch first
            return $this->searchWithElasticsearch($criteria);
        } catch (\Exception $e) {
            Log::warning('ES search failed, falling back to DB:', [
                'error' => $e->getMessage()
            ]);
            // Fallback to database
            return $this->searchWithDatabase($criteria);
        }
    }

    /**
     * Get products by category
     */
    public function getByCategory(string $categoryId, int $limit = 10): array
    {
        $products = Product::where('category_id', $categoryId)
            ->where('stock', '>', 0)
            ->limit($limit)
            ->get();

        return $products->toArray();
    }

    /**
     * Create product
     */
    public function create(array $data): array
    {
        $product = Product::create($data);

        // Index in ES asynchronously
        $this->indexProductInElasticsearch($product->toArray());

        return $product->toArray();
    }

    /**
     * Update product
     */
    public function update(string $id, array $data): array
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        // Re-index in ES
        $this->indexProductInElasticsearch($product->toArray());

        return $product->toArray();
    }

    /**
     * Delete product
     */
    public function delete(string $id): bool
    {
        $product = Product::findOrFail($id);
        return $product->delete();
    }

    /**
     * Search using Elasticsearch
     */
    private function searchWithElasticsearch(array $criteria): array
    {
        $queryBuilder = new ElasticsearchQueryBuilder();

        // Apply search criteria
        if (!empty($criteria['search'])) {
            $queryBuilder->search($criteria['search']);
        }

        if (!empty($criteria['category_id'])) {
            $queryBuilder->filterByCategory($criteria['category_id']);
        }

        if (!empty($criteria['min_price']) || !empty($criteria['max_price'])) {
            $queryBuilder->filterByPriceRange(
                $criteria['min_price'] ?? null,
                $criteria['max_price'] ?? null
            );
        }

        // Sorting
        switch ($criteria['sort_by'] ?? 'relevance') {
            case 'price':
                $queryBuilder->sortByPrice($criteria['sort_direction'] ?? 'asc');
                break;
            default:
                $queryBuilder->sortByRelevance();
        }

        // Pagination
        $queryBuilder->paginate(
            $criteria['page'] ?? 1,
            $criteria['per_page'] ?? 15
        );

        return $this->elasticsearchClient->search($queryBuilder);
    }

    /**
     * Fallback database search
     */
    private function searchWithDatabase(array $criteria): array
    {
        $query = Product::query();

        if (!empty($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($criteria['category_id'])) {
            $query->where('category_id', $criteria['category_id']);
        }

        $products = $query->limit(20)->get();

        return [
            'data' => $products->toArray(),
            'total' => $products->count(),
            'took' => 0
        ];
    }

    /**
     * Index product in Elasticsearch
     */
    private function indexProductInElasticsearch(array $product): void
    {
        try {
            $this->elasticsearchClient->indexProduct($product);
        } catch (\Exception $e) {
            Log::error('Failed to index product:', [
                'product_id' => $product['id'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
