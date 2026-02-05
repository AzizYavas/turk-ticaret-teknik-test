<?php

namespace App\Repositories;

use PDO;

abstract class BaseRepository implements RepositoryInterface
{
    protected PDO $db;
    protected string $tableName;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->tableName = $this->getTableName();
    }

    /**
     * Tablo adını döndürür
     * 
     * @return string
     */
    abstract protected function getTableName(): string;

    /**
     * Tüm kayıtları getirir
     * 
     * @param array $filters
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $filters = [], array $sort = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE 1=1";
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
     * ID'ye göre kayıt getirir
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
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
        $sql = "SELECT COUNT(*) as total FROM {$this->tableName} WHERE 1=1";
        $params = [];

        $sql = $this->applyFilters($sql, $filters, $params);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $paramType = is_int($value) ? \PDO::PARAM_INT : (is_bool($value) ? \PDO::PARAM_BOOL : \PDO::PARAM_STR);
            $stmt->bindValue($key, $value, $paramType);
        }
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Filtreleri uygular (alt sınıflar override edebilir)
     * 
     * @param string $sql
     * @param array $filters
     * @param array $params
     * @return string
     */
    protected function applyFilters(string $sql, array $filters, array &$params): string
    {
        return $sql;
    }

    /**
     * Sıralamayı uygular (alt sınıflar override edebilir)
     * 
     * @param string $sql
     * @param array $sort
     * @return string
     */
    protected function applySorting(string $sql, array $sort): string
    {
        if (!empty($sort['field']) && !empty($sort['direction'])) {
            $allowedFields = $this->getAllowedSortFields();
            $allowedDirections = ['ASC', 'DESC'];
            
            if (in_array($sort['field'], $allowedFields) && in_array(strtoupper($sort['direction']), $allowedDirections)) {
                $sql .= " ORDER BY {$sort['field']} " . strtoupper($sort['direction']);
            }
        } else {
            $sql .= " ORDER BY id DESC";
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
        return ['id', 'created_at'];
    }
}
