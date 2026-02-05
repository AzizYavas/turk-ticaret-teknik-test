<?php

namespace App\Repositories;

use PDO;

class ProductRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'products';
    }

    /**
     * Tüm ürünleri getirir (kategori bilgisi ile)
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
                FROM {$this->tableName} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];

        // Filtreleri uygula
        $sql = $this->applyFilters($sql, $filters, $params);
        
        // Sıralamayı uygula
        $sql = $this->applySorting($sql, $sort);
        
        // Limit ve offset ekle
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        
        // Tüm parametreleri bind et
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? \PDO::PARAM_INT : (is_bool($value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR);
            $stmt->bindValue($key, $value, $paramType);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre ürün getirir (kategori bilgisi ile)
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM {$this->tableName} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Toplam kayıt sayısını getirir
     * 
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tableName} p WHERE 1=1";
        $params = [];

        $sql = $this->applyFilters($sql, $filters, $params);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Filtreleri uygular
     * 
     * @param string $sql
     * @param array $filters
     * @param array $params
     * @return string
     */
    protected function applyFilters(string $sql, array $filters, array &$params): string
    {
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
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (p.name LIKE :search_name OR p.description LIKE :search_desc)";
            $params[':search_name'] = $searchTerm;
            $params[':search_desc'] = $searchTerm;
        }

        return $sql;
    }

    /**
     * Sıralamayı uygular
     * 
     * @param string $sql
     * @param array $sort
     * @return string
     */
    protected function applySorting(string $sql, array $sort): string
    {
        $orderBy = "p.created_at DESC";
        
        if (!empty($sort['field']) && !empty($sort['direction'])) {
            $allowedFields = ['name', 'price', 'created_at'];
            $allowedDirections = ['ASC', 'DESC'];
            
            if (in_array($sort['field'], $allowedFields) && in_array(strtoupper($sort['direction']), $allowedDirections)) {
                $orderBy = "p.{$sort['field']} " . strtoupper($sort['direction']);
            }
        }

        $sql .= " ORDER BY {$orderBy}";
        return $sql;
    }

    /**
     * İzin verilen sıralama alanlarını döndürür
     * 
     * @return array
     */
    protected function getAllowedSortFields(): array
    {
        return ['name', 'price', 'created_at'];
    }
}
