<?php

namespace App\Container;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    /**
     * Sınıfı container'a bind eder
     * 
     * @param string $abstract
     * @param callable|string|null $concrete
     * @return void
     */
    public function bind(string $abstract, $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Singleton olarak bind eder
     * 
     * @param string $abstract
     * @param callable|string|null $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
        
        // İlk çağrıda instance oluştur
        if (!isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $this->resolve($abstract);
        }
    }

    /**
     * Sınıfı resolve eder (oluşturur)
     * 
     * @param string $abstract
     * @return mixed
     * @throws \Exception
     */
    public function resolve(string $abstract)
    {
        // Singleton ise mevcut instance'ı döndür
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Interface veya abstract class ise binding kontrolü yap
        if (interface_exists($abstract) || (class_exists($abstract) && (new \ReflectionClass($abstract))->isAbstract())) {
            if (!isset($this->bindings[$abstract])) {
                throw new \Exception("Cannot resolve interface or abstract class: {$abstract}. Please bind it first.");
            }
            $concrete = $this->bindings[$abstract];
        } else {
            // Binding var mı kontrol et
            $concrete = $this->bindings[$abstract] ?? $abstract;
        }

        // Closure ise çağır
        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } else {
            $instance = $this->build($concrete);
        }

        // Singleton ise kaydet
        if (isset($this->bindings[$abstract]) && $this->isSingleton($abstract)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Sınıfı oluşturur (constructor injection)
     * 
     * @param string $concrete
     * @return object
     * @throws \Exception
     */
    private function build(string $concrete): object
    {
        // Interface veya abstract class ise bind edilmiş concrete class'ı kullan
        if (interface_exists($concrete) || (class_exists($concrete) && (new \ReflectionClass($concrete))->isAbstract())) {
            // Binding var mı kontrol et
            if (isset($this->bindings[$concrete])) {
                $concrete = $this->bindings[$concrete];
                // Closure ise çağır
                if ($concrete instanceof \Closure) {
                    return $concrete($this);
                }
            } else {
                throw new \Exception("Cannot instantiate interface or abstract class: {$concrete}. Please bind it first.");
            }
        }

        $reflection = new \ReflectionClass($concrete);

        // Constructor yoksa direkt oluştur
        if (!$reflection->hasMethod('__construct')) {
            return new $concrete();
        }

        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $dependencies = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();
                $dependencies[] = $this->resolve($dependencyClass);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve dependency: {$parameter->getName()}");
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Singleton olup olmadığını kontrol eder
     * 
     * @param string $abstract
     * @return bool
     */
    private function isSingleton(string $abstract): bool
    {
        // Basit bir kontrol - gerçek uygulamada daha gelişmiş olabilir
        return isset($this->instances[$abstract]);
    }

    /**
     * Container'ı temizler (test için)
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}
