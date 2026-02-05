<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Services\CacheService;

class CategoryService implements CategoryServiceInterface
{
    private CategoryRepository $categoryRepository;
    private ?CacheService $cacheService;

    public function __construct(CategoryRepository $categoryRepository, ?CacheService $cacheService = null)
    {
        $this->categoryRepository = $categoryRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Tüm kategorileri getirir
     * 
     * @return array
     */
    public function getCategories(): array
    {
        // Cache kontrolü
        $cacheKey = 'categories_all';
        
        if ($this->cacheService !== null) {
            $cached = $this->cacheService->get($cacheKey);
            
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $categories = $this->categoryRepository->findAll();
        
        // Cache'e kaydet (30 dakika TTL - kategoriler sık değişmez)
        if ($this->cacheService !== null) {
            $this->cacheService->set($cacheKey, $categories, 1800);
        }
        
        return $categories;
    }
}

