<?php

namespace App\Services;

use App\Models\Cart;
use App\Repositories\ProductRepository;
use App\Repositories\VariantRepository;
use App\Services\CouponService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class CartService
{
    private Cart $cartModel;
    private ProductRepository $productRepository;
    private VariantRepository $variantRepository;
    private ?CouponService $couponService;

    public function __construct(ProductRepository $productRepository, VariantRepository $variantRepository, ?CouponService $couponService = null)
    {
        $this->cartModel = new Cart();
        $this->productRepository = $productRepository;
        $this->variantRepository = $variantRepository;
        $this->couponService = $couponService;
    }

    /**
     * Sepete ürün ekler
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        // Ürünün var olup olmadığını kontrol et
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
        }

        // Varyant kontrolü
        $variant = null;
        $availableStock = (int) $product['stock'];
        $price = (float) $product['price'];
        
        if ($variantId !== null) {
            $variant = $this->variantRepository->findById($variantId);
            if (!$variant || $variant['product_id'] != $productId) {
                throw new NotFoundException('Varyant bulunamadı', 404, 'VARIANT_NOT_FOUND');
            }
            $availableStock = (int) $variant['stock'];
            $price += (float) $variant['price_modifier'];
        }

        // Stok kontrolü
        $cart = $this->cartModel->getCart();
        $currentQuantityInCart = $this->getCurrentQuantityInCart($cart, $productId, $variantId);
        $requestedQuantity = $currentQuantityInCart + $quantity;

        if ($requestedQuantity > $availableStock) {
            throw new ValidationException(
                "Yeterli stok bulunmuyor. Mevcut stok: {$availableStock}, Sepetteki miktar: {$currentQuantityInCart}, İstenen miktar: {$quantity}",
                [],
                400,
                'INSUFFICIENT_STOCK'
            );
        }

        // Sepete ekle
        $this->cartModel->addItem($productId, $quantity, $variantId);
        
        return $this->getCartDetails();
    }

    /**
     * Sepetteki mevcut miktarı getirir
     * 
     * @param array $cart
     * @param int $productId
     * @param int|null $variantId
     * @return int
     */
    private function getCurrentQuantityInCart(array $cart, int $productId, ?int $variantId = null): int
    {
        foreach ($cart as $item) {
            if (is_array($item) && isset($item['product_id']) && $item['product_id'] == $productId) {
                if (($variantId === null && ($item['variant_id'] ?? null) === null) ||
                    ($variantId !== null && ($item['variant_id'] ?? null) == $variantId)) {
                    return (int) ($item['quantity'] ?? 0);
                }
            }
        }
        return 0;
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
        $this->cartModel->removeItem($productId, $variantId);
        return $this->getCartDetails();
    }

    /**
     * Ürün miktarını günceller
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateQuantity(int $productId, int $quantity, ?int $variantId = null): array
    {
        // Ürünün var olup olmadığını kontrol et
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
        }

        // Varyant kontrolü
        $availableStock = (int) $product['stock'];
        if ($variantId !== null) {
            $variant = $this->variantRepository->findById($variantId);
            if (!$variant || $variant['product_id'] != $productId) {
                throw new NotFoundException('Varyant bulunamadı', 404, 'VARIANT_NOT_FOUND');
            }
            $availableStock = (int) $variant['stock'];
        }

        // Stok kontrolü (quantity 0 ise sepetten çıkarılır, kontrol gerekmez)
        if ($quantity > 0) {
            if ($quantity > $availableStock) {
                throw new ValidationException(
                    "Yeterli stok bulunmuyor. Mevcut stok: {$availableStock}, İstenen miktar: {$quantity}",
                    [],
                    400,
                    'INSUFFICIENT_STOCK'
                );
            }
        }

        $this->cartModel->updateQuantity($productId, $quantity, $variantId);
        return $this->getCartDetails();
    }

    /**
     * Sepeti görüntüler (ürünler + toplam tutar)
     * 
     * @return array
     */
    public function getCart(): array
    {
        return $this->getCartDetails();
    }

    /**
     * Sepeti temizler
     * 
     * @return array
     */
    public function clearCart(): array
    {
        $this->cartModel->clear();
        return [
            'items' => [],
            'total_items' => 0,
            'total_quantity' => 0,
            'total_amount' => 0.00
        ];
    }

    /**
     * Sepet detaylarını getirir (ürün bilgileri + toplam tutar)
     * 
     * @return array
     */
    private function getCartDetails(): array
    {
        $cart = $this->cartModel->getCart();
        $items = [];
        $totalAmount = 0.00;

        foreach ($cart as $key => $item) {
            // Eski format desteği (backward compatibility)
            if (!is_array($item)) {
                $productId = (int) $key;
                $quantity = (int) $item;
                $variantId = null;
            } else {
                $productId = (int) ($item['product_id'] ?? $key);
                $quantity = (int) ($item['quantity'] ?? 0);
                $variantId = isset($item['variant_id']) ? (int) $item['variant_id'] : null;
            }

            $product = $this->productRepository->findById($productId);
            
            if ($product) {
                $price = (float) $product['price'];
                $stock = (int) $product['stock'];
                $variant = null;
                
                // Varyant bilgilerini al
                if ($variantId !== null) {
                    $variant = $this->variantRepository->findById($variantId);
                    if ($variant) {
                        $price += (float) $variant['price_modifier'];
                        $stock = (int) $variant['stock'];
                    }
                }
                
                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;

                $itemData = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'product' => [
                        'id' => (int) $product['id'],
                        'name' => $product['name'],
                        'price' => $price,
                        'stock' => $stock,
                        'image' => $product['image_url'] ?? null,
                        'category_name' => $product['category_name'] ?? null
                    ],
                    'quantity' => $quantity,
                    'subtotal' => $itemTotal
                ];
                
                // Varyant bilgilerini ekle
                if ($variant) {
                    $itemData['variant'] = [
                        'id' => (int) $variant['id'],
                        'type' => $variant['variant_type'],
                        'value' => $variant['variant_value'],
                        'price_modifier' => (float) $variant['price_modifier'],
                        'sku' => $variant['sku']
                    ];
                }
                
                $items[] = $itemData;
            }
        }

        $cartTotal = round($totalAmount, 2);

        // Kupon indirimi hesapla
        $couponCalculation = $this->calculateCouponDiscount($cartTotal);

        return [
            'items' => $items,
            'total_items' => $this->cartModel->getItemCount(),
            'total_quantity' => $this->cartModel->getTotalQuantity(),
            'total_amount' => $cartTotal,
            'coupon' => $couponCalculation['coupon'],
            'discount' => $couponCalculation['discount'],
            'total_after_discount' => $couponCalculation['total_after_discount']
        ];
    }

    /**
     * Kupon indirimini hesaplar
     * 
     * @param float $cartTotal
     * @return array
     */
    private function calculateCouponDiscount(float $cartTotal): array
    {
        if ($this->couponService === null) {
            return [
                'cart_total' => $cartTotal,
                'discount' => 0,
                'total_after_discount' => $cartTotal,
                'coupon' => null
            ];
        }

        return $this->couponService->calculateCartWithCoupon($cartTotal);
    }

    /**
     * Başka bir session'dan sepeti mevcut sepete birleştirir
     * 
     * @param string $sourceSessionId
     * @return array Birleştirilmiş sepet detayları
     * @throws ValidationException
     */
    public function mergeCartFromSession(string $sourceSessionId): array
    {
        if (empty($sourceSessionId)) {
            throw new ValidationException('Session ID gereklidir', [], 400, 'INVALID_SESSION_ID');
        }

        // Kaynak session'dan sepeti al
        $sourceCart = $this->cartModel->getCartFromSession($sourceSessionId);
        
        if ($sourceCart === null || empty($sourceCart)) {
            // Kaynak sepet boş, mevcut sepeti döndür
            return $this->getCartDetails();
        }

        // Stok kontrolü yaparak birleştir
        $mergedItems = [];
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
                // Ürün kontrolü
                $product = $this->productRepository->findById($productId);
                if (!$product) {
                    continue; // Ürün bulunamadı, atla
                }

                // Varyant kontrolü
                $availableStock = (int) $product['stock'];
                if ($variantId !== null) {
                    $variant = $this->variantRepository->findById($variantId);
                    if ($variant && $variant['product_id'] == $productId) {
                        $availableStock = (int) $variant['stock'];
                    } else {
                        continue; // Varyant bulunamadı, atla
                    }
                }

                // Mevcut sepetteki miktarı kontrol et
                $currentCart = $this->cartModel->getCart();
                $currentQuantity = $this->getCurrentQuantityInCart($currentCart, $productId, $variantId);
                $totalQuantity = $currentQuantity + $quantity;

                // Stok yeterliyse ekle
                if ($totalQuantity <= $availableStock) {
                    $mergedItems[] = [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'quantity' => $quantity
                    ];
                } else {
                    // Stok yetersiz, mevcut stok kadar ekle
                    $addableQuantity = max(0, $availableStock - $currentQuantity);
                    if ($addableQuantity > 0) {
                        $mergedItems[] = [
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'quantity' => $addableQuantity
                        ];
                    }
                }
            }
        }

        // Sepetleri birleştir
        foreach ($mergedItems as $item) {
            $this->cartModel->addItem(
                $item['product_id'],
                $item['quantity'],
                $item['variant_id']
            );
        }

        // Session ID'yi kaydet (ileride kullanmak için)
        $this->cartModel->saveSessionId($sourceSessionId);

        return $this->getCartDetails();
    }
}
