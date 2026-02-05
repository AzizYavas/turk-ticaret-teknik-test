<?php

namespace App\Models;

class Cart
{
    private const SESSION_KEY = 'cart';

    /**
     * Sepeti başlatır
     */
    private function initCart(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    /**
     * Sepeti getirir
     * 
     * @return array
     */
    public function getCart(): array
    {
        $this->initCart();
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    /**
     * Sepete ürün ekler veya miktarını günceller
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array
     */
    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $this->initCart();
        
        $key = $this->getCartKey($productId, $variantId);
        
        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            $item = $_SESSION[self::SESSION_KEY][$key];
            $item['quantity'] += $quantity;
            $_SESSION[self::SESSION_KEY][$key] = $item;
        } else {
            $_SESSION[self::SESSION_KEY][$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ];
        }
        
        return $this->getCart();
    }

    /**
     * Sepet key'ini oluşturur
     * 
     * @param int $productId
     * @param int|null $variantId
     * @return string
     */
    private function getCartKey(int $productId, ?int $variantId = null): string
    {
        return $variantId !== null ? "{$productId}_{$variantId}" : (string) $productId;
    }

    /**
     * Sepetten ürün çıkarır
     * 
     * @param int $productId
     * @param int|null $variantId
     * @return array
     */
    public function removeItem(int $productId, ?int $variantId = null): array
    {
        $this->initCart();
        
        $key = $this->getCartKey($productId, $variantId);
        
        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            unset($_SESSION[self::SESSION_KEY][$key]);
        }
        
        return $this->getCart();
    }

    /**
     * Ürün miktarını günceller
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array
     */
    public function updateQuantity(int $productId, int $quantity, ?int $variantId = null): array
    {
        $this->initCart();
        
        if ($quantity <= 0) {
            return $this->removeItem($productId, $variantId);
        }
        
        $key = $this->getCartKey($productId, $variantId);
        
        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            $item = $_SESSION[self::SESSION_KEY][$key];
            $item['quantity'] = $quantity;
            $_SESSION[self::SESSION_KEY][$key] = $item;
        }
        
        return $this->getCart();
    }

    /**
     * Sepeti temizler
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->initCart();
        $_SESSION[self::SESSION_KEY] = [];
    }

    /**
     * Sepetteki ürün sayısını getirir
     * 
     * @return int
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;
        
        foreach ($cart as $item) {
            if (is_array($item)) {
                $count++; // Her item (ürün+varyant kombinasyonu) bir sayı
            } else {
                // Eski format desteği
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Sepetteki toplam ürün miktarını getirir
     * 
     * @return int
     */
    public function getTotalQuantity(): int
    {
        $cart = $this->getCart();
        $total = 0;
        
        foreach ($cart as $item) {
            if (is_array($item) && isset($item['quantity'])) {
                $total += (int) $item['quantity'];
            } else {
                // Eski format desteği (backward compatibility)
                $total += (int) $item;
            }
        }
        
        return $total;
    }

    /**
     * Başka bir session'dan sepeti alır
     * 
     * @param string $sessionId
     * @return array|null
     */
    public function getCartFromSession(string $sessionId): ?array
    {
        // Session'ı başlatmadan önce mevcut session'ı kaydet
        $currentSessionId = session_id();
        
        // Eski session'ı oku
        session_write_close();
        session_id($sessionId);
        session_start();
        
        $cart = $_SESSION[self::SESSION_KEY] ?? null;
        
        // Session'ı kapat ve mevcut session'a geri dön
        session_write_close();
        session_id($currentSessionId);
        session_start();
        
        return $cart;
    }

    /**
     * Başka bir session'dan sepeti mevcut sepete birleştirir
     * 
     * @param array $sourceCart
     * @return array Birleştirilmiş sepet
     */
    public function mergeCart(array $sourceCart): array
    {
        $this->initCart();
        $currentCart = $this->getCart();
        
        // Kaynak sepeti dolaş ve mevcut sepete ekle
        foreach ($sourceCart as $key => $sourceItem) {
            // Eski format desteği
            if (!is_array($sourceItem)) {
                $productId = (int) $key;
                $quantity = (int) $sourceItem;
                $variantId = null;
            } else {
                $productId = (int) ($sourceItem['product_id'] ?? $key);
                $quantity = (int) ($sourceItem['quantity'] ?? 0);
                $variantId = isset($sourceItem['variant_id']) ? (int) $sourceItem['variant_id'] : null;
            }
            
            if ($quantity > 0) {
                $cartKey = $this->getCartKey($productId, $variantId);
                
                // Mevcut sepette varsa miktarı topla, yoksa ekle
                if (isset($currentCart[$cartKey])) {
                    $currentItem = $currentCart[$cartKey];
                    if (is_array($currentItem)) {
                        $currentItem['quantity'] += $quantity;
                        $_SESSION[self::SESSION_KEY][$cartKey] = $currentItem;
                    }
                } else {
                    $_SESSION[self::SESSION_KEY][$cartKey] = [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'quantity' => $quantity
                    ];
                }
            }
        }
        
        return $this->getCart();
    }

    /**
     * Session ID'yi saklar (birleştirme için)
     * 
     * @param string $sessionId
     * @return void
     */
    public function saveSessionId(string $sessionId): void
    {
        $this->initCart();
        $_SESSION['previous_session_id'] = $sessionId;
    }

    /**
     * Kaydedilmiş session ID'yi getirir
     * 
     * @return string|null
     */
    public function getSavedSessionId(): ?string
    {
        $this->initCart();
        return $_SESSION['previous_session_id'] ?? null;
    }
}
