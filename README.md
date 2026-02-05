# Türk Ticaret E-Ticaret API

## 📋 İçindekiler

- [Proje Açıklaması](#-proje-açıklaması)
- [Kurulum Adımları](#-kurulum-adımları)
- [Veritabanı Kurulumu](#-veritabanı-kurulumu)
- [API Kullanım Örnekleri](#-api-kullanım-örnekleri)
- [Postman Collection Kullanımı](#-postman-collection-kullanımı)
- [Teknik Kararlar ve Gerekçeleri](#️-teknik-kararlar-ve-gerekçeleri)

## 🎯 Proje Açıklaması

Bu proje, e-ticaret işlemleri için geliştirilmiş bir REST API'dir. Aşağıdaki özellikleri içermektedir:

### Temel Özellikler

- ✅ **Ürün Yönetimi**: Ürün listeleme, detay görüntüleme, arama, filtreleme, sıralama
- ✅ **Kategori Yönetimi**: Kategori listeleme
- ✅ **Sepet İşlemleri**: Sepete ekleme, çıkarma, miktar güncelleme, sepet görüntüleme, temizleme
- ✅ **Favori Ürünler**: Favorilere ekleme, çıkarma, listeleme, favoriden sepete ekleme
- ✅ **Kupon Sistemi**: Kupon doğrulama, uygulama, kaldırma (yüzdelik/sabit indirim, minimum sepet tutarı)
- ✅ **Stok Kontrolü**: Sepete eklerken ve miktar güncellerken stok kontrolü
- ✅ **Ürün Varyantları**: Renk, beden gibi varyant desteği
- ✅ **Son Görüntülenen Ürünler**: Kullanıcının son baktığı ürünler
- ✅ **Rate Limiting**: API isteklerini sınırlama
- ✅ **Caching**: Sık kullanılan verileri cache'leme
- ✅ **Logging**: İşlem logları tutma
- ✅ **Session Bazlı Sepet Birleştirme**: Farklı session'ların sepetlerini birleştirme

### Mimari Yapı

Proje, modern yazılım geliştirme prensipleri kullanılarak geliştirilmiştir:

```
MVC + Repository Pattern + Service Layer + Dependency Injection
├── Controllers/      → HTTP isteklerini yönetir
├── Services/         → İş mantığı (business logic)
├── Repositories/    → Veritabanı işlemleri
├── Models/          → Veri modelleri
├── Helpers/         → Yardımcı sınıflar
├── Container/       → Dependency Injection Container
└── Exceptions/      → Özel exception sınıfları
```

## 🚀 Kurulum Adımları

### Gereksinimler

- PHP 8.0 veya üzeri
- MySQL 5.7+ veya MariaDB 10.3+
- Apache/Nginx web sunucusu
- Composer (opsiyonel, otomatik autoload için)

### Adım 1: Projeyi İndirin

```bash
# Git ile klonlayın
git clone <repository-url> turkticaret_test
cd turkticaret_test

# Veya ZIP olarak indirip açın
```

### Adım 2: Composer Bağımlılıklarını Yükleyin (Opsiyonel)

```bash
composer install
```

**Not:** Composer yüklü değilse, proje otomatik autoloader kullanır. Composer gerekli değildir.

### Adım 3: Web Sunucusu Yapılandırması

#### Apache ile:

1. `.htaccess` dosyası zaten mevcut (ana dizinde)
2. Apache'de `mod_rewrite` modülünün aktif olduğundan emin olun
3. Document Root'u `public` klasörüne yönlendirin veya proje URL'sini `http://localhost/turkticaret_test/public/` olarak kullanın

#### Nginx ile:

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/turkticaret_test/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Adım 4: Veritabanı Yapılandırması

`config/database.php` dosyasını düzenleyin:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'turkticaret_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');
```

## 💾 Veritabanı Kurulumu

### Adım 1: Veritabanını Oluşturun

MySQL/MariaDB'ye bağlanın ve veritabanını oluşturun:

```sql
CREATE DATABASE turkticaret_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Adım 2: Şemayı İçe Aktarın

```bash
# Komut satırından:
mysql -u root -p turkticaret_db < sql/schema.sql

# Veya phpMyAdmin'den:
# 1. turkticaret_db veritabanını seçin
# 2. "İçe Aktar" sekmesine gidin
# 3. sql/schema.sql dosyasını seçin ve içe aktarın
```

### Adım 3: Test Verilerini Yükleyin (Opsiyonel)

```bash
# Komut satırından:
mysql -u root -p turkticaret_db < sql/seed.sql

# Veya phpMyAdmin'den:
# sql/seed.sql dosyasını içe aktarın
```

### Veritabanı Yapısı

Proje aşağıdaki tabloları içerir:

- `categories` - Ürün kategorileri
- `products` - Ürünler
- `product_variants` - Ürün varyantları (renk, beden vb.)
- `coupons` - Kuponlar
- `favorites` - Favori ürünler
- `recently_viewed` - Son görüntülenen ürünler
- `logs` - İşlem logları

## 📡 API Kullanım Örnekleri

### Base URL

```
http://localhost:8080/turkticaret_test/public
```

veya

```
http://localhost/turkticaret_test/public
```

### Response Formatı

#### Başarılı Response:
```json
{
    "success": true,
    "data": { ... },
    "message": "İşlem başarılı"
}
```

#### Hata Response:
```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Hata mesajı"
    }
}
```

### Örnek 1: Ürün Listesi

```bash
# Basit liste
GET /api/products

# Sayfalama ile
GET /api/products?page=1&limit=10

# Arama ile
GET /api/products?search=iphone

# Filtreleme ile
GET /api/products?category_id=1&min_price=100&max_price=1000

# Sıralama ile
GET /api/products?sort=price_asc
GET /api/products?sort=price_desc
GET /api/products?sort=name_asc
GET /api/products?sort=name_desc
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "iPhone 15 Pro",
            "price": 49999.99,
            "stock": 50,
            "category_name": "Elektronik"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 100,
        "total_pages": 10
    },
    "message": "Ürünler başarıyla getirildi"
}
```

### Örnek 2: Ürün Detayı

```bash
GET /api/products/1
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "iPhone 15 Pro",
        "price": 49999.99,
        "stock": 50,
        "description": "Apple'ın en yeni telefonu",
        "variants": {
            "renk": [
                {
                    "id": 1,
                    "value": "Siyah",
                    "price_modifier": 0,
                    "stock": 20
                }
            ],
            "beden": [
                {
                    "id": 2,
                    "value": "128GB",
                    "price_modifier": 0,
                    "stock": 15
                }
            ]
        }
    },
    "message": "Ürün detayı başarıyla getirildi"
}
```

### Örnek 3: Sepete Ürün Ekleme

```bash
POST /api/cart
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2,
    "variant_id": 1  // Opsiyonel
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "product_id": 1,
                "variant_id": 1,
                "product": {
                    "id": 1,
                    "name": "iPhone 15 Pro",
                    "price": 49999.99,
                    "stock": 50
                },
                "quantity": 2,
                "subtotal": 99999.98
            }
        ],
        "total_items": 1,
        "total_quantity": 2,
        "total_amount": 99999.98,
        "coupon": null,
        "discount": 0,
        "total_after_discount": 99999.98
    },
    "message": "Ürün sepete eklendi"
}
```

### Örnek 4: Kupon Uygulama

```bash
POST /api/coupons/apply
Content-Type: application/json

{
    "code": "INDIRIM10"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "cart": { ... },
        "coupon": {
            "id": 1,
            "code": "INDIRIM10",
            "type": "percentage",
            "value": 10,
            "discount": 9999.99
        },
        "discount": 9999.99,
        "total_after_discount": 89999.99
    },
    "message": "Kupon sepete uygulandı"
}
```

### Örnek 5: Favorilere Ekleme

```bash
POST /api/favorites
Content-Type: application/json

{
    "product_id": 1
}
```

### Örnek 6: Son Görüntülenen Ürünler

```bash
GET /api/recently-viewed?limit=10
```

### Örnek 7: Sepet Birleştirme

```bash
POST /api/cart/merge
Content-Type: application/json

{
    "session_id": "eski_session_id_12345"
}
```

## 📬 Postman Collection Kullanımı

Proje, tüm API endpoint'lerini test etmek için hazır bir Postman collection içerir.

### Adım 1: Postman Collection'ı İçe Aktarın

1. Postman uygulamasını açın
2. **Import** butonuna tıklayın
3. `postman/collection.json` dosyasını seçin
4. Collection başarıyla içe aktarılacaktır

### Adım 2: Environment Variables'ı Ayarlayın

Collection'da şu değişkenler kullanılmaktadır:

- `host`: `localhost` (veya sunucu adresi)
- `port`: `8080` (veya kullandığınız port)
- `base_path`: `turkticaret_test` (proje klasör adı)

Bu değişkenleri Postman'de Environment olarak ayarlayabilir veya collection içindeki değerleri düzenleyebilirsiniz.

### Adım 3: İstekleri Test Edin

Collection şu klasörleri içerir:

- **Products** - Ürün işlemleri
- **Categories** - Kategori işlemleri
- **Cart** - Sepet işlemleri
- **Favorites** - Favori işlemleri
- **Coupons** - Kupon işlemleri
- **Recently Viewed** - Son görüntülenenler

Her endpoint için örnek request'ler hazırdır. Sadece "Send" butonuna tıklayarak test edebilirsiniz.

### Önemli Notlar

- Sepet ve favori işlemleri **session bazlı** çalışır. Postman'de cookies otomatik olarak yönetilir.
- Rate limiting aktif olduğu için çok fazla istek gönderirseniz 429 hatası alabilirsiniz.
- Test verileri için `sql/seed.sql` dosyasını çalıştırdığınızdan emin olun.

## 🏗️ Teknik Kararlar ve Gerekçeleri

### 1. Repository Pattern

**Karar:** Veritabanı işlemlerini Model'lerden ayırdık ve Repository katmanı oluşturduk.

**Gerekçe:**
- **Separation of Concerns**: Veri erişim mantığı iş mantığından ayrıldı
- **Test Edilebilirlik**: Repository'ler mock'lanabilir, unit test yazılabilir
- **Esneklik**: Veritabanı değişikliklerinde sadece Repository katmanı etkilenir
- **SOLID Prensipleri**: Single Responsibility Principle'a uygun

**Örnek:**
```php
// Model yerine Repository kullanımı
class ProductService {
    public function __construct(ProductRepository $repository) {
        // Repository üzerinden veri erişimi
    }
}
```

### 2. Service Layer

**Karar:** İş mantığını Controller'lardan ayırdık ve Service katmanı oluşturduk.

**Gerekçe:**
- **Business Logic Separation**: İş kuralları Controller'dan ayrıldı
- **Reusability**: Service'ler farklı Controller'larda kullanılabilir
- **Maintainability**: İş mantığı değişiklikleri tek yerden yönetilir
- **Testability**: Service'ler bağımsız test edilebilir

**Örnek:**
```php
// Controller sadece HTTP isteklerini yönetir
class ProductController {
    public function index() {
        $products = $this->productService->getProducts();
        // Response döndür
    }
}

// İş mantığı Service'de
class ProductService {
    public function getProducts() {
        // Filtreleme, sıralama, cache kontrolü vb.
    }
}
```

### 3. Dependency Injection Container

**Karar:** Manuel `new Class()` kullanımı yerine DI Container kullandık.

**Gerekçe:**
- **Loose Coupling**: Sınıflar birbirine sıkı bağlı değil
- **Testability**: Mock objeler kolayca enjekte edilebilir
- **Maintainability**: Bağımlılıklar tek yerden yönetilir
- **Scalability**: Yeni bağımlılıklar kolayca eklenebilir

**Örnek:**
```php
// Manuel (kötü):
$repo = new ProductRepository(new PDO(...));
$service = new ProductService($repo);

// DI Container ile (iyi):
$service = $container->resolve(ProductService::class);
// Container otomatik olarak tüm bağımlılıkları çözer
```

### 4. Interface Kullanımı

**Karar:** Service'ler için Interface'ler oluşturduk (ProductServiceInterface, CategoryServiceInterface).

**Gerekçe:**
- **Dependency Inversion**: Concrete class'lara değil, abstraction'lara bağımlılık
- **Flexibility**: Farklı implementation'lar kolayca değiştirilebilir
- **Testing**: Mock interface'ler kolayca oluşturulabilir

**Örnek:**
```php
// Controller interface bekliyor
class ProductController {
    public function __construct(ProductServiceInterface $service) {
        // Concrete class değil, interface
    }
}

// ServiceProvider'da binding
$container->bind(ProductServiceInterface::class, ProductService::class);
```

### 5. PDO Prepared Statements

**Karar:** Tüm SQL sorgularında PDO Prepared Statements kullandık.

**Gerekçe:**
- **SQL Injection Koruması**: Kullanıcı girdileri güvenli şekilde işlenir
- **Performance**: Prepared statement'lar cache'lenir, daha hızlı
- **Best Practice**: PHP'de veritabanı güvenliği için standart yöntem

**Örnek:**
```php
// Güvenli:
$stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

// Güvensiz (kullanılmadı):
// $db->query("SELECT * FROM products WHERE id = $id");
```

### 6. Custom Exception Sınıfları

**Karar:** NotFoundException ve ValidationException gibi özel exception'lar oluşturduk.

**Gerekçe:**
- **Error Handling**: Hata türlerine göre farklı işlemler yapılabilir
- **Consistency**: Tüm projede tutarlı hata yönetimi
- **HTTP Status Codes**: Doğru HTTP status code'ları döndürülür

**Örnek:**
```php
throw new NotFoundException('Ürün bulunamadı', 404, 'PRODUCT_NOT_FOUND');
// Otomatik olarak 404 status code ile JSON response döner
```

### 7. Response Helper

**Karar:** Tüm API response'ları için merkezi ResponseHelper kullandık.

**Gerekçe:**
- **Consistency**: Tüm endpoint'ler aynı response formatını kullanır
- **Maintainability**: Response formatı değişikliği tek yerden yapılır
- **DRY Principle**: Kod tekrarı önlenir

**Örnek:**
```php
// Her yerde aynı format:
ResponseHelper::success($data, 'İşlem başarılı');
ResponseHelper::error('Hata mesajı', 400, 'ERROR_CODE');
```

### 8. Session Bazlı Sepet ve Favoriler

**Karar:** Sepet ve favoriler için veritabanı yerine session kullandık.

**Gerekçe:**
- **Performance**: Veritabanı sorgusu yok, daha hızlı
- **Simplicity**: Misafir kullanıcılar için kullanıcı kaydı gerekmez
- **Scalability**: Session storage (Redis/Memcached) ile ölçeklenebilir

**Not:** Production'da session'lar Redis veya database'de saklanabilir.

### 9. Caching Stratejisi

**Karar:** Dosya tabanlı cache kullandık (CacheService).

**Gerekçe:**
- **Performance**: Sık kullanılan veriler (ürün listesi, kategoriler) cache'lenir
- **Database Load**: Veritabanı yükü azalır
- **TTL Support**: Cache'ler belirli süre sonra expire olur

**Cache Stratejisi:**
- Ürün listesi: 5 dakika TTL
- Ürün detayı: 10 dakika TTL
- Kategori listesi: 30 dakika TTL
- Arama sonuçları: Cache'lenmez (dinamik içerik)

### 10. Rate Limiting

**Karar:** API isteklerini sınırlandırdık (RateLimiterService).

**Gerekçe:**
- **API Abuse Prevention**: Kötüye kullanımı önler
- **Server Protection**: Sunucu kaynaklarını korur
- **Fair Usage**: Tüm kullanıcılar için adil kullanım

**Limitler:**
- `/api/coupons/apply`: 10 istek/dakika
- `/api/cart`: 50 istek/dakika
- `/api/products`: 200 istek/dakika
- Diğer endpoint'ler: 100 istek/dakika

### 11. Logging Sistemi

**Karar:** Tüm önemli işlemleri logladık (LogService).

**Gerekçe:**
- **Debugging**: Hata ayıklama kolaylaşır
- **Audit Trail**: İşlem geçmişi tutulur
- **Monitoring**: Sistem davranışı izlenebilir

**Log Seviyeleri:**
- `info`: Normal işlemler (ürün ekleme, sepet işlemleri)
- `warning`: Uyarılar (validation hataları)
- `error`: Hatalar (exception'lar)

### 12. PSR-4 Autoloading

**Karar:** PSR-4 standardına uygun autoloading kullandık.

**Gerekçe:**
- **Standard Compliance**: PHP standartlarına uyum
- **Composer Compatibility**: Composer ile uyumlu
- **Namespace Organization**: Kod organizasyonu

### 13. Stok Kontrolü

**Karar:** Sepete ekleme ve miktar güncellemede stok kontrolü yaptık.

**Gerekçe:**
- **Data Integrity**: Stokta olmayan ürün sepete eklenemez
- **User Experience**: Kullanıcıya net hata mesajı verilir
- **Business Logic**: E-ticaret için kritik iş kuralı

### 14. Ürün Varyantları

**Karar:** Renk, beden gibi varyant desteği ekledik.

**Gerekçe:**
- **Real-world Requirement**: Gerçek e-ticaret senaryosu
- **Flexibility**: Farklı varyant tipleri desteklenir
- **Stock Management**: Varyant bazlı stok yönetimi

### 15. Session Bazlı Sepet Birleştirme

**Karar:** Farklı session'ların sepetlerini birleştirme özelliği ekledik.

**Gerekçe:**
- **User Experience**: Kullanıcı giriş yaptığında misafir sepeti korunur
- **Data Preservation**: Kullanıcı verisi kaybolmaz
- **E-commerce Best Practice**: Standart e-ticaret özelliği

## 📁 Proje Yapısı

```
turkticaret_test/
├── src/
│   ├── Controllers/          # HTTP isteklerini yönetir
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── CartController.php
│   │   ├── FavoriteController.php
│   │   ├── CouponController.php
│   │   └── RecentlyViewedController.php
│   ├── Services/             # İş mantığı
│   │   ├── ProductService.php
│   │   ├── CartService.php
│   │   ├── CouponService.php
│   │   ├── CacheService.php
│   │   ├── LogService.php
│   │   └── RateLimiterService.php
│   ├── Repositories/         # Veritabanı işlemleri
│   │   ├── ProductRepository.php
│   │   ├── CategoryRepository.php
│   │   ├── CouponRepository.php
│   │   └── VariantRepository.php
│   ├── Models/               # Veri modelleri
│   │   ├── Cart.php
│   │   ├── Favorite.php
│   │   └── ...
│   ├── Container/            # Dependency Injection
│   │   ├── Container.php
│   │   └── ServiceProvider.php
│   ├── Helpers/              # Yardımcı sınıflar
│   │   ├── Router.php
│   │   ├── ResponseHelper.php
│   │   └── PaginationHelper.php
│   ├── Http/                 # HTTP sınıfları
│   │   ├── Request.php
│   │   └── Response.php
│   └── Exceptions/           # Özel exception'lar
│       ├── NotFoundException.php
│       └── ValidationException.php
├── config/
│   └── database.php          # Veritabanı ayarları
├── public/
│   ├── index.php            # Ana giriş noktası
│   └── .htaccess            # Apache yönlendirme
├── sql/
│   ├── schema.sql           # Veritabanı şeması
│   └── seed.sql             # Test verileri
├── postman/
│   └── collection.json     # Postman collection
├── cache/                    # Cache dosyaları (otomatik oluşur)
├── composer.json
├── README.md
└── .gitignore
```

## 🔧 Teknolojiler

- **PHP 8.0+** - Programlama dili
- **MySQL/MariaDB** - Veritabanı
- **PDO** - Veritabanı erişim katmanı
- **Composer** - Bağımlılık yönetimi (opsiyonel)
- **Apache/Nginx** - Web sunucusu

## 📝 API Endpoint'leri

### Ürünler
- `GET /api/products` - Ürün listesi (pagination, filtreleme, sıralama)
- `GET /api/products/{id}` - Ürün detayı

### Kategoriler
- `GET /api/categories` - Kategori listesi

### Sepet
- `GET /api/cart` - Sepeti görüntüle
- `POST /api/cart` - Sepete ürün ekle
- `POST /api/cart/merge` - Sepet birleştir
- `PUT /api/cart/{productId}` - Ürün miktarını güncelle
- `DELETE /api/cart/{productId}` - Sepetten ürün çıkar
- `DELETE /api/cart` - Sepeti temizle

### Favoriler
- `GET /api/favorites` - Favori listesi
- `POST /api/favorites` - Favorilere ekle
- `DELETE /api/favorites/{productId}` - Favorilerden çıkar
- `POST /api/favorites/{productId}/add-to-cart` - Favoriden sepete ekle

### Kuponlar
- `POST /api/coupons/validate` - Kupon doğrula
- `POST /api/coupons/apply` - Kuponu sepete uygula
- `DELETE /api/coupons` - Kuponu kaldır

### Son Görüntülenenler
- `GET /api/recently-viewed` - Son görüntülenen ürünler

## 🧪 Test

Proje, Postman collection ile test edilebilir. Unit test'ler için test framework'ü eklenebilir.

## 📄 Lisans

Bu proje teknik test amaçlıdır.

## 👤 Geliştirici Notları

- Proje, modern PHP geliştirme prensipleri kullanılarak geliştirilmiştir
- SOLID prensipleri uygulanmıştır
- PSR-4 autoloading standardına uygundur
- Production'a geçmeden önce güvenlik audit'i yapılmalıdır
- Session storage için Redis/Memcached kullanılması önerilir
- Cache için Redis kullanılması önerilir (dosya cache yerine)
