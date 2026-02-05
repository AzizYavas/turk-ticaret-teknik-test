<?php

namespace App\Repositories;

use PDO;

class CouponRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'coupons';
    }

    /**
     * Kupon koduna göre kupon getirir
     * 
     * @param string $code
     * @return array|null
     */
    public function findByCode(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE code = :code AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $code, \PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Kupon kullanım sayısını artırır
     * 
     * @param int $couponId
     * @return bool
     */
    public function incrementUsage(int $couponId): bool
    {
        $sql = "UPDATE {$this->tableName} SET used_count = used_count + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $couponId, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}
