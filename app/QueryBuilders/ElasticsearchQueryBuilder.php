<?php

namespace App\QueryBuilders;

/**
 * Elasticsearch Query Builder for Product Search
 * 
 * This is the bottom layer that constructs complex ES queries.
 * Uses the Builder pattern for flexible query construction.
 */
class ElasticsearchQueryBuilder
{
  private array $query = [];
  private array $sort = [];
  private int $size = 10;
  private int $from = 0;

  public function __construct()
  {
    // Initialize base query structure
    $this->query = [
      'bool' => [
        'must' => [],
        'filter' => [],
        'should' => []
      ]
    ];
  }

  /**
   * Add full-text search with field boosting
   */
  public function search(string $searchTerm): self
  {
    if (!empty($searchTerm)) {
      $this->query['bool']['must'][] = [
        'multi_match' => [
          'query' => $searchTerm,
          'fields' => [
            'title^2',     // Boost title matches
            'brand^1.5',   // Moderate boost for brand
            'description'
          ],
          'type' => 'best_fields',
          'fuzziness' => 'AUTO'
        ]
      ];
    }
    return $this;
  }

  /**
   * Filter by category
   */
  public function filterByCategory(string $categoryId): self
  {
    $this->query['bool']['filter'][] = [
      'term' => ['category_id' => $categoryId]
    ];
    return $this;
  }

  /**
   * Filter by price range
   */
  public function filterByPriceRange(?float $minPrice, ?float $maxPrice): self
  {
    $rangeFilter = [];
    if ($minPrice !== null) $rangeFilter['gte'] = $minPrice;
    if ($maxPrice !== null) $rangeFilter['lte'] = $maxPrice;

    if (!empty($rangeFilter)) {
      $this->query['bool']['filter'][] = [
        'range' => ['price' => $rangeFilter]
      ];
    }
    return $this;
  }

  /**
   * Sort by price
   */
  public function sortByPrice(string $direction = 'asc'): self
  {
    $this->sort = ['price' => ['order' => $direction]];
    return $this;
  }

  /**
   * Sort by relevance (default)
   */
  public function sortByRelevance(): self
  {
    $this->sort = ['_score' => ['order' => 'desc']];
    return $this;
  }

  /**
   * Set pagination
   */
  public function paginate(int $page = 1, int $perPage = 10): self
  {
    $this->size = $perPage;
    $this->from = ($page - 1) * $perPage;
    return $this;
  }

  /**
   * Build the final ES query
   */
  public function build(): array
  {
    $esQuery = [
      'index' => 'products',
      'body' => [
        'query' => $this->query,
        'size' => $this->size,
        'from' => $this->from,
      ]
    ];

    if (!empty($this->sort)) {
      $esQuery['body']['sort'] = [$this->sort];
    }

    return $esQuery;
  }

  /**
   * Get query for debugging
   */
  public function toArray(): array
  {
    return $this->build();
  }
}
