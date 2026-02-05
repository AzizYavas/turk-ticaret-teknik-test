<?php

namespace App\Models;

class Favorite
{
    private const SESSION_KEY = 'favorites';

    /**
     * Favorileri başlatır
     */
    private function initFavorites(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    /**
     * Favori listesini getirir
     * 
     * @return array
     */
    public function getFavorites(): array
    {
        $this->initFavorites();
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    /**
     * Favorilere ürün ekler
     * 
     * @param int $productId
     * @return array
     */
    public function addItem(int $productId): array
    {
        $this->initFavorites();
        
        if (!in_array($productId, $_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY][] = $productId;
        }
        
        return $this->getFavorites();
    }

    /**
     * Favorilerden ürün çıkarır
     * 
     * @param int $productId
     * @return array
     */
    public function removeItem(int $productId): array
    {
        $this->initFavorites();
        
        $key = array_search($productId, $_SESSION[self::SESSION_KEY]);
        if ($key !== false) {
            unset($_SESSION[self::SESSION_KEY][$key]);
            $_SESSION[self::SESSION_KEY] = array_values($_SESSION[self::SESSION_KEY]); // Re-index
        }
        
        return $this->getFavorites();
    }

    /**
     * Ürünün favorilerde olup olmadığını kontrol eder
     * 
     * @param int $productId
     * @return bool
     */
    public function isFavorite(int $productId): bool
    {
        $favorites = $this->getFavorites();
        return in_array($productId, $favorites);
    }

    /**
     * Favori sayısını getirir
     * 
     * @return int
     */
    public function getCount(): int
    {
        return count($this->getFavorites());
    }
}
