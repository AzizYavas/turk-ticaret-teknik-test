<?php

namespace App\Controllers;

use App\Services\ProductServiceInterface;
use App\Services\RecentlyViewedService;
use App\Http\Request;
use App\Helpers\ResponseHelper;
use App\Exceptions\NotFoundException;

class ProductController
{
    private ProductServiceInterface $productService;
    private RecentlyViewedService $recentlyViewedService;
    private Request $request;

    public function __construct(ProductServiceInterface $productService, RecentlyViewedService $recentlyViewedService, Request $request)
    {
        $this->productService = $productService;
        $this->recentlyViewedService = $recentlyViewedService;
        $this->request = $request;
    }

    /**
     * Ürün listesini döndürür
     * GET /api/products
     */
    public function index(): void
    {
        try {
            $params = $this->request->allGet();
            $result = $this->productService->getProducts($params);
            
            ResponseHelper::successWithPagination(
                $result['products'],
                $result['pagination'],
                'Ürünler başarıyla getirildi'
            );
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Tek ürün detayını döndürür
     * GET /api/products/{id}
     */
    public function show(int $id): void
    {
        try {
            $product = $this->productService->getProductById($id);
            
            // Ürün görüntüleme kaydı ekle
            $this->recentlyViewedService->addView($id);
            
            ResponseHelper::success($product, 'Ürün detayı başarıyla getirildi');
        } catch (NotFoundException $e) {
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}

