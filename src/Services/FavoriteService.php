<?php

namespace App\Services;

use App\Models\Favorite;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Models\Cart;
use App\Exceptions\NotFoundException;

class FavoriteService
{
    private Favorite $favoriteModel;
    private ProductRepository $productRepository;
    private CartService $cartService;
    private Cart $cartModel;

    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->favoriteModel = new Favorite();
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
        $this->cartModel = new Cart();
    }

    /**
     * Favorilere ürün ekler
     * 
     * @param int $productId
     * @return array
     * @throws NotFoundException
     */
    public function addItem(int $productId): array
    {
        // Ürünün var olup olmadığını kontrol et
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
        }

        // Favorilere ekle
        $this->favoriteModel->addItem($productId);
        
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
        $this->favoriteModel->removeItem($productId);
        return $this->getFavorites();
    }

    /**
     * Favori listesini görüntüler
     * 
     * @return array
     */
    public function getFavorites(): array
    {
        return $this->getFavoriteDetails();
    }

    /**
     * Favori ürünü sepete ekler
     * 
     * @param int $productId
     * @param int $quantity
     * @return array
     * @throws NotFoundException
     */
    public function addToCart(int $productId, int $quantity = 1): array
    {
        // Ürünün var olup olmadığını kontrol et
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
        }

        // Ürünün favorilerde olup olmadığını kontrol et
        if (!$this->favoriteModel->isFavorite($productId)) {
            throw new NotFoundException('Ürün favorilerde bulunamadı', 404, 'FAVORITE_NOT_FOUND');
        }

        // Sepete ekle
        $this->cartModel->addItem($productId, $quantity);
        
        // Sepet detaylarını getir
        $cart = $this->cartService->getCart();
        
        return [
            'message' => 'Ürün sepete eklendi',
            'cart' => $cart,
            'favorite' => $this->getFavoriteDetails()
        ];
    }

    /**
     * Favori detaylarını getirir (ürün bilgileri)
     * 
     * @return array
     */
    private function getFavoriteDetails(): array
    {
        $favorites = $this->favoriteModel->getFavorites();
        $items = [];

        foreach ($favorites as $productId) {
            $product = $this->productRepository->findById($productId);
            
            if ($product) {
                $items[] = [
                    'product_id' => (int) $productId,
                    'product' => [
                        'id' => (int) $product['id'],
                        'name' => $product['name'],
                        'price' => (float) $product['price'],
                        'description' => $product['description'] ?? null,
                        'image' => $product['image'] ?? null,
                        'category_name' => $product['category_name'] ?? null,
                        'category_slug' => $product['category_slug'] ?? null
                    ]
                ];
            }
        }

        return [
            'items' => $items,
            'total_count' => $this->favoriteModel->getCount()
        ];
    }

}
