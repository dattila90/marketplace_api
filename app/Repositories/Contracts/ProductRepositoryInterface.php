<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Product Repository Interface
 * 
 * Defines the contract for product data access.
 * Allows different implementations (DB, ES, Cache, etc.)
 */
interface ProductRepositoryInterface
{
    /**
     * Find product by ID
     */
    public function find(string $id): ?array;

    /**
     * Search products with criteria
     */
    public function search(array $criteria): array;

    /**
     * Get products by category
     */
    public function getByCategory(string $categoryId, int $limit = 10): array;

    /**
     * Get featured products (highest rated, in stock)
     */
    public function getFeatured(int $limit = 10): array;

    /**
     * Create new product
     */
    public function create(array $data): array;

    /**
     * Update product
     */
    public function update(string $id, array $data): array;

    /**
     * Delete product
     */
    public function delete(string $id): bool;
}
