<?php

namespace App\Services;

use App\Repositories\LogRepository;

class LogService
{
    private LogRepository $logRepository;

    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Request bilgilerini toplar
     * 
     * @return array
     */
    private function getRequestInfo(): array
    {
        $sessionId = null;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
        } elseif (session_status() === PHP_SESSION_NONE) {
            session_start();
            $sessionId = session_id();
        }

        return [
            'session_id' => $sessionId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
    }

    /**
     * Info seviyesinde log kaydeder
     * 
     * @param string $message
     * @param array|null $context
     * @return int Log ID
     */
    public function info(string $message, ?array $context = null): int
    {
        $requestInfo = $this->getRequestInfo();
        return $this->logRepository->create(
            'info',
            $message,
            $context,
            $requestInfo['session_id'],
            $requestInfo['ip_address'],
            $requestInfo['user_agent']
        );
    }

    /**
     * Warning seviyesinde log kaydeder
     * 
     * @param string $message
     * @param array|null $context
     * @return int Log ID
     */
    public function warning(string $message, ?array $context = null): int
    {
        $requestInfo = $this->getRequestInfo();
        return $this->logRepository->create(
            'warning',
            $message,
            $context,
            $requestInfo['session_id'],
            $requestInfo['ip_address'],
            $requestInfo['user_agent']
        );
    }

    /**
     * Error seviyesinde log kaydeder
     * 
     * @param string $message
     * @param array|null $context
     * @return int Log ID
     */
    public function error(string $message, ?array $context = null): int
    {
        $requestInfo = $this->getRequestInfo();
        return $this->logRepository->create(
            'error',
            $message,
            $context,
            $requestInfo['session_id'],
            $requestInfo['ip_address'],
            $requestInfo['user_agent']
        );
    }

    /**
     * API isteği log kaydeder
     * 
     * @param string $method
     * @param string $path
     * @param int $statusCode
     * @param array|null $context
     * @return int Log ID
     */
    public function logRequest(string $method, string $path, int $statusCode, ?array $context = null): int
    {
        $level = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');
        $message = "API Request: {$method} {$path} - Status: {$statusCode}";
        
        $logContext = array_merge($context ?? [], [
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode
        ]);

        $requestInfo = $this->getRequestInfo();
        return $this->logRepository->create(
            $level,
            $message,
            $logContext,
            $requestInfo['session_id'],
            $requestInfo['ip_address'],
            $requestInfo['user_agent']
        );
    }
}
