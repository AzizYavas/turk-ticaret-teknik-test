<?php

namespace App\Services;

class CacheService
{
    private string $cacheDir;
    private int $defaultTtl; // Varsayılan TTL (saniye cinsinden)

    public function __construct(string $cacheDir = null, int $defaultTtl = 3600)
    {
        // Cache dizinini belirle
        if ($cacheDir === null) {
            $cacheDir = __DIR__ . '/../../cache';
        }
        
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->defaultTtl = $defaultTtl;
        
        // Cache dizinini oluştur
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Cache key'inden dosya yolunu oluşturur
     * 
     * @param string $key
     * @return string
     */
    private function getCacheFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * Cache'den veri getirir
     * 
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $data = file_get_contents($filePath);
        $cacheData = unserialize($data);
        
        // TTL kontrolü
        if ($cacheData['expires_at'] < time()) {
            $this->delete($key);
            return null;
        }
        
        return $cacheData['value'];
    }

    /**
     * Cache'e veri yazar
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $filePath = $this->getCacheFilePath($key);
        
        $ttl = $ttl ?? $this->defaultTtl;
        $expiresAt = time() + $ttl;
        
        $cacheData = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time()
        ];
        
        $data = serialize($cacheData);
        
        return file_put_contents($filePath, $data, LOCK_EX) !== false;
    }

    /**
     * Cache'den veri siler
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }

    /**
     * Cache'i temizler (pattern ile)
     * 
     * @param string $pattern
     * @return int Silinen dosya sayısı
     */
    public function clear(string $pattern = '*'): int
    {
        $deleted = 0;
        $files = glob($this->cacheDir . '/' . $pattern . '.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }

    /**
     * Cache key'inin var olup olmadığını kontrol eder
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $data = file_get_contents($filePath);
        $cacheData = unserialize($data);
        
        // TTL kontrolü
        if ($cacheData['expires_at'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }

    /**
     * Cache key'ini oluşturur (parametrelerden)
     * 
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        if (empty($params)) {
            return $prefix;
        }
        
        ksort($params); // Tutarlılık için sırala
        $paramsString = http_build_query($params);
        return $prefix . '_' . md5($paramsString);
    }

    /**
     * Süresi dolmuş cache dosyalarını temizler
     * 
     * @return int Temizlenen dosya sayısı
     */
    public function cleanExpired(): int
    {
        $cleaned = 0;
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cacheData = unserialize($data);
            
            if ($cacheData['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}
