<?php

namespace App\Repositories;

use PDO;

class LogRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'logs';
    }

    /**
     * Yeni log kaydı oluşturur
     * 
     * @param string $level
     * @param string $message
     * @param array|null $context
     * @param string|null $sessionId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return int Log ID
     */
    public function create(
        string $level,
        string $message,
        ?array $context = null,
        ?string $sessionId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): int {
        $sql = "INSERT INTO {$this->tableName} 
                (level, message, context, session_id, ip_address, user_agent) 
                VALUES (:level, :message, :context, :session_id, :ip_address, :user_agent)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':level', $level, \PDO::PARAM_STR);
        $stmt->bindValue(':message', $message, \PDO::PARAM_STR);
        $stmt->bindValue(':context', $context !== null ? json_encode($context, JSON_UNESCAPED_UNICODE) : null, \PDO::PARAM_STR);
        $stmt->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindValue(':ip_address', $ipAddress, \PDO::PARAM_STR);
        $stmt->bindValue(':user_agent', $userAgent, \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return (int) $this->db->lastInsertId();
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
        // Level filtresi
        if (!empty($filters['level'])) {
            $sql .= " AND level = :level";
            $params[':level'] = $filters['level'];
        }

        // Session ID filtresi
        if (!empty($filters['session_id'])) {
            $sql .= " AND session_id = :session_id";
            $params[':session_id'] = $filters['session_id'];
        }

        // Tarih aralığı filtresi
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
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
        return ['id', 'level', 'created_at'];
    }
}
