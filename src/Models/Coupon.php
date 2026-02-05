<?php

namespace App\Models;

use PDO;

class Coupon extends BaseModel
{
    protected function getTableName(): string
    {
        return 'coupons';
    }

    /**
     * Kupon koduna göre kupon getirir
     * 
     * @param string $code
     * @return array|null
     */
    public function findByCode(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE code = :code AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $code, \PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Kupon kullanım sayısını artırır
     * 
     * @param int $couponId
     * @return bool
     */
    public function incrementUsage(int $couponId): bool
    {
        $sql = "UPDATE {$this->getTableName()} SET used_count = used_count + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $couponId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Kuponun geçerli olup olmadığını kontrol eder
     * 
     * @param array $coupon
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateCoupon(array $coupon): array
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
    public function validateMinCartTotal(array $coupon, float $cartTotal): array
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
    public function calculateDiscount(array $coupon, float $cartTotal): float
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
}
