<?php

namespace App\Services;

interface ProductServiceInterface
{
    /**
     * Ürün listesini getirir
     * 
     * @param array $params
     * @return array
     */
    public function getProducts(array $params = []): array;

    /**
     * Tek ürün detayını getirir
     * 
     * @param int $id
     * @return array
     */
    public function getProductById(int $id): array;
}
