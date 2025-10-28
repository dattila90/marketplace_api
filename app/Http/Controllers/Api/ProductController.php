<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSearchRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

/**
 * Product API Controller
 */
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Search products with filters and pagination
     * 
     * @param ProductSearchRequest $request
     * @return JsonResponse
     */
    public function search(ProductSearchRequest $request): JsonResponse
    {
        try {
            $criteria = $request->getSearchCriteria();
            $results = $this->productService->searchProducts($criteria);

            return $this->successResponse($results, 'Products retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to search products',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Get featured products for homepage
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min(50, max(1, (int) $request->get('limit', 12)));
            $products = $this->productService->getFeaturedProducts($limit);

            return $this->successResponse([
                'products' => $products,
                'total' => count($products),
                'limit' => $limit
            ], 'Featured products retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve featured products',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Standard success response format
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    private function successResponse($data, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Standard error response format
     * 
     * @param string $message
     * @param int $statusCode
     * @param string|null $details
     * @return JsonResponse
     */
    private function errorResponse(string $message, int $statusCode = Response::HTTP_BAD_REQUEST, ?string $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($details && config('app.debug')) {
            $response['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }
}
