<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Repositories\VariantRepository;
use App\Services\CacheService;
use App\Exceptions\NotFoundException;
use App\Helpers\PaginationHelper;

class ProductService implements ProductServiceInterface
{
    private ProductRepository $productRepository;
    private VariantRepository $variantRepository;
    private ?CacheService $cacheService;

    public function __construct(ProductRepository $productRepository, VariantRepository $variantRepository, ?CacheService $cacheService = null)
    {
        $this->productRepository = $productRepository;
        $this->variantRepository = $variantRepository;
        $this->cacheService = $cacheService;
    }

    /**
     * Ürün listesini getirir (filtreleme, sıralama, pagination ile)
     * 
     * @param array $params
     * @return array
     */
    public function getProducts(array $params = []): array
    {
        // Cache key oluştur (arama yoksa cache kullan)
        $useCache = empty($params['search']); // Arama varsa cache kullanma
        $cacheKey = null;
        
        if ($useCache && $this->cacheService !== null) {
            $cacheKey = $this->cacheService->generateKey('products_list', $params);
            $cached = $this->cacheService->get($cacheKey);
            
            if ($cached !== null) {
                return $cached;
            }
        }
        
        // Pagination parametreleri
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $limit = isset($params['limit']) ? (int) $params['limit'] : 10;
        $limit = min(max(1, $limit), 100); // Max 100, min 1
        
        // Filtreler
        $filters = [];
        if (!empty($params['category_id'])) {
            $filters['category_id'] = (int) $params['category_id'];
        }
        if (!empty($params['min_price'])) {
            $filters['min_price'] = (float) $params['min_price'];
        }
        if (!empty($params['max_price'])) {
            $filters['max_price'] = (float) $params['max_price'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = trim($params['search']);
        }
        
        // Sıralama (sort: price_asc, price_desc, name_asc, name_desc)
        $sort = [];
        if (!empty($params['sort'])) {
            $sortValue = strtolower(trim($params['sort']));
            $sortMap = [
                'price_asc' => ['field' => 'price', 'direction' => 'ASC'],
                'price_desc' => ['field' => 'price', 'direction' => 'DESC'],
                'name_asc' => ['field' => 'name', 'direction' => 'ASC'],
                'name_desc' => ['field' => 'name', 'direction' => 'DESC']
            ];
            
            if (isset($sortMap[$sortValue])) {
                $sort = $sortMap[$sortValue];
            }
        }
        
        // Toplam kayıt sayısı
        $totalItems = $this->productRepository->count($filters);
        
        // Pagination hesaplama
        $pagination = PaginationHelper::calculate($totalItems, $page, $limit);
        $limitOffset = PaginationHelper::getLimitOffset($page, $limit);
        
        // Ürünleri getir
        $products = $this->productRepository->findAll(
            $filters,
            $sort,
            $limitOffset['limit'],
            $limitOffset['offset']
        );
        
        $result = [
            'products' => $products,
            'pagination' => $pagination
        ];
        
        // Cache'e kaydet (5 dakika TTL)
        if ($useCache && $this->cacheService !== null && $cacheKey !== null) {
            $this->cacheService->set($cacheKey, $result, 300);
        }
        
        return $result;
    }

    /**
     * Tek ürün detayını getirir
     * 
     * @param int $id
     * @return array
     * @throws NotFoundException
     */
    public function getProductById(int $id): array
    {
        // Cache kontrolü
        $cacheKey = null;
        if ($this->cacheService !== null) {
            $cacheKey = "product_{$id}";
            $cached = $this->cacheService->get($cacheKey);
            
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $product = $this->productRepository->findById($id);
        
        if (!$product) {
            throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
        }
        
        // Varyantları ekle
        $variants = $this->variantRepository->getGroupedByType($id);
        $product['variants'] = $variants;
        
        // Cache'e kaydet (10 dakika TTL)
        if ($this->cacheService !== null && $cacheKey !== null) {
            $this->cacheService->set($cacheKey, $product, 600);
        }
        
        return $product;
    }
}

