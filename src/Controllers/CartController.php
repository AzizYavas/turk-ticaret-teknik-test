<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Services\LogService;
use App\Http\Request;
use App\Helpers\ResponseHelper;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class CartController
{
    private CartService $cartService;
    private LogService $logService;
    private Request $request;

    public function __construct(CartService $cartService, LogService $logService, Request $request)
    {
        $this->cartService = $cartService;
        $this->logService = $logService;
        $this->request = $request;
    }

    /**
     * Sepeti görüntüler
     * GET /api/cart
     */
    public function index(): void
    {
        try {
            $cart = $this->cartService->getCart();
            ResponseHelper::success($cart, 'Sepet başarıyla getirildi');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Sepete ürün ekler
     * POST /api/cart
     */
    public function add(): void
    {
        try {
            if (!$this->request->hasBody('product_id')) {
                throw new ValidationException('product_id parametresi gereklidir');
            }

            $productId = $this->request->bodyInt('product_id');
            $quantity = $this->request->bodyInt('quantity', 1);
            $variantId = $this->request->hasBody('variant_id') ? $this->request->bodyInt('variant_id') : null;

            if ($quantity <= 0) {
                throw new ValidationException('Miktar 0\'dan büyük olmalıdır');
            }

            $cart = $this->cartService->addItem($productId, $quantity, $variantId);
            $this->logService->info('Ürün sepete eklendi', [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ]);
            ResponseHelper::success($cart, 'Ürün sepete eklendi');
        } catch (ValidationException $e) {
            $this->logService->warning('Sepete ürün ekleme hatası', [
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            $this->logService->warning('Sepete ürün ekleme hatası - Ürün bulunamadı', [
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            $this->logService->error('Sepete ürün ekleme hatası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Sepetten ürün çıkarır
     * DELETE /api/cart/{productId}
     */
    public function remove(int $productId): void
    {
        try {
            $cart = $this->cartService->removeItem($productId);
            $this->logService->info('Ürün sepetten çıkarıldı', ['product_id' => $productId]);
            ResponseHelper::success($cart, 'Ürün sepetten çıkarıldı');
        } catch (\Exception $e) {
            $this->logService->error('Sepetten ürün çıkarma hatası', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Ürün miktarını günceller
     * PUT /api/cart/{productId}
     */
    public function update(int $productId): void
    {
        try {
            if (!$this->request->hasBody('quantity')) {
                throw new ValidationException('quantity parametresi gereklidir');
            }

            $quantity = $this->request->bodyInt('quantity');
            $variantId = $this->request->hasBody('variant_id') ? $this->request->bodyInt('variant_id') : null;

            if ($quantity < 0) {
                throw new ValidationException('Miktar 0 veya daha büyük olmalıdır');
            }

            $cart = $this->cartService->updateQuantity($productId, $quantity, $variantId);
            $this->logService->info('Ürün miktarı güncellendi', [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ]);
            ResponseHelper::success($cart, 'Ürün miktarı güncellendi');
        } catch (ValidationException $e) {
            $this->logService->warning('Ürün miktarı güncelleme hatası', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            $this->logService->warning('Ürün miktarı güncelleme hatası - Ürün bulunamadı', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            $this->logService->error('Ürün miktarı güncelleme hatası', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Sepeti temizler
     * DELETE /api/cart
     */
    public function clear(): void
    {
        try {
            $cart = $this->cartService->clearCart();
            $this->logService->info('Sepet temizlendi');
            ResponseHelper::success($cart, 'Sepet temizlendi');
        } catch (\Exception $e) {
            $this->logService->error('Sepet temizleme hatası', ['error' => $e->getMessage()]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Başka bir session'dan sepeti mevcut sepete birleştirir
     * POST /api/cart/merge
     */
    public function merge(): void
    {
        try {
            if (!$this->request->hasBody('session_id')) {
                throw new ValidationException('session_id parametresi gereklidir');
            }

            $sourceSessionId = $this->request->bodyString('session_id');

            $cart = $this->cartService->mergeCartFromSession($sourceSessionId);
            
            $this->logService->info('Sepet birleştirildi', [
                'source_session_id' => $sourceSessionId
            ]);
            
            ResponseHelper::success($cart, 'Sepet başarıyla birleştirildi');
        } catch (ValidationException $e) {
            $this->logService->warning('Sepet birleştirme hatası', [
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (\Exception $e) {
            $this->logService->error('Sepet birleştirme hatası', [
                'error' => $e->getMessage()
            ]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
