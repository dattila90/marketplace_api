<?php

namespace App\Clients;

use App\QueryBuilders\ElasticsearchQueryBuilder;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Elasticsearch Client
 */
class ElasticsearchClient
{
    private array $config;
    private ?object $client = null;

    public function __construct()
    {
        $this->config = [
            'hosts' => [env('ELASTICSEARCH_HOST', 'localhost:9200')],
            'retries' => 3,
            'timeout' => 30,
        ];

        // In production: $this->client = new \Elasticsearch\Client($this->config);
    }

    /**
     * Search products using QueryBuilder
     */
    public function search(ElasticsearchQueryBuilder $queryBuilder): array
    {
        try {
            $query = $queryBuilder->build();
            Log::info('ES Query:', $query);

            // In production: $response = $this->client->search($query);
            $response = $this->mockElasticsearchResponse($query);

            return $this->formatResponse($response);
        } catch (Exception $e) {
            Log::error('ES search failed:', [
                'error' => $e->getMessage(),
                'query' => $queryBuilder->toArray()
            ]);
            throw new Exception('Search service unavailable', 503);
        }
    }

    /**
     * Index a product document
     */
    public function indexProduct(array $product): bool
    {
        try {
            $params = [
                'index' => 'products',
                'id' => $product['id'],
                'body' => $product
            ];

            Log::info('Indexing product:', ['id' => $product['id']]);

            // In production: $this->client->index($params);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to index product:', [
                'error' => $e->getMessage(),
                'product_id' => $product['id'] ?? 'unknown'
            ]);
            return false;
        }
    }

    /**
     * Check ES health
     */
    public function ping(): bool
    {
        try {
            // In production: return $this->client->ping();
            return true;
        } catch (Exception $e) {
            Log::error('ES ping failed:', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Mock ES response for development
     */
    private function mockElasticsearchResponse(array $query): array
    {
        $size = $query['body']['size'] ?? 10;
        $from = $query['body']['from'] ?? 0;

        return [
            'took' => 15,
            'hits' => [
                'total' => ['value' => 150],
                'hits' => $this->generateMockHits($size, $from)
            ]
        ];
    }

    /**
     * Generate mock search results
     */
    private function generateMockHits(int $size, int $from): array
    {
        $hits = [];
        for ($i = 0; $i < $size; $i++) {
            $id = $from + $i + 1;
            $hits[] = [
                '_id' => "product-{$id}",
                '_score' => 1.0 - ($i * 0.1),
                '_source' => [
                    'id' => "product-{$id}",
                    'title' => "Sample Product {$id}",
                    'brand' => 'Test Brand',
                    'price' => rand(10, 500),
                    'currency' => 'USD',
                    'rating' => rand(3, 5) + (rand(0, 99) / 100),
                    'stock' => rand(0, 100),
                    'popularity' => rand(1, 1000),
                    'category_id' => 'category-1',
                    'seller_id' => 'seller-' . rand(1, 10),
                    'attributes' => [
                        'color' => 'Blue',
                        'size' => 'Medium'
                    ],
                    'created_at' => now()->subDays(rand(1, 30))->toISOString()
                ]
            ];
        }
        return $hits;
    }

    /**
     * Format ES response for application use
     */
    private function formatResponse(array $response): array
    {
        return [
            'data' => array_map(fn($hit) => $hit['_source'], $response['hits']['hits']),
            'total' => $response['hits']['total']['value'],
            'took' => $response['took']
        ];
    }
}
