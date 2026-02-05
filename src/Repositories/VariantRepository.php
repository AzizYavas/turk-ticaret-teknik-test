<?php

namespace App\Repositories;

use PDO;

class VariantRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'product_variants';
    }

    /**
     * Ürün ID'sine göre tüm varyantları getirir
     * 
     * @param int $productId
     * @return array
     */
    public function findByProductId(int $productId): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE product_id = :product_id ORDER BY variant_type, variant_value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Varyant ID'sine göre varyant getirir
     * 
     * @param int $variantId
     * @return array|null
     */
    public function findById(int $variantId): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $variantId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ürün ID ve varyant tipi/değerine göre varyant getirir
     * 
     * @param int $productId
     * @param string $variantType
     * @param string $variantValue
     * @return array|null
     */
    public function findByProductAndVariant(int $productId, string $variantType, string $variantValue): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE product_id = :product_id 
                AND variant_type = :variant_type 
                AND variant_value = :variant_value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, \PDO::PARAM_INT);
        $stmt->bindValue(':variant_type', $variantType, \PDO::PARAM_STR);
        $stmt->bindValue(':variant_value', $variantValue, \PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Ürün varyantlarını tip bazında gruplar
     * 
     * @param int $productId
     * @return array
     */
    public function getGroupedByType(int $productId): array
    {
        $variants = $this->findByProductId($productId);
        $grouped = [];
        
        foreach ($variants as $variant) {
            $type = $variant['variant_type'];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = [
                'id' => (int) $variant['id'],
                'value' => $variant['variant_value'],
                'price_modifier' => (float) $variant['price_modifier'],
                'stock' => (int) $variant['stock'],
                'sku' => $variant['sku']
            ];
        }
        
        return $grouped;
    }
}
