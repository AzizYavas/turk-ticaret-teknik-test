<?php

namespace App\Repositories;

use PDO;

class CategoryRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'categories';
    }

    /**
     * Tüm kategorileri getirir
     * 
     * @param array $filters
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $filters = [], array $sort = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Slug'a göre kategori getirir
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
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
        return $sql . " ORDER BY name ASC";
    }
}
