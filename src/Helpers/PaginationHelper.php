<?php

namespace App\Helpers;

class PaginationHelper
{
    /**
     * Pagination bilgilerini hesaplar
     * 
     * @param int $totalItems
     * @param int $currentPage
     * @param int $perPage
     * @return array
     */
    public static function calculate(int $totalItems, int $currentPage, int $perPage): array
    {
        $totalPages = (int) ceil($totalItems / $perPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        
        return [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }

    /**
     * SQL LIMIT ve OFFSET değerlerini hesaplar
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public static function getLimitOffset(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        return [
            'limit' => $perPage,
            'offset' => $offset
        ];
    }
}

