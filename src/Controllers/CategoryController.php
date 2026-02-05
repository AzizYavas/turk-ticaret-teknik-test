<?php

namespace App\Controllers;

use App\Services\CategoryServiceInterface;
use App\Helpers\ResponseHelper;

class CategoryController
{
    private CategoryServiceInterface $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Kategori listesini döndürür
     * GET /api/categories
     */
    public function index(): void
    {
        try {
            $categories = $this->categoryService->getCategories();
            
            ResponseHelper::success($categories, 'Kategoriler başarıyla getirildi');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}

