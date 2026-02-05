<?php

namespace App\Repositories;

use PDO;

class RecentlyViewedRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'recently_viewed';
    }

    /**
     * Yeni görüntüleme kaydı oluşturur veya günceller
     * 
     * @param string $sessionId
     * @param int $productId
     * @return bool
     */
    public function addOrUpdate(string $sessionId, int $productId): bool
    {
        // Önce var mı kontrol et
        $existing = $this->findBySessionAndProduct($sessionId, $productId);
        
        if ($existing) {
            // Varsa sadece viewed_at'i güncelle
            $sql = "UPDATE {$this->tableName} SET viewed_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $existing['id'], \PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Yoksa yeni kayıt oluştur
            $sql = "INSERT INTO {$this->tableName} (session_id, product_id) VALUES (:session_id, :product_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
            $stmt->bindValue(':product_id', $productId, \PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    /**
     * Session ve ürün ID'sine göre kayıt getirir
     * 
     * @param string $sessionId
     * @param int $productId
     * @return array|null
     */
    public function findBySessionAndProduct(string $sessionId, int $productId): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE session_id = :session_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindValue(':product_id', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Session'a göre son görüntülenen ürünleri getirir
     * 
     * @param string $sessionId
     * @param int $limit
     * @return array
     */
    public function findBySession(string $sessionId, int $limit = 10): array
    {
        $sql = "SELECT rv.*, p.name, p.price, p.image_url, p.description, c.name as category_name
                FROM {$this->tableName} rv
                INNER JOIN products p ON rv.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE rv.session_id = :session_id
                ORDER BY rv.viewed_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Session'a göre eski kayıtları temizler (limit aşımı için)
     * 
     * @param string $sessionId
     * @param int $keepCount
     * @return bool
     */
    public function cleanOldRecords(string $sessionId, int $keepCount = 20): bool
    {
        // Önce tutulacak ID'leri al
        $selectSql = "SELECT id FROM {$this->tableName} 
                      WHERE session_id = :session_id 
                      ORDER BY viewed_at DESC 
                      LIMIT :keep_count";
        
        $selectStmt = $this->db->prepare($selectSql);
        $selectStmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $selectStmt->bindValue(':keep_count', $keepCount, \PDO::PARAM_INT);
        $selectStmt->execute();
        
        $keepIds = $selectStmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (empty($keepIds)) {
            return true; // Silinecek kayıt yok
        }
        
        // Placeholder'ları oluştur
        $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
        
        // Eski kayıtları sil
        $deleteSql = "DELETE FROM {$this->tableName} 
                      WHERE session_id = :session_id 
                      AND id NOT IN ({$placeholders})";
        
        $deleteStmt = $this->db->prepare($deleteSql);
        $deleteStmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        
        foreach ($keepIds as $index => $id) {
            $deleteStmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }
        
        return $deleteStmt->execute();
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
        // Session ID filtresi
        if (!empty($filters['session_id'])) {
            $sql .= " AND session_id = :session_id";
            $params[':session_id'] = $filters['session_id'];
        }

        return $sql;
    }

    /**
     * İzin verilen sıralama alanlarını döndürür
     * 
     * @return array
     */
    protected function getAllowedSortFields(): array
    {
        return ['id', 'viewed_at', 'product_id'];
    }
}
