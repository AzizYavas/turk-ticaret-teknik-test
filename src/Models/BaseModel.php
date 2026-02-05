<?php

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        require_once __DIR__ . '/../../config/database.php';
        $this->db = \Database::getConnection();
    }

    /**
     * Tablo adını döndürür (her model kendi tablo adını tanımlamalı)
     * 
     * @return string
     */
    abstract protected function getTableName(): string;
}

