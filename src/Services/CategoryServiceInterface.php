<?php

namespace App\Services;

interface CategoryServiceInterface
{
    /**
     * Tüm kategorileri getirir
     * 
     * @return array
     */
    public function getCategories(): array;
}
