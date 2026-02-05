<?php

namespace App\Services;

use App\Repositories\RecentlyViewedRepository;

class RecentlyViewedService
{
    private RecentlyViewedRepository $recentlyViewedRepository;

    public function __construct(RecentlyViewedRepository $recentlyViewedRepository)
    {
        $this->recentlyViewedRepository = $recentlyViewedRepository;
    }

    /**
     * Session ID'yi getirir
     * 
     * @return string
     */
    private function getSessionId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return session_id();
    }

    /**
     * Ürün görüntüleme kaydı ekler veya günceller
     * 
     * @param int $productId
     * @return bool
     */
    public function addView(int $productId): bool
    {
        $sessionId = $this->getSessionId();
        $result = $this->recentlyViewedRepository->addOrUpdate($sessionId, $productId);
        
        // Eski kayıtları temizle (performans için)
        $this->recentlyViewedRepository->cleanOldRecords($sessionId, 20);
        
        return $result;
    }

    /**
     * Son görüntülenen ürünleri getirir
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentlyViewed(int $limit = 10): array
    {
        $sessionId = $this->getSessionId();
        $items = $this->recentlyViewedRepository->findBySession($sessionId, $limit);
        
        // Formatla
        $formatted = [];
        foreach ($items as $item) {
            $formatted[] = [
                'id' => (int) $item['product_id'],
                'name' => $item['name'],
                'price' => (float) $item['price'],
                'image' => $item['image_url'],
                'description' => $item['description'],
                'category_name' => $item['category_name'],
                'viewed_at' => $item['viewed_at']
            ];
        }
        
        return $formatted;
    }
}
