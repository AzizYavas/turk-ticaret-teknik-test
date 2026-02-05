<?php

namespace App\Http;

class Request
{
    private array $get;
    private array $post;
    private array $body;
    private array $server;

    public function __construct()
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->server = $_SERVER ?? [];
        $this->body = $this->parseBody();
    }

    /**
     * Request body'yi parse eder
     * 
     * @return array
     */
    private function parseBody(): array
    {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            return [];
        }

        $data = json_decode($rawInput, true);
        return is_array($data) ? $data : [];
    }

    /**
     * GET parametresini alır ve temizler
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->sanitize($this->get[$key] ?? $default);
    }

    /**
     * POST parametresini alır ve temizler
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function post(string $key, $default = null)
    {
        return $this->sanitize($this->post[$key] ?? $default);
    }

    /**
     * Body parametresini alır ve temizler
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function body(string $key, $default = null)
    {
        return $this->sanitize($this->body[$key] ?? $default);
    }

    /**
     * Tüm GET parametrelerini döndürür
     * 
     * @return array
     */
    public function allGet(): array
    {
        return array_map([$this, 'sanitize'], $this->get);
    }

    /**
     * Tüm body parametrelerini döndürür
     * 
     * @return array
     */
    public function allBody(): array
    {
        return array_map([$this, 'sanitize'], $this->body);
    }

    /**
     * Request method'unu döndürür
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Request URI'yi döndürür
     * 
     * @return string
     */
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Değeri temizler ve sanitize eder
     * 
     * @param mixed $value
     * @return mixed
     */
    private function sanitize($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        if (is_string($value)) {
            // HTML tag'lerini temizle
            $value = strip_tags($value);
            // Özel karakterleri escape et
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            // Başta ve sonda boşlukları temizle
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Integer değer döndürür
     * 
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => $default]]) ?: $default;
    }

    /**
     * Float değer döndürür
     * 
     * @param string $key
     * @param float $default
     * @return float
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);
        return filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => $default]]) ?: $default;
    }

    /**
     * String değer döndürür
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        return is_string($value) ? $value : $default;
    }

    /**
     * Body'den integer değer döndürür
     * 
     * @param string $key
     * @param int $default
     * @return int
     */
    public function bodyInt(string $key, int $default = 0): int
    {
        $value = $this->body($key, $default);
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => $default]]) ?: $default;
    }

    /**
     * Body'den float değer döndürür
     * 
     * @param string $key
     * @param float $default
     * @return float
     */
    public function bodyFloat(string $key, float $default = 0.0): float
    {
        $value = $this->body($key, $default);
        return filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => $default]]) ?: $default;
    }

    /**
     * Body'den string değer döndürür
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function bodyString(string $key, string $default = ''): string
    {
        $value = $this->body($key, $default);
        return is_string($value) ? $value : $default;
    }

    /**
     * Parametrenin var olup olmadığını kontrol eder
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]) || isset($this->body[$key]);
    }

    /**
     * Body'de parametrenin var olup olmadığını kontrol eder
     * 
     * @param string $key
     * @return bool
     */
    public function hasBody(string $key): bool
    {
        return isset($this->body[$key]);
    }
}
