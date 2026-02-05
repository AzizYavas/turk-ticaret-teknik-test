<?php

namespace App\Services;

class RateLimiterService
{
    private const DEFAULT_LIMIT = 100; // Varsayılan: 100 istek
    private const DEFAULT_WINDOW = 60; // Varsayılan: 60 saniye (1 dakika)
    private const SESSION_KEY = 'rate_limiter';

    /**
     * IP adresini getirir
     * 
     * @return string
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }

    /**
     * Rate limit kontrolü yapar
     * 
     * @param string $endpoint
     * @param int $limit
     * @param int $window
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
     */
    public function checkLimit(string $endpoint, int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $ip = $this->getClientIp();
        $key = $this->getKey($ip, $endpoint);
        $now = time();

        // Rate limit verilerini al
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $rateLimitData = $_SESSION[self::SESSION_KEY][$key] ?? null;

        // İlk istek veya süre dolmuş
        if ($rateLimitData === null || $rateLimitData['reset_at'] < $now) {
            $rateLimitData = [
                'count' => 1,
                'reset_at' => $now + $window,
                'first_request' => $now
            ];
            $_SESSION[self::SESSION_KEY][$key] = $rateLimitData;
            
            return [
                'allowed' => true,
                'remaining' => $limit - 1,
                'reset_at' => $rateLimitData['reset_at']
            ];
        }

        // Limit aşılmış
        if ($rateLimitData['count'] >= $limit) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => $rateLimitData['reset_at']
            ];
        }

        // İsteği say
        $rateLimitData['count']++;
        $_SESSION[self::SESSION_KEY][$key] = $rateLimitData;

        return [
            'allowed' => true,
            'remaining' => $limit - $rateLimitData['count'],
            'reset_at' => $rateLimitData['reset_at']
        ];
    }

    /**
     * Rate limit key'ini oluşturur
     * 
     * @param string $ip
     * @param string $endpoint
     * @return string
     */
    private function getKey(string $ip, string $endpoint): string
    {
        return md5($ip . '_' . $endpoint);
    }

    /**
     * Eski rate limit kayıtlarını temizler
     * 
     * @return void
     */
    public function cleanExpired(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $now = time();
        foreach ($_SESSION[self::SESSION_KEY] as $key => $data) {
            if ($data['reset_at'] < $now) {
                unset($_SESSION[self::SESSION_KEY][$key]);
            }
        }
    }

    /**
     * Endpoint'e özel limit ayarları
     * 
     * @param string $endpoint
     * @return array ['limit' => int, 'window' => int]
     */
    public function getEndpointLimits(string $endpoint): array
    {
        // Endpoint bazlı özel limitler
        $limits = [
            '/api/coupons/apply' => ['limit' => 10, 'window' => 60], // 10 istek/dakika
            '/api/cart' => ['limit' => 50, 'window' => 60], // 50 istek/dakika
            '/api/products' => ['limit' => 200, 'window' => 60], // 200 istek/dakika
        ];

        // Endpoint pattern eşleştirme
        foreach ($limits as $pattern => $config) {
            if (strpos($endpoint, $pattern) === 0) {
                return $config;
            }
        }

        // Varsayılan limit
        return ['limit' => self::DEFAULT_LIMIT, 'window' => self::DEFAULT_WINDOW];
    }
}
