<?php

namespace App\Services;

use App\Repositories\CouponRepository;
use App\Models\Cart;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class CouponService
{
    private CouponRepository $couponRepository;
    private Cart $cartModel;

    public function __construct(CouponRepository $couponRepository)
    {
        $this->couponRepository = $couponRepository;
        $this->cartModel = new Cart();
    }

    /**
     * Kuponu doğrular
     * 
     * @param string $code
     * @param float|null $cartTotal
     * @return array
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function validateCoupon(string $code, ?float $cartTotal = null): array
    {
        // Kuponu bul
        $coupon = $this->couponRepository->findByCode($code);
        if (!$coupon) {
            throw new NotFoundException('Kupon bulunamadı', 404, 'COUPON_NOT_FOUND');
        }

        // Kupon geçerliliğini kontrol et
        $validation = $this->validateCouponRules($coupon);
        if (!$validation['valid']) {
            throw new ValidationException($validation['message'], [], 400, 'COUPON_INVALID');
        }

        // Sepet tutarı varsa minimum tutar kontrolü yap
        if ($cartTotal !== null) {
            $minCartValidation = $this->validateMinCartTotal($coupon, $cartTotal);
            if (!$minCartValidation['valid']) {
                throw new ValidationException($minCartValidation['message'], [], 400, 'COUPON_MIN_CART_NOT_MET');
            }
        }

        // İndirim bilgilerini hesapla
        $discount = $cartTotal !== null 
            ? $this->calculateDiscount($coupon, $cartTotal)
            : 0;

        return [
            'coupon' => [
                'id' => (int) $coupon['id'],
                'code' => $coupon['code'],
                'type' => $coupon['type'],
                'value' => (float) $coupon['value'],
                'min_cart_total' => (float) $coupon['min_cart_total'],
                'discount' => $discount
            ],
            'valid' => true,
            'message' => 'Kupon geçerli'
        ];
    }

    /**
     * Kuponu sepete uygular
     * 
     * @param string $code
     * @return array
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function applyCoupon(string $code, ?float $cartTotal = null): array
    {
        // Sepet tutarı zorunlu - Controller'dan gelmeli
        if ($cartTotal === null) {
            throw new \InvalidArgumentException('Cart total is required');
        }

        // Kuponu doğrula
        $validation = $this->validateCoupon($code, $cartTotal);
        $coupon = $validation['coupon'];

        // Kupon kullanım sayısını artır
        $this->couponRepository->incrementUsage($coupon['id']);

        // Session'a kupon bilgisini kaydet
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['applied_coupon'] = [
            'id' => $coupon['id'],
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'min_cart_total' => $coupon['min_cart_total'],
            'discount' => $coupon['discount']
        ];

        // Güncellenmiş sepet bilgilerini döndür
        $cartDetails = $this->getCartDetailsForCoupon();
        return [
            'cart' => $cartDetails,
            'coupon' => $coupon,
            'discount' => $coupon['discount'],
            'total_after_discount' => max(0, $cartTotal - $coupon['discount'])
        ];
    }

    /**
     * Kuponu kaldırır
     * 
     * @return array
     */
    public function removeCoupon(): array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        unset($_SESSION['applied_coupon']);

        // Sepet bilgilerini döndür (basit hesaplama - circular dependency önlemek için)
        $cart = $this->getCartDetailsForCoupon();

        return [
            'cart' => $cart,
            'message' => 'Kupon kaldırıldı'
        ];
    }

    /**
     * Uygulanmış kuponu getirir
     * 
     * @return array|null
     */
    public function getAppliedCoupon(): ?array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        return $_SESSION['applied_coupon'] ?? null;
    }

    /**
     * Sepet tutarını kupon indirimi ile hesaplar
     * 
     * @param float $cartTotal
     * @return array
     */
    public function calculateCartWithCoupon(float $cartTotal): array
    {
        $appliedCoupon = $this->getAppliedCoupon();
        
        if (!$appliedCoupon) {
            return [
                'cart_total' => $cartTotal,
                'discount' => 0,
                'total_after_discount' => $cartTotal,
                'coupon' => null
            ];
        }

        // Kuponu tekrar doğrula (süre dolmuş olabilir)
        try {
            $validation = $this->validateCoupon($appliedCoupon['code'], $cartTotal);
            $discount = $validation['coupon']['discount'];
            // Güncel kupon bilgisini güncelle
            $appliedCoupon = $validation['coupon'];
        } catch (\Exception $e) {
            // Kupon geçersizse kaldır
            $this->removeCoupon();
            $discount = 0;
            $appliedCoupon = null;
        }

        return [
            'cart_total' => $cartTotal,
            'discount' => $discount,
            'total_after_discount' => max(0, $cartTotal - $discount),
            'coupon' => $appliedCoupon ? [
                'id' => $appliedCoupon['id'],
                'code' => $appliedCoupon['code'],
                'type' => $appliedCoupon['type'],
                'value' => $appliedCoupon['value'],
                'min_cart_total' => $appliedCoupon['min_cart_total'],
                'discount' => $discount
            ] : null
        ];
    }

    /**
     * Kupon geçerliliğini kontrol eder
     * 
     * @param array $coupon
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateCouponRules(array $coupon): array
    {
        // Tarih kontrolü
        if (!empty($coupon['expires_at'])) {
            $expiresAt = strtotime($coupon['expires_at']);
            if ($expiresAt < time()) {
                return [
                    'valid' => false,
                    'message' => 'Kupon süresi dolmuş'
                ];
            }
        }

        // Kullanım limiti kontrolü
        if (!empty($coupon['usage_limit'])) {
            if ($coupon['used_count'] >= $coupon['usage_limit']) {
                return [
                    'valid' => false,
                    'message' => 'Kupon kullanım limitine ulaşılmış'
                ];
            }
        }

        // Aktiflik kontrolü
        if (empty($coupon['is_active'])) {
            return [
                'valid' => false,
                'message' => 'Kupon aktif değil'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Kupon geçerli'
        ];
    }

    /**
     * Minimum sepet tutarı kontrolü
     * 
     * @param array $coupon
     * @param float $cartTotal
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateMinCartTotal(array $coupon, float $cartTotal): array
    {
        $minCartTotal = (float) ($coupon['min_cart_total'] ?? 0);
        
        if ($minCartTotal > 0 && $cartTotal < $minCartTotal) {
            return [
                'valid' => false,
                'message' => "Bu kuponu kullanmak için minimum {$minCartTotal} TL tutarında alışveriş yapmalısınız"
            ];
        }

        return [
            'valid' => true,
            'message' => 'Minimum sepet tutarı şartı sağlandı'
        ];
    }

    /**
     * İndirim tutarını hesaplar
     * 
     * @param array $coupon
     * @param float $cartTotal
     * @return float
     */
    private function calculateDiscount(array $coupon, float $cartTotal): float
    {
        $value = (float) $coupon['value'];
        $type = $coupon['type'];

        if ($type === 'percentage') {
            // Yüzdelik indirim
            $discount = ($cartTotal * $value) / 100;
        } else {
            // Sabit tutar indirimi
            $discount = $value;
        }

        // İndirim sepet tutarını aşamaz
        return min($discount, $cartTotal);
    }

    /**
     * Kupon için sepet detaylarını getirir (circular dependency önlemek için)
     * 
     * @return array
     */
    private function getCartDetailsForCoupon(): array
    {
        $cart = $this->cartModel->getCart();
        $items = [];
        $totalAmount = 0.00;

        foreach ($cart as $productId => $quantity) {
            // Basit hesaplama - ProductRepository'ye ihtiyaç yok
            // Sadece toplam tutarı döndür
            $items[] = [
                'product_id' => (int) $productId,
                'quantity' => $quantity
            ];
        }

        return [
            'items' => $items,
            'total_items' => $this->cartModel->getItemCount(),
            'total_quantity' => $this->cartModel->getTotalQuantity(),
            'total_amount' => $totalAmount // CartService'den gelecek
        ];
    }
}
