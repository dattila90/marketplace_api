<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;

/**
 * Product API Feature Tests
 * 
 * Comprehensive testing of all product-related API endpoints
 * including validation, error handling, and response formats.
 */
class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seedTestData();
    }

    /**
     * Test featured products endpoint
     */
    public function test_featured_products_endpoint(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'products' => [
                        '*' => [
                            'id',
                            'title',
                            'brand',
                            'price' => [
                                'amount',
                                'currency',
                                'formatted'
                            ],
                            'rating',
                            'stock_status',
                            'availability',
                            'category_name'
                        ]
                    ],
                    'total',
                    'limit'
                ],
                'timestamp'
            ])
            ->assertJson([
                'success' => true
            ]);
    }

    /**
     * Test featured products with custom limit
     */
    public function test_featured_products_with_limit(): void
    {
        $response = $this->getJson('/api/v1/products/featured?limit=5');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(5, $data['data']['limit']);
        $this->assertLessThanOrEqual(5, count($data['data']['products']));
    }

    /**
     * Test product search endpoint
     */
    public function test_product_search_endpoint(): void
    {
        $response = $this->getJson('/api/v1/products/search');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'products',
                    'total',
                    'took',
                    'pagination',
                    'filters'
                ],
                'timestamp'
            ]);
    }

    /**
     * Test product search with parameters
     */
    public function test_product_search_with_parameters(): void
    {
        $searchParams = [
            'search' => 'test product',
            'min_price' => 10,
            'max_price' => 1000,
            'sort_by' => 'price',
            'sort_direction' => 'asc',
            'per_page' => 10
        ];

        $response = $this->getJson('/api/v1/products/search?' . http_build_query($searchParams));

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('products', $data['data']);
    }

    /**
     * Test product search validation
     */
    public function test_product_search_validation(): void
    {
        // Test invalid sort_by
        $response = $this->getJson('/api/v1/products/search?sort_by=invalid');
        $response->assertStatus(422);

        // Test invalid price range
        $response = $this->getJson('/api/v1/products/search?min_price=-10');
        $response->assertStatus(422);

        // Test max_price less than min_price
        $response = $this->getJson('/api/v1/products/search?min_price=100&max_price=50');
        $response->assertStatus(422);
    }

    /**
     * Test single product endpoint
     */
    public function test_single_product_endpoint(): void
    {
        $product = Product::first();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'brand',
                    'price',
                    'rating',
                    'category_name',
                    'attributes'
                ],
                'timestamp'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id
                ]
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
                'error' => [
                    'type',
                    'message',
                    'code'
                ],
                'timestamp'
            ])
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => 'PRODUCT_NOT_FOUND'
                ]
            ]);
    }

    /**
     * Test invalid UUID format
     */
    public function test_invalid_uuid_format(): void
    {
        $response = $this->getJson('/api/v1/products/invalid-uuid');

        // Should return 404 due to route constraint
        $response->assertStatus(404);
    }

    /**
     * Test API health endpoint
     */
    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
                'services' => [
                    'database',
                    'elasticsearch',
                    'cache'
                ]
            ])
            ->assertJson([
                'status' => 'healthy'
            ]);
    }

    /**
     * Test API documentation endpoint
     */
    public function test_docs_endpoint(): void
    {
        $response = $this->getJson('/api/docs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'version',
                'description',
                'endpoints',
                'rate_limits'
            ]);
    }

    /**
     * Test response headers
     */
    public function test_response_headers(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        // Check for performance and tracing headers
        $response->assertHeader('X-Response-Time');
        $response->assertHeader('X-Trace-ID');
    }

    /**
     * Test rate limiting headers
     */
    public function test_rate_limiting_headers(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200);

        // Rate limiting headers should be present
        $this->assertArrayHasKey('x-ratelimit-limit', $response->headers->all());
        $this->assertArrayHasKey('x-ratelimit-remaining', $response->headers->all());
    }

    /**
     * Seed test data for API testing
     */
    private function seedTestData(): void
    {
        // Create test categories
        $category = Category::create([
            'name' => 'Test Electronics',
            'path' => 'Test Electronics'
        ]);

        // Create test products
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'title' => "Test Product {$i}",
                'brand' => 'Test Brand',
                'category_id' => $category->id,
                'price' => rand(100, 1000),
                'currency' => 'USD',
                'stock' => rand(1, 100),
                'seller_id' => \Illuminate\Support\Str::uuid(),
                'rating' => rand(30, 50) / 10, // 3.0 to 5.0
                'popularity' => rand(1, 100),
                'attributes' => [
                    'color' => $this->faker->colorName(),
                    'weight' => rand(100, 5000) . 'g'
                ]
            ]);
        }
    }
}
