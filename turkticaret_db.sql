-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 05 Şub 2026, 20:21:50
-- Sunucu sürümü: 8.0.30
-- PHP Sürümü: 7.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `turkticaret_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL COMMENT 'Sepet ID',
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Session identifier',
  `coupon_id` int DEFAULT NULL COMMENT 'Kupon ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sepetler tablosu';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int NOT NULL COMMENT 'Sepet öğesi ID',
  `cart_id` int NOT NULL COMMENT 'Sepet ID',
  `product_id` int NOT NULL COMMENT 'Ürün ID',
  `quantity` int NOT NULL DEFAULT '1' COMMENT 'Miktar',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sepet öğeleri tablosu';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL COMMENT 'Kategori ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kategori adı',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL slug',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürün kategorileri tablosu';

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Elektronik', 'elektronik', '2026-02-04 20:29:33'),
(2, 'Giyim', 'giyim', '2026-02-04 20:29:33'),
(3, 'Ev & Yaşam', 'ev-yasam', '2026-02-04 20:29:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `coupons`
--

CREATE TABLE `coupons` (
  `id` int NOT NULL COMMENT 'Kupon ID',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kupon kodu',
  `type` enum('percentage','fixed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'İndirim tipi (yüzde/sabit)',
  `value` decimal(10,2) NOT NULL COMMENT 'İndirim değeri',
  `min_cart_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Min. sepet tutarı',
  `usage_limit` int DEFAULT NULL COMMENT 'Kullanım limiti (NULL = sınırsız)',
  `used_count` int DEFAULT '0' COMMENT 'Kullanım sayısı',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Bitiş tarihi',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Aktif mi?',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kuponlar tablosu';

--
-- Tablo döküm verisi `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_cart_total`, `usage_limit`, `used_count`, `expires_at`, `is_active`, `created_at`) VALUES
(1, 'YUZDE15', 'percentage', 15.00, 0.00, 100, 0, '2026-03-06 20:29:33', 1, '2026-02-04 20:29:33'),
(2, 'SABIT100', 'fixed', 100.00, 0.00, 50, 0, '2026-03-06 20:29:33', 1, '2026-02-04 20:29:33'),
(3, 'MIN500', 'percentage', 20.00, 500.00, NULL, 0, '2026-03-06 20:29:33', 1, '2026-02-04 20:29:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL COMMENT 'Favori ID',
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Session identifier',
  `product_id` int NOT NULL COMMENT 'Ürün ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Favoriler tablosu';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL COMMENT 'Log ID',
  `level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Log seviyesi (info, warning, error)',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Log mesajı',
  `context` json DEFAULT NULL COMMENT 'Ek bilgiler (JSON format)',
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session identifier',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP adresi',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='İşlem logları tablosu';

--
-- Tablo döküm verisi `logs`
--

INSERT INTO `logs` (`id`, `level`, `message`, `context`, `session_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'info', 'Ürün görüntülendi', '{\"product_id\": 1, \"product_name\": \"iPhone 15 Pro\"}', 'session_123', '192.168.1.1', 'Mozilla/5.0', '2026-02-04 20:29:33'),
(2, 'info', 'Sepete ürün eklendi', '{\"quantity\": 2, \"product_id\": 7}', 'session_123', '192.168.1.1', 'Mozilla/5.0', '2026-02-04 20:29:33'),
(3, 'info', 'Kupon uygulandı', '{\"discount\": 45.0, \"coupon_code\": \"YUZDE15\"}', 'session_456', '192.168.1.2', 'Mozilla/5.0', '2026-02-04 20:29:33'),
(4, 'warning', 'Stok yetersiz', '{\"available\": 5, \"requested\": 10, \"product_id\": 3}', 'session_789', '192.168.1.3', 'Mozilla/5.0', '2026-02-04 20:29:33'),
(5, 'error', 'Veritabanı bağlantı hatası', '{\"error\": \"Connection timeout\"}', NULL, '192.168.1.4', 'Mozilla/5.0', '2026-02-04 20:29:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL COMMENT 'Ürün ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ürün adı',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ürün açıklaması',
  `price` decimal(10,2) NOT NULL COMMENT 'Fiyat',
  `stock` int NOT NULL DEFAULT '0' COMMENT 'Stok miktarı',
  `category_id` int DEFAULT NULL COMMENT 'Kategori ID',
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Görsel URL',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürünler tablosu';

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15 Pro', 'Apple iPhone 15 Pro 256GB, Titanium, 5G, A17 Pro çip', 45999.99, 15, 1, 'https://example.com/images/iphone15pro.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(2, 'Samsung Galaxy S24', 'Samsung Galaxy S24 Ultra 512GB, 5G, S Pen', 42999.99, 8, 1, 'https://example.com/images/galaxys24.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(3, 'MacBook Pro 14\"', 'Apple MacBook Pro 14\" M3 Pro, 18GB RAM, 512GB SSD', 69999.99, 5, 1, 'https://example.com/images/macbookpro.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(4, 'iPad Air', 'Apple iPad Air 11\" M2, 256GB, Wi-Fi', 24999.99, 12, 1, 'https://example.com/images/ipadair.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(5, 'AirPods Pro', 'Apple AirPods Pro 2. Nesil, Aktif Gürültü Engelleme', 8999.99, 20, 1, 'https://example.com/images/airpodspro.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(6, 'Sony WH-1000XM5', 'Sony WH-1000XM5 Kablosuz Kulaklık, Gürültü Engelleme', 12999.99, 10, 1, 'https://example.com/images/sony-headphone.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(7, 'Erkek Polo Tişört', 'Pamuklu erkek polo tişört, çok renk seçeneği, nefes alabilir kumaş', 299.99, 50, 2, 'https://example.com/images/polo-tshirt.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(8, 'Kadın Denim Pantolon', 'Klasik kesim kadın denim pantolon, mavi, esnek bel', 599.99, 30, 2, 'https://example.com/images/denim-pantolon.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(9, 'Erkek Gömlek', 'Klasik kesim erkek gömlek, beyaz, %100 pamuk', 399.99, 25, 2, 'https://example.com/images/gomlek.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(10, 'Kadın Elbise', 'Yazlık kadın elbise, çiçek desenli, pamuklu kumaş', 799.99, 18, 2, 'https://example.com/images/elbise.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(11, 'Erkek Ceket', 'Klasik kesim erkek ceket, siyah, %100 yün', 1999.99, 12, 2, 'https://example.com/images/ceket.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(12, 'Kadın Mont', 'Kışlık kadın mont, su geçirmez, yalıtımlı', 1499.99, 15, 2, 'https://example.com/images/mont.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(13, 'Kahve Makinesi', 'Otomatik espresso kahve makinesi, 15 bar basınç, süt köpürtücü', 8999.99, 12, 3, 'https://example.com/images/kahve-makinesi.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(14, 'Yatak Odası Takımı', '5 parça yatak odası takımı, meşe ağacı, modern tasarım', 12999.99, 3, 3, 'https://example.com/images/yatak-odasi.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(15, 'Masa Lambası', 'LED masa lambası, dokunmatik kontrol, 3 farklı ışık modu', 499.99, 20, 3, 'https://example.com/images/masa-lambasi.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(16, 'Yemek Takımı', '12 kişilik porselen yemek takımı, beyaz, şık tasarım', 2999.99, 8, 3, 'https://example.com/images/yemek-takimi.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(17, 'Halı', 'Modern desenli halı, 200x300 cm, yıkanabilir', 2499.99, 6, 3, 'https://example.com/images/hali.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(18, 'Mutfak Robotu', 'Çok fonksiyonlu mutfak robotu, 1000W motor, 5 hız', 5999.99, 10, 3, 'https://example.com/images/mutfak-robotu.jpg', '2026-02-04 20:29:33', '2026-02-04 20:29:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int NOT NULL COMMENT 'Varyant ID',
  `product_id` int NOT NULL COMMENT 'Ürün ID',
  `variant_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Varyant tipi (renk, beden, vb.)',
  `variant_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Varyant değeri (kırmızı, M, vb.)',
  `price_modifier` decimal(10,2) DEFAULT '0.00' COMMENT 'Fiyat farkı',
  `stock` int NOT NULL DEFAULT '0' COMMENT 'Stok miktarı',
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Stok kodu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürün varyantları tablosu';

--
-- Tablo döküm verisi `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_type`, `variant_value`, `price_modifier`, `stock`, `sku`, `created_at`, `updated_at`) VALUES
(1, 7, 'renk', 'Beyaz', 0.00, 15, 'POLO-WHITE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(2, 7, 'renk', 'Siyah', 0.00, 12, 'POLO-BLACK-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(3, 7, 'renk', 'Mavi', 0.00, 10, 'POLO-BLUE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(4, 7, 'beden', 'S', 0.00, 8, 'POLO-WHITE-S', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(5, 7, 'beden', 'M', 0.00, 15, 'POLO-WHITE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(6, 7, 'beden', 'L', 0.00, 12, 'POLO-WHITE-L', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(7, 7, 'beden', 'XL', 50.00, 5, 'POLO-WHITE-XL', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(8, 8, 'beden', '36', 0.00, 8, 'DENIM-36', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(9, 8, 'beden', '38', 0.00, 10, 'DENIM-38', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(10, 8, 'beden', '40', 0.00, 7, 'DENIM-40', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(11, 8, 'beden', '42', 0.00, 5, 'DENIM-42', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(12, 9, 'renk', 'Beyaz', 0.00, 10, 'GOMLEK-WHITE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(13, 9, 'renk', 'Mavi', 0.00, 8, 'GOMLEK-BLUE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(14, 9, 'beden', 'M', 0.00, 10, 'GOMLEK-WHITE-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(15, 9, 'beden', 'L', 0.00, 8, 'GOMLEK-WHITE-L', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(16, 9, 'beden', 'XL', 50.00, 7, 'GOMLEK-WHITE-XL', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(17, 10, 'renk', 'Pembe', 0.00, 6, 'ELBISE-PINK-S', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(18, 10, 'renk', 'Mavi', 0.00, 5, 'ELBISE-BLUE-S', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(19, 10, 'renk', 'Beyaz', 0.00, 7, 'ELBISE-WHITE-S', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(20, 10, 'beden', 'S', 0.00, 6, 'ELBISE-PINK-S', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(21, 10, 'beden', 'M', 0.00, 7, 'ELBISE-PINK-M', '2026-02-04 20:29:33', '2026-02-04 20:29:33'),
(22, 10, 'beden', 'L', 0.00, 5, 'ELBISE-PINK-L', '2026-02-04 20:29:33', '2026-02-04 20:29:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int NOT NULL COMMENT 'Görüntüleme ID',
  `session_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Session identifier',
  `product_id` int NOT NULL COMMENT 'Ürün ID',
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Görüntüleme tarihi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Son görüntülenen ürünler tablosu';

--
-- Tablo döküm verisi `recently_viewed`
--

INSERT INTO `recently_viewed` (`id`, `session_id`, `product_id`, `viewed_at`) VALUES
(1, 'session_123', 1, '2026-02-04 19:29:33'),
(2, 'session_123', 2, '2026-02-04 18:29:33'),
(3, 'session_123', 7, '2026-02-04 17:29:33'),
(4, 'session_456', 3, '2026-02-04 19:59:33'),
(5, 'session_456', 5, '2026-02-04 19:29:33'),
(6, 'session_456', 10, '2026-02-04 18:29:33');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_coupon` (`coupon_id`);

--
-- Tablo için indeksler `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_product` (`cart_id`,`product_id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_name` (`name`);

--
-- Tablo için indeksler `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Tablo için indeksler `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session_product` (`session_id`,`product_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Tablo için indeksler `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Tablo için indeksler `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_stock` (`stock`);

--
-- Tablo için indeksler `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_type` (`variant_type`),
  ADD KEY `idx_sku` (`sku`);

--
-- Tablo için indeksler `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session_product_recent` (`session_id`,`product_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Sepet ID';

--
-- Tablo için AUTO_INCREMENT değeri `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Sepet öğesi ID';

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Kategori ID', AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Kupon ID', AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Favori ID';

--
-- Tablo için AUTO_INCREMENT değeri `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Log ID', AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Ürün ID', AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Varyant ID', AUTO_INCREMENT=23;

--
-- Tablo için AUTO_INCREMENT değeri `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Görüntüleme ID', AUTO_INCREMENT=7;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `recently_viewed_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
