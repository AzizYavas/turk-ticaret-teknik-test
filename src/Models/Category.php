<?php

namespace App\Models;

class Category extends BaseModel
{
    protected function getTableName(): string
    {
        return 'categories';
    }

    /**
     * Tüm kategorileri getirir
     * 
     * @return array
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->getTableName()} ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre kategori getirir
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Slug'a göre kategori getirir
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}

