<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Basic API Route Tests
 * 
 * Simple tests to ensure routes are working without complex seeding
 */
class BasicApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API health endpoint
     */
    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp'
            ]);
    }

    /**
     * Test API docs endpoint
     */
    public function test_docs_endpoint(): void
    {
        $response = $this->getJson('/api/docs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'version',
                'description'
            ]);
    }

    /**
     * Test featured products endpoint (empty database)
     */
    public function test_featured_products_empty(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'timestamp'
            ]);
    }

    /**
     * Test search products endpoint (empty database)
     */
    public function test_search_products_empty(): void
    {
        $response = $this->getJson('/api/v1/products/search');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'timestamp'
            ]);
    }

    /**
     * Test product not found
     */
    public function test_product_not_found(): void
    {
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';

        $response = $this->getJson("/api/v1/products/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'error',
                'timestamp'
            ]);
    }
}
