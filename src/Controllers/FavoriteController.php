<?php

namespace App\Controllers;

use App\Services\FavoriteService;
use App\Http\Request;
use App\Helpers\ResponseHelper;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class FavoriteController
{
    private FavoriteService $favoriteService;
    private Request $request;

    public function __construct(FavoriteService $favoriteService, Request $request)
    {
        $this->favoriteService = $favoriteService;
        $this->request = $request;
    }

    /**
     * Favori listesini görüntüler
     * GET /api/favorites
     */
    public function index(): void
    {
        try {
            $favorites = $this->favoriteService->getFavorites();
            ResponseHelper::success($favorites, 'Favoriler başarıyla getirildi');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Favorilere ürün ekler
     * POST /api/favorites
     */
    public function add(): void
    {
        try {
            if (!$this->request->hasBody('product_id')) {
                throw new ValidationException('product_id parametresi gereklidir');
            }

            $productId = $this->request->bodyInt('product_id');

            $favorites = $this->favoriteService->addItem($productId);
            ResponseHelper::success($favorites, 'Ürün favorilere eklendi');
        } catch (ValidationException $e) {
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Favorilerden ürün çıkarır
     * DELETE /api/favorites/{productId}
     */
    public function remove(int $productId): void
    {
        try {
            $favorites = $this->favoriteService->removeItem($productId);
            ResponseHelper::success($favorites, 'Ürün favorilerden çıkarıldı');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Favori ürünü sepete ekler
     * POST /api/favorites/{productId}/add-to-cart
     */
    public function addToCart(int $productId): void
    {
        try {
            $quantity = $this->request->bodyInt('quantity', 1);

            if ($quantity <= 0) {
                throw new ValidationException('Miktar 0\'dan büyük olmalıdır');
            }

            $result = $this->favoriteService->addToCart($productId, $quantity);
            ResponseHelper::success($result, 'Ürün sepete eklendi');
        } catch (ValidationException $e) {
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
