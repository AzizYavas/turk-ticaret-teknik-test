<?php

class Database
{
    private static ?PDO $connection = null;
    
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'turkticaret_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';
    
    /**
     * Veritabanı bağlantısını oluşturur veya mevcut bağlantıyı döndürür
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    self::DB_HOST,
                    self::DB_NAME,
                    self::DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false,
                ];
                
                self::$connection = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
                
            } catch (PDOException $e) {
                throw new PDOException(
                    "Veritabanı bağlantı hatası: " . $e->getMessage(),
                    (int)$e->getCode()
                );
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Bağlantıyı kapatır
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
    
    /**
     * Bağlantının aktif olup olmadığını kontrol eder
     * 
     * @return bool
     */
    public static function isConnected(): bool
    {
        try {
            if (self::$connection !== null) {
                self::$connection->query('SELECT 1');
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        
        return false;
    }
}

