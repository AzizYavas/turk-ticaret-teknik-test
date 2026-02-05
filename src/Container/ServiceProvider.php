<?php

namespace App\Container;

use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CouponRepository;
use App\Repositories\LogRepository;
use App\Repositories\RecentlyViewedRepository;
use App\Repositories\VariantRepository;
use App\Services\ProductService;
use App\Services\ProductServiceInterface;
use App\Services\CategoryService;
use App\Services\CategoryServiceInterface;
use App\Services\CartService;
use App\Services\FavoriteService;
use App\Services\CouponService;
use App\Services\LogService;
use App\Services\RecentlyViewedService;
use App\Services\CacheService;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\CartController;
use App\Controllers\FavoriteController;
use App\Controllers\CouponController;
use App\Controllers\RecentlyViewedController;

class ServiceProvider
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Tüm servisleri register eder
     * 
     * @return void
     */
    public function register(): void
    {
        // Database connection singleton
        $this->container->singleton(\PDO::class, function () {
            require_once __DIR__ . '/../../config/database.php';
            return \Database::getConnection();
        });

        // Repositories
        $this->container->bind(ProductRepository::class, function (Container $container) {
            return new ProductRepository($container->resolve(\PDO::class));
        });

        $this->container->bind(CategoryRepository::class, function (Container $container) {
            return new CategoryRepository($container->resolve(\PDO::class));
        });

        $this->container->bind(CouponRepository::class, function (Container $container) {
            return new CouponRepository($container->resolve(\PDO::class));
        });

        $this->container->bind(LogRepository::class, function (Container $container) {
            return new LogRepository($container->resolve(\PDO::class));
        });

        $this->container->bind(RecentlyViewedRepository::class, function (Container $container) {
            return new RecentlyViewedRepository($container->resolve(\PDO::class));
        });

        $this->container->bind(VariantRepository::class, function (Container $container) {
            return new VariantRepository($container->resolve(\PDO::class));
        });

        // Services - Interface'leri concrete class'lara bind et
        $this->container->bind(ProductServiceInterface::class, function (Container $container) {
            return new ProductService(
                $container->resolve(ProductRepository::class),
                $container->resolve(VariantRepository::class),
                $container->resolve(CacheService::class)
            );
        });
        
        $this->container->bind(ProductService::class, function (Container $container) {
            return new ProductService(
                $container->resolve(ProductRepository::class),
                $container->resolve(VariantRepository::class),
                $container->resolve(CacheService::class)
            );
        });

        $this->container->bind(CategoryServiceInterface::class, function (Container $container) {
            return new CategoryService(
                $container->resolve(CategoryRepository::class),
                $container->resolve(CacheService::class)
            );
        });
        
        $this->container->bind(CategoryService::class, function (Container $container) {
            return new CategoryService(
                $container->resolve(CategoryRepository::class),
                $container->resolve(CacheService::class)
            );
        });

        $this->container->bind(CartService::class, function (Container $container) {
            return new CartService(
                $container->resolve(ProductRepository::class),
                $container->resolve(VariantRepository::class),
                $container->resolve(CouponService::class)
            );
        });

        $this->container->bind(FavoriteService::class, function (Container $container) {
            return new FavoriteService(
                $container->resolve(ProductRepository::class),
                $container->resolve(CartService::class)
            );
        });

        $this->container->bind(CouponService::class, function (Container $container) {
            return new CouponService($container->resolve(CouponRepository::class));
        });

        $this->container->bind(LogService::class, function (Container $container) {
            return new LogService($container->resolve(LogRepository::class));
        });

        $this->container->bind(RecentlyViewedService::class, function (Container $container) {
            return new RecentlyViewedService($container->resolve(RecentlyViewedRepository::class));
        });

        $this->container->singleton(CacheService::class, function () {
            return new CacheService();
        });

        // Request singleton (her request için aynı instance)
        $this->container->singleton(\App\Http\Request::class, function () {
            return new \App\Http\Request();
        });

        // Controllers
        $this->container->bind(ProductController::class, function (Container $container) {
            return new ProductController(
                $container->resolve(ProductServiceInterface::class),
                $container->resolve(RecentlyViewedService::class),
                $container->resolve(\App\Http\Request::class)
            );
        });

        $this->container->bind(CategoryController::class, function (Container $container) {
            return new CategoryController(
                $container->resolve(CategoryServiceInterface::class)
            );
        });

        $this->container->bind(CartController::class, function (Container $container) {
            return new CartController(
                $container->resolve(CartService::class),
                $container->resolve(LogService::class),
                $container->resolve(\App\Http\Request::class)
            );
        });

        $this->container->bind(FavoriteController::class, function (Container $container) {
            return new FavoriteController(
                $container->resolve(FavoriteService::class),
                $container->resolve(\App\Http\Request::class)
            );
        });

        $this->container->bind(CouponController::class, function (Container $container) {
            return new CouponController(
                $container->resolve(CouponService::class),
                $container->resolve(CartService::class),
                $container->resolve(LogService::class),
                $container->resolve(\App\Http\Request::class)
            );
        });

        $this->container->bind(RecentlyViewedController::class, function (Container $container) {
            return new RecentlyViewedController(
                $container->resolve(RecentlyViewedService::class)
            );
        });
    }
}
