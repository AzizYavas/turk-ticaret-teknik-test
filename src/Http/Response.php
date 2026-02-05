<?php

namespace App\Http;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private $data = null;

    /**
     * Status code ayarlar
     * 
     * @param int $code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Header ekler
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Data ayarlar
     * 
     * @param mixed $data
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * JSON response döndürür
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public function json($data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->setData($data);
        $this->send();
    }

    /**
     * Response'u gönderir
     * 
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->data !== null) {
            echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        exit;
    }
}
