<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Support\Facades\Cache;

/**
 * Product Integration Tests
 * 
 * Tests complete data flow from controller through service,
 * repository, and back with real database interactions.
 */
class ProductIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed comprehensive test data
        $this->seedIntegrationData();
    }

    /**
     * Test complete search integration with database
     */
    public function test_complete_search_integration(): void
    {
        // Test basic search that should hit database fallback
        $response = $this->getJson('/api/v1/products/search?search=smartphone');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
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
                            'category_name',
                            'stock_status',
                            'availability'
                        ]
                    ],
                    'total',
                    'took',
                    'pagination',
                    'filters'
                ]
            ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertGreaterThan(0, $data['data']['total']);
    }

    /**
     * Test search with price filtering
     */
    public function test_search_with_price_filter_integration(): void
    {
        $response = $this->getJson('/api/v1/products/search?min_price=500&max_price=1500');

        $response->assertStatus(200);

        $data = $response->json();
        $products = $data['data']['products'];

        // Verify all products fall within price range
        foreach ($products as $product) {
            $price = $product['price']['amount'];
            $this->assertGreaterThanOrEqual(500, $price);
            $this->assertLessThanOrEqual(1500, $price);
        }
    }

    /**
     * Test search with category filtering
     */
    public function test_search_with_category_filter_integration(): void
    {
        $category = Category::where('name', 'Electronics')->first();

        $response = $this->getJson("/api/v1/products/search?category={$category->name}");

        $response->assertStatus(200);

        $data = $response->json();
        $products = $data['data']['products'];

        // Verify all products belong to the specified category
        foreach ($products as $product) {
            $this->assertEquals('Electronics', $product['category_name']);
        }
    }

    /**
     * Test featured products with caching
     */
    public function test_featured_products_caching_integration(): void
    {
        // Clear cache first
        Cache::flush();

        // First request should populate cache
        $response1 = $this->getJson('/api/v1/products/featured?limit=5');
        $response1->assertStatus(200);

        // Second request should use cache (faster response)
        $response2 = $this->getJson('/api/v1/products/featured?limit=5');
        $response2->assertStatus(200);

        // Results should be identical
        $this->assertEquals(
            $response1->json()['data']['products'],
            $response2->json()['data']['products']
        );
    }

    /**
     * Test single product retrieval integration
     */
    public function test_single_product_retrieval_integration(): void
    {
        $product = Product::with('category')->first();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'brand' => $product->brand,
                    'category_name' => $product->category->name
                ]
            ]);

        $data = $response->json()['data'];

        // Verify price formatting
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('formatted', $data['price']);
        $this->assertStringContainsString('$', $data['price']['formatted']);

        // Verify stock status calculation
        $this->assertContains($data['stock_status'], ['in_stock', 'low_stock', 'out_of_stock']);
        $this->assertContains($data['availability'], ['available', 'limited', 'unavailable']);
    }

    /**
     * Test error handling integration
     */
    public function test_error_handling_integration(): void
    {
        // Test non-existent product
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
     * Test validation error integration
     */
    public function test_validation_error_integration(): void
    {
        // Test invalid search parameters
        $response = $this->getJson('/api/v1/products/search?min_price=-100&sort_by=invalid');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

        $errors = $response->json()['errors'];
        $this->assertArrayHasKey('min_price', $errors);
        $this->assertArrayHasKey('sort_by', $errors);
    }

    /**
     * Test pagination integration
     */
    public function test_pagination_integration(): void
    {
        // Request first page
        $response1 = $this->getJson('/api/v1/products/search?per_page=5&page=1');
        $response1->assertStatus(200);

        // Request second page
        $response2 = $this->getJson('/api/v1/products/search?per_page=5&page=2');
        $response2->assertStatus(200);

        $data1 = $response1->json()['data'];
        $data2 = $response2->json()['data'];

        // Verify pagination structure
        $this->assertArrayHasKey('pagination', $data1);
        $this->assertArrayHasKey('pagination', $data2);

        // Verify different products on different pages
        $products1 = collect($data1['products'])->pluck('id')->toArray();
        $products2 = collect($data2['products'])->pluck('id')->toArray();

        $this->assertEmpty(array_intersect($products1, $products2));
    }

    /**
     * Test sorting integration
     */
    public function test_sorting_integration(): void
    {
        // Test price ascending
        $response = $this->getJson('/api/v1/products/search?sort_by=price&sort_direction=asc&per_page=10');
        $response->assertStatus(200);

        $products = $response->json()['data']['products'];
        $prices = collect($products)->pluck('price.amount')->toArray();

        // Verify ascending order
        $sortedPrices = $prices;
        sort($sortedPrices);
        $this->assertEquals($sortedPrices, $prices);
    }

    /**
     * Test API headers integration
     */
    public function test_api_headers_integration(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200);

        // Verify performance tracking headers
        $this->assertNotNull($response->headers->get('X-Response-Time'));
        $this->assertNotNull($response->headers->get('X-Trace-ID'));

        // Verify rate limiting headers
        $this->assertNotNull($response->headers->get('X-RateLimit-Limit'));
        $this->assertNotNull($response->headers->get('X-RateLimit-Remaining'));
    }

    /**
     * Test concurrent requests integration
     */
    public function test_concurrent_requests_integration(): void
    {
        $responses = [];

        // Simulate concurrent requests
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/v1/products/featured?limit=3');
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // All should return same data (cached)
        $firstData = $responses[0]->json()['data']['products'];
        foreach ($responses as $response) {
            $this->assertEquals($firstData, $response->json()['data']['products']);
        }
    }

    /**
     * Seed comprehensive integration test data
     */
    private function seedIntegrationData(): void
    {
        // Create sellers
        $seller1 = Seller::create([
            'name' => 'TechWorld Store',
            'rating' => 4.8
        ]);

        $seller2 = Seller::create([
            'name' => 'GadgetHub',
            'rating' => 4.6
        ]);

        // Create categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'path' => 'Electronics'
        ]);

        $smartphones = Category::create([
            'name' => 'Smartphones',
            'path' => 'Electronics > Smartphones'
        ]);

        $laptops = Category::create([
            'name' => 'Laptops',
            'path' => 'Electronics > Laptops'
        ]);

        // Create diverse products
        $products = [
            [
                'title' => 'iPhone 15 Pro',
                'brand' => 'Apple',
                'category_id' => $smartphones->id,
                'seller_id' => $seller1->id,
                'price' => 1199.99,
                'stock' => 25,
                'rating' => 4.8,
                'popularity' => 95
            ],
            [
                'title' => 'Samsung Galaxy S24',
                'brand' => 'Samsung',
                'category_id' => $smartphones->id,
                'seller_id' => $seller2->id,
                'price' => 999.99,
                'stock' => 30,
                'rating' => 4.6,
                'popularity' => 88
            ],
            [
                'title' => 'MacBook Pro 16"',
                'brand' => 'Apple',
                'category_id' => $laptops->id,
                'seller_id' => $seller1->id,
                'price' => 2499.99,
                'stock' => 15,
                'rating' => 4.9,
                'popularity' => 92
            ],
            [
                'title' => 'Dell XPS 13',
                'brand' => 'Dell',
                'category_id' => $laptops->id,
                'seller_id' => $seller2->id,
                'price' => 1299.99,
                'stock' => 20,
                'rating' => 4.4,
                'popularity' => 75
            ],
            [
                'title' => 'Google Pixel 8',
                'brand' => 'Google',
                'category_id' => $smartphones->id,
                'seller_id' => $seller1->id,
                'price' => 799.99,
                'stock' => 40,
                'rating' => 4.5,
                'popularity' => 78
            ],
            [
                'title' => 'iPad Pro 12.9"',
                'brand' => 'Apple',
                'category_id' => $electronics->id,
                'seller_id' => $seller2->id,
                'price' => 1099.99,
                'stock' => 18,
                'rating' => 4.7,
                'popularity' => 85
            ],
            [
                'title' => 'Surface Laptop 5',
                'brand' => 'Microsoft',
                'category_id' => $laptops->id,
                'seller_id' => $seller1->id,
                'price' => 1599.99,
                'stock' => 12,
                'rating' => 4.3,
                'popularity' => 70
            ],
            [
                'title' => 'OnePlus 12',
                'brand' => 'OnePlus',
                'category_id' => $smartphones->id,
                'seller_id' => $seller2->id,
                'price' => 699.99,
                'stock' => 35,
                'rating' => 4.4,
                'popularity' => 72
            ]
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'currency' => 'USD',
                'attributes' => [
                    'warranty' => '1 year',
                    'condition' => 'new'
                ]
            ]));
        }
    }
}
