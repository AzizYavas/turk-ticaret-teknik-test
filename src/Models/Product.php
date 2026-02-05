<?php

namespace App\Models;

class Product extends BaseModel
{
    protected function getTableName(): string
    {
        return 'products';
    }

    /**
     * Tüm ürünleri getirir
     * 
     * @param array $filters
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $filters = [], array $sort = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->getTableName()} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        // Kategori filtresi
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        // Fiyat aralığı filtresi
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        // Arama filtresi (isim ve açıklama)
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        // Sıralama
        $orderBy = "p.created_at DESC";
        if (!empty($sort['field']) && !empty($sort['direction'])) {
            $allowedFields = ['name', 'price', 'created_at'];
            $allowedDirections = ['ASC', 'DESC'];
            
            if (in_array($sort['field'], $allowedFields) && in_array(strtoupper($sort['direction']), $allowedDirections)) {
                $orderBy = "p.{$sort['field']} " . strtoupper($sort['direction']);
            }
        }
        
        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Toplam kayıt sayısını getirir
     * 
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->getTableName()} p WHERE 1=1";
        $params = [];
        
        // Kategori filtresi
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        // Fiyat aralığı filtresi
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        // Arama filtresi
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * ID'ye göre ürün getirir
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->getTableName()} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}

