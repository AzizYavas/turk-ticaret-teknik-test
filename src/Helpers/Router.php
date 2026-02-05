<?php

namespace App\Helpers;

class Router
{
    private array $routes = [];

    /**
     * GET route ekler
     * 
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * POST route ekler
     * 
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * PUT route ekler
     * 
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public function put(string $path, string $controller, string $method): void
    {
        $this->routes['PUT'][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * DELETE route ekler
     * 
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public function delete(string $path, string $controller, string $method): void
    {
        $this->routes['DELETE'][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * Route'u çalıştırır
     * 
     * @param string $method
     * @param string $path
     * @param \App\Container\Container|null $container
     * @return void
     */
    public function dispatch(string $method, string $path, ?\App\Container\Container $container = null): void
    {
        // Path'i temizle
        $path = parse_url($path, PHP_URL_PATH);
        $path = rtrim($path, '/') ?: '/';
        
        // Path başında / yoksa ekle
        if ($path !== '/' && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Route'u bul
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            \App\Helpers\ResponseHelper::error('Endpoint bulunamadı', 404, 'ENDPOINT_NOT_FOUND');
        }
        
        // Controller'ı yükle
        $controllerClass = $route['controller'];
        $methodName = $route['method'];
        $params = $route['params'] ?? [];
        
        if (!class_exists($controllerClass)) {
            \App\Helpers\ResponseHelper::error('Controller bulunamadı', 500, 'CONTROLLER_NOT_FOUND');
        }
        
        // DI Container varsa kullan, yoksa direkt oluştur
        if ($container !== null) {
            try {
                $controller = $container->resolve($controllerClass);
            } catch (\Exception $e) {
                // Container resolve edemezse direkt oluştur
                $controller = new $controllerClass();
            }
        } else {
            $controller = new $controllerClass();
        }
        
        if (!method_exists($controller, $methodName)) {
            \App\Helpers\ResponseHelper::error('Method bulunamadı', 500, 'METHOD_NOT_FOUND');
        }
        
        // Method'u çağır
        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Route'u bulur (parametreli route'ları destekler)
     * 
     * @param string $method
     * @param string $path
     * @return array|null
     */
    private function findRoute(string $method, string $path): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        
        // Tam eşleşme kontrolü
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }
        
        // Parametreli route kontrolü
        foreach ($this->routes[$method] as $routePath => $route) {
            $pattern = '#^' . preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath) . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // İlk eleman tam eşleşme
                
                // Parametre isimlerini al
                preg_match_all('/\{(\w+)\}/', $routePath, $paramNames);
                $paramNames = $paramNames[1];
                
                // Parametreleri eşleştir
                $params = [];
                foreach ($paramNames as $index => $name) {
                    $params[] = is_numeric($matches[$index]) ? (int) $matches[$index] : $matches[$index];
                }
                
                $route['params'] = $params;
                return $route;
            }
        }
        
        return null;
    }
}

