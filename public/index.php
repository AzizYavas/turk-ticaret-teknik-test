<?php

// Hata raporlama (geliştirme ortamı için)
if (getenv('APP_ENV') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        if (strncmp('App\\', $class, 4) !== 0) {
            return;
        }
        $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}

// Config
require_once __DIR__ . '/../config/database.php';

// Container ve Service Provider
use App\Container\Container;
use App\Container\ServiceProvider;
use App\Helpers\Router;
use App\Services\RateLimiterService;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\CartController;
use App\Controllers\FavoriteController;
use App\Controllers\CouponController;
use App\Controllers\RecentlyViewedController;

// DI Container'ı başlat
$container = new Container();
$serviceProvider = new ServiceProvider($container);
$serviceProvider->register();

$router = new Router();

// Ürün route'ları
$router->get('/api/products', ProductController::class, 'index');
$router->get('/api/products/{id}', ProductController::class, 'show');

// Kategori route'ları
$router->get('/api/categories', CategoryController::class, 'index');

// Sepet route'ları
$router->get('/api/cart', CartController::class, 'index');
$router->post('/api/cart', CartController::class, 'add');
$router->post('/api/cart/merge', CartController::class, 'merge');
$router->put('/api/cart/{productId}', CartController::class, 'update');
$router->delete('/api/cart/{productId}', CartController::class, 'remove');
$router->delete('/api/cart', CartController::class, 'clear');

// Favori route'ları
$router->get('/api/favorites', FavoriteController::class, 'index');
$router->post('/api/favorites', FavoriteController::class, 'add');
$router->delete('/api/favorites/{productId}', FavoriteController::class, 'remove');
$router->post('/api/favorites/{productId}/add-to-cart', FavoriteController::class, 'addToCart');

// Kupon route'ları
$router->post('/api/coupons/validate', CouponController::class, 'validate');
$router->post('/api/coupons/apply', CouponController::class, 'apply');
$router->delete('/api/coupons', CouponController::class, 'remove');

// Son görüntülenen ürünler route'ları
$router->get('/api/recently-viewed', RecentlyViewedController::class, 'index');

// Path'i parse et
$method = $_SERVER['REQUEST_METHOD'];

// PUT ve DELETE metodları için _method parametresini kontrol et
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
} elseif ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Base path'i çıkar
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
} elseif ($path !== '/' && $path !== '') {
    // Htaccess çalışmadıysa proje base path'ini çıkar
    $projectBasePath = dirname($scriptDir);
    if ($projectBasePath !== '/' && $projectBasePath !== '\\' && $projectBasePath !== '.' && strpos($path, $projectBasePath) === 0) {
        $path = substr($path, strlen($projectBasePath));
    }
}

// Path'i normalize et
$path = '/' . trim($path, '/');

// Rate Limiting kontrolü (root path hariç)
if ($path !== '/') {
    $rateLimiter = new RateLimiterService();
    $endpointLimits = $rateLimiter->getEndpointLimits($path);
    $rateLimitCheck = $rateLimiter->checkLimit($path, $endpointLimits['limit'], $endpointLimits['window']);
    
    if (!$rateLimitCheck['allowed']) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('X-RateLimit-Limit: ' . $endpointLimits['limit']);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $rateLimitCheck['reset_at']);
        header('Retry-After: ' . ($rateLimitCheck['reset_at'] - time()));
        
        \App\Helpers\ResponseHelper::error(
            'Çok fazla istek gönderildi. Lütfen daha sonra tekrar deneyin.',
            429,
            'RATE_LIMIT_EXCEEDED'
        );
    }
    
    // Rate limit header'larını ekle
    header('X-RateLimit-Limit: ' . $endpointLimits['limit']);
    header('X-RateLimit-Remaining: ' . $rateLimitCheck['remaining']);
    header('X-RateLimit-Reset: ' . $rateLimitCheck['reset_at']);
    
    // Eski kayıtları temizle (her 100 istekte bir)
    if (rand(1, 100) === 1) {
        $rateLimiter->cleanExpired();
    }
}

// Root path için özel kontrol
if ($path === '/') {
    \App\Helpers\ResponseHelper::success([
        'message' => 'E-ticaret API',
        'version' => '1.0',
        'endpoints' => [
            'GET /api/products' => 'Ürün listesi',
            'GET /api/products/{id}' => 'Ürün detayı',
            'GET /api/categories' => 'Kategori listesi',
            'GET /api/cart' => 'Sepeti görüntüle',
            'POST /api/cart' => 'Sepete ürün ekle',
            'POST /api/cart/merge' => 'Başka session\'dan sepeti birleştir',
            'PUT /api/cart/{productId}' => 'Ürün miktarını güncelle',
            'DELETE /api/cart/{productId}' => 'Sepetten ürün çıkar',
            'DELETE /api/cart' => 'Sepeti temizle',
            'GET /api/favorites' => 'Favori listesini görüntüle',
            'POST /api/favorites' => 'Favorilere ürün ekle',
            'DELETE /api/favorites/{productId}' => 'Favorilerden ürün çıkar',
            'POST /api/favorites/{productId}/add-to-cart' => 'Favori ürünü sepete ekle',
            'POST /api/coupons/validate' => 'Kupon doğrula',
            'POST /api/coupons/apply' => 'Kuponu sepete uygula',
            'DELETE /api/coupons' => 'Kuponu kaldır',
            'GET /api/recently-viewed' => 'Son görüntülenen ürünler'
        ]
    ], 'API başarıyla çalışıyor');
}

// Route'u çalıştır
$router->dispatch($method, $path, $container);
