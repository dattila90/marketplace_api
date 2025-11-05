<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ProductService;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductSearchException;
use Illuminate\Support\Facades\Cache;
use Mockery;

/**
 * Product Service Unit Tests
 * 
 * Testing business logic, caching, error handling,
 * and data transformation in isolation.
 */
class ProductServiceTest extends TestCase
{
    private ProductService $productService;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test search criteria sanitization
     */
    public function test_sanitize_search_criteria(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('sanitizeSearchCriteria');
        $method->setAccessible(true);

        // Test with all parameters
        $criteria = [
            'search' => 'test product',
            'min_price' => '10.50',
            'max_price' => '100.00',
            'category' => 'electronics',
            'sort_by' => 'price',
            'sort_direction' => 'asc',
            'per_page' => '20'
        ];

        $sanitized = $method->invokeArgs($this->productService, [$criteria]);

        $this->assertEquals('test product', $sanitized['search']);
        $this->assertEquals(10.5, $sanitized['min_price']);
        $this->assertEquals(100.0, $sanitized['max_price']);
        $this->assertEquals('electronics', $sanitized['category']);
        $this->assertEquals('price', $sanitized['sort_by']);
        $this->assertEquals('asc', $sanitized['sort_direction']);
        $this->assertEquals(20, $sanitized['per_page']);
    }

    /**
     * Test sanitization with missing optional parameters
     */
    public function test_sanitize_with_missing_parameters(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('sanitizeSearchCriteria');
        $method->setAccessible(true);

        // Test with only search parameter
        $criteria = ['search' => 'test'];
        $sanitized = $method->invokeArgs($this->productService, [$criteria]);

        $this->assertEquals('test', $sanitized['search']);
        $this->assertNull($sanitized['min_price']);
        $this->assertNull($sanitized['max_price']);
        $this->assertNull($sanitized['category']);
        $this->assertEquals('relevance', $sanitized['sort_by']);
        $this->assertEquals('desc', $sanitized['sort_direction']);
        $this->assertEquals(50, $sanitized['per_page']);
    }

    /**
     * Test price validation in sanitization
     */
    public function test_price_validation(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('sanitizeSearchCriteria');
        $method->setAccessible(true);

        // Test negative prices are converted to null
        $criteria = [
            'min_price' => '-10',
            'max_price' => '-5'
        ];

        $sanitized = $method->invokeArgs($this->productService, [$criteria]);

        $this->assertNull($sanitized['min_price']);
        $this->assertNull($sanitized['max_price']);
    }

    /**
     * Test product search with repository success
     */
    public function test_search_products_success(): void
    {
        $searchResults = [
            'hits' => [
                'total' => ['value' => 10],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '123',
                            'title' => 'Test Product',
                            'price' => 99.99
                        ]
                    ]
                ]
            ],
            'took' => 15
        ];

        $this->mockRepository
            ->shouldReceive('search')
            ->once()
            ->andReturn($searchResults);

        $result = $this->productService->searchProducts(['search' => 'test']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('total', $result);
    }

    /**
     * Test product search with repository exception
     */
    public function test_search_products_with_exception(): void
    {
        $this->mockRepository
            ->shouldReceive('search')
            ->once()
            ->andThrow(new \Exception('Search failed'));

        $this->expectException(ProductSearchException::class);
        $this->expectExceptionMessage('Product search service temporarily unavailable');

        $this->productService->searchProducts(['search' => 'test']);
    }

    /**
     * Test get product by ID success
     */
    public function test_get_product_by_id_success(): void
    {
        $productData = [
            'id' => '123',
            'title' => 'Test Product',
            'price' => 99.99,
            'category' => ['name' => 'Electronics']
        ];

        $this->mockRepository
            ->shouldReceive('find')
            ->with('123')
            ->once()
            ->andReturn($productData);

        $result = $this->productService->getProductById('123');

        $this->assertNotNull($result);
        $this->assertEquals('123', $result['id']);
        $this->assertEquals('Test Product', $result['title']);
    }

    /**
     * Test get product not found
     */
    public function test_get_product_by_id_not_found(): void
    {
        $this->mockRepository
            ->shouldReceive('find')
            ->with('non-existent')
            ->once()
            ->andReturn(null);

        $result = $this->productService->getProductById('non-existent');

        $this->assertNull($result);
    }

    /**
     * Test get featured products
     */
    public function test_get_featured_products(): void
    {
        $searchResults = [
            'data' => [
                [
                    'id' => '1',
                    'title' => 'Featured Product 1',
                    'price' => 199.99
                ],
                [
                    'id' => '2',
                    'title' => 'Featured Product 2',
                    'price' => 299.99
                ]
            ]
        ];

        $this->mockRepository
            ->shouldReceive('search')
            ->once()
            ->andReturn($searchResults);

        $result = $this->productService->getFeaturedProducts(10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('limit', $result);
    }

    /**
     * Test price formatting
     */
    public function test_format_price(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('formatPrice');
        $method->setAccessible(true);

        $formatted = $method->invokeArgs($this->productService, [99.99, 'USD']);

        $this->assertIsArray($formatted);
        $this->assertEquals(99.99, $formatted['amount']);
        $this->assertEquals('USD', $formatted['currency']);
        $this->assertEquals('$99.99', $formatted['formatted']);
    }

    /**
     * Test stock status calculation
     */
    public function test_stock_status_calculation(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('getStockStatus');
        $method->setAccessible(true);

        // Test in stock
        $this->assertEquals('in_stock', $method->invokeArgs($this->productService, [10]));

        // Test low stock
        $this->assertEquals('low_stock', $method->invokeArgs($this->productService, [3]));

        // Test out of stock
        $this->assertEquals('out_of_stock', $method->invokeArgs($this->productService, [0]));
    }

    /**
     * Test availability calculation
     */
    public function test_availability_calculation(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('getAvailability');
        $method->setAccessible(true);

        // Test available
        $this->assertEquals('available', $method->invokeArgs($this->productService, [10]));

        // Test limited
        $this->assertEquals('limited', $method->invokeArgs($this->productService, [3]));

        // Test unavailable
        $this->assertEquals('unavailable', $method->invokeArgs($this->productService, [0]));
    }

    /**
     * Test cache key generation
     */
    public function test_cache_key_generation(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('generateCacheKey');
        $method->setAccessible(true);

        $criteria = ['search' => 'test', 'category' => 'electronics'];
        $cacheKey = $method->invokeArgs($this->productService, ['product_search', $criteria]);

        $this->assertIsString($cacheKey);
        $this->assertStringContainsString('product_search', $cacheKey);
    }

    /**
     * Test search results transformation
     */
    public function test_transform_search_results(): void
    {
        $reflection = new \ReflectionClass($this->productService);
        $method = $reflection->getMethod('transformSearchResults');
        $method->setAccessible(true);

        $esResults = [
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '1',
                            'title' => 'Product 1',
                            'price' => 99.99,
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        '_source' => [
                            'id' => '2',
                            'title' => 'Product 2',
                            'price' => 199.99,
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ],
            'took' => 25
        ];

        $transformed = $method->invokeArgs($this->productService, [$esResults]);

        $this->assertIsArray($transformed);
        $this->assertArrayHasKey('products', $transformed);
        $this->assertArrayHasKey('total', $transformed);
        $this->assertArrayHasKey('took', $transformed);
        $this->assertEquals(2, $transformed['total']);
        $this->assertEquals(25, $transformed['took']);
        $this->assertCount(2, $transformed['products']);
    }
}
