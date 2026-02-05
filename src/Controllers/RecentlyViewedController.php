<?php

namespace App\Controllers;

use App\Services\RecentlyViewedService;
use App\Helpers\ResponseHelper;

class RecentlyViewedController
{
    private RecentlyViewedService $recentlyViewedService;

    public function __construct(RecentlyViewedService $recentlyViewedService)
    {
        $this->recentlyViewedService = $recentlyViewedService;
    }

    /**
     * Son görüntülenen ürünleri listeler
     * GET /api/recently-viewed
     */
    public function index(): void
    {
        try {
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            $limit = max(1, min(50, $limit)); // 1-50 arası sınırla
            
            $products = $this->recentlyViewedService->getRecentlyViewed($limit);
            
            ResponseHelper::success($products, 'Son görüntülenen ürünler başarıyla getirildi');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
