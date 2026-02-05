<?php

namespace App\Controllers;

use App\Services\CouponService;
use App\Services\CartService;
use App\Services\LogService;
use App\Http\Request;
use App\Helpers\ResponseHelper;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class CouponController
{
    private CouponService $couponService;
    private CartService $cartService;
    private LogService $logService;
    private Request $request;

    public function __construct(CouponService $couponService, CartService $cartService, LogService $logService, Request $request)
    {
        $this->couponService = $couponService;
        $this->cartService = $cartService;
        $this->logService = $logService;
        $this->request = $request;
    }

    /**
     * Kuponu doğrular
     * POST /api/coupons/validate
     */
    public function validate(): void
    {
        try {
            if (!$this->request->hasBody('code')) {
                throw new ValidationException('Kupon kodu gereklidir');
            }

            $code = $this->request->bodyString('code');
            $cartTotal = $this->request->hasBody('cart_total') 
                ? $this->request->bodyFloat('cart_total') 
                : null;

            $result = $this->couponService->validateCoupon($code, $cartTotal);
            ResponseHelper::success($result, 'Kupon doğrulandı');
        } catch (ValidationException $e) {
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Kuponu sepete uygular
     * POST /api/coupons/apply
     */
    public function apply(): void
    {
        try {
            if (!$this->request->hasBody('code')) {
                throw new ValidationException('Kupon kodu gereklidir');
            }

            $code = $this->request->bodyString('code');
            
            // Sepet tutarını al
            $cart = $this->cartService->getCart();
            $cartTotal = $cart['total_amount'] ?? 0.0;
            
            $result = $this->couponService->applyCoupon($code, $cartTotal);
            
            $this->logService->info('Kupon sepete uygulandı', [
                'coupon_code' => $code,
                'cart_total' => $cartTotal,
                'discount' => $result['discount'] ?? 0
            ]);
            
            ResponseHelper::success($result, 'Kupon sepete uygulandı');
        } catch (ValidationException $e) {
            $this->logService->warning('Kupon uygulama hatası', [
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), 400, $e->getErrorCode());
        } catch (NotFoundException $e) {
            $this->logService->warning('Kupon uygulama hatası - Kupon bulunamadı', [
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ]);
            ResponseHelper::error($e->getMessage(), $e->getCode(), $e->getErrorCode());
        } catch (\Exception $e) {
            $this->logService->error('Kupon uygulama hatası', [
                'error' => $e->getMessage()
            ]);
            ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Kuponu kaldırır
     * DELETE /api/coupons
     */
    public function remove(): void
    {
        try {
            $result = $this->couponService->removeCoupon();
            ResponseHelper::success($result, 'Kupon kaldırıldı');
        } catch (\Exception $e) {
            ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
