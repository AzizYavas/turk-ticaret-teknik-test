<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Başarılı JSON yanıtı döndürür
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    public static function success($data = null, string $message = 'İşlem başarılı', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Hata JSON yanıtı döndürür
     * 
     * @param string $message
     * @param int $statusCode
     * @param string|null $errorCode
     * @return void
     */
    public static function error(string $message = 'Bir hata oluştu', int $statusCode = 400, ?string $errorCode = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Error code belirlenmemişse status code'a göre oluştur
        if ($errorCode === null) {
            $errorCode = self::getErrorCodeByStatusCode($statusCode);
        }
        
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Status code'a göre error code döndürür
     * 
     * @param int $statusCode
     * @return string
     */
    private static function getErrorCodeByStatusCode(int $statusCode): string
    {
        $errorCodes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'INTERNAL_SERVER_ERROR'
        ];
        
        return $errorCodes[$statusCode] ?? 'UNKNOWN_ERROR';
    }

    /**
     * Pagination ile başarılı JSON yanıtı döndürür
     * 
     * @param mixed $data
     * @param array $pagination
     * @param string $message
     * @return void
     */
    public static function successWithPagination($data, array $pagination, string $message = 'İşlem başarılı'): void
    {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => $pagination,
            'message' => $message
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

