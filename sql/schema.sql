-- Veritabanı oluştur
CREATE DATABASE IF NOT EXISTS turkticaret_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE turkticaret_db;

-- Kategoriler Tablosu
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Kategori ID',
    name VARCHAR(100) NOT NULL COMMENT 'Kategori adı',
    slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL slug',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    INDEX idx_slug (slug),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürün kategorileri tablosu';

-- Kuponlar Tablosu
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Kupon ID',
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Kupon kodu',
    type ENUM('percentage', 'fixed') NOT NULL COMMENT 'İndirim tipi (yüzde/sabit)',
    value DECIMAL(10,2) NOT NULL COMMENT 'İndirim değeri',
    min_cart_total DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Min. sepet tutarı',
    usage_limit INT NULL COMMENT 'Kullanım limiti (NULL = sınırsız)',
    used_count INT DEFAULT 0 COMMENT 'Kullanım sayısı',
    expires_at TIMESTAMP NULL COMMENT 'Bitiş tarihi',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Aktif mi?',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    INDEX idx_code (code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kuponlar tablosu';

-- Ürünler Tablosu
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Ürün ID',
    name VARCHAR(255) NOT NULL COMMENT 'Ürün adı',
    description TEXT COMMENT 'Ürün açıklaması',
    price DECIMAL(10,2) NOT NULL COMMENT 'Fiyat',
    stock INT NOT NULL DEFAULT 0 COMMENT 'Stok miktarı',
    category_id INT COMMENT 'Kategori ID',
    image_url VARCHAR(500) COMMENT 'Görsel URL',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi',
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_name (name),
    INDEX idx_price (price),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürünler tablosu';

-- Sepetler Tablosu
CREATE TABLE IF NOT EXISTS carts (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Sepet ID',
    session_id VARCHAR(100) NOT NULL COMMENT 'Session identifier',
    coupon_id INT NULL COMMENT 'Kupon ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi',
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_coupon (coupon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sepetler tablosu';

-- Sepet Öğeleri Tablosu
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Sepet öğesi ID',
    cart_id INT NOT NULL COMMENT 'Sepet ID',
    product_id INT NOT NULL COMMENT 'Ürün ID',
    variant_id INT NULL COMMENT 'Varyant ID (opsiyonel)',
    quantity INT NOT NULL DEFAULT 1 COMMENT 'Miktar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_cart (cart_id),
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    UNIQUE KEY unique_cart_product_variant (cart_id, product_id, variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sepet öğeleri tablosu';

-- Favoriler Tablosu
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Favori ID',
    session_id VARCHAR(100) NOT NULL COMMENT 'Session identifier',
    product_id INT NOT NULL COMMENT 'Ürün ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_product (product_id),
    UNIQUE KEY unique_session_product (session_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Favoriler tablosu';

-- Ürün Varyantları Tablosu (Bonus: Renk, beden vb.)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Varyant ID',
    product_id INT NOT NULL COMMENT 'Ürün ID',
    variant_type VARCHAR(50) NOT NULL COMMENT 'Varyant tipi (renk, beden, vb.)',
    variant_value VARCHAR(100) NOT NULL COMMENT 'Varyant değeri (kırmızı, M, vb.)',
    price_modifier DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Fiyat farkı',
    stock INT NOT NULL DEFAULT 0 COMMENT 'Stok miktarı',
    sku VARCHAR(100) COMMENT 'Stok kodu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi',
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_type (variant_type),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ürün varyantları tablosu';

-- Son Görüntülenen Ürünler Tablosu (Bonus)
CREATE TABLE IF NOT EXISTS recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Görüntüleme ID',
    session_id VARCHAR(100) NOT NULL COMMENT 'Session identifier',
    product_id INT NOT NULL COMMENT 'Ürün ID',
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Görüntüleme tarihi',
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_product (product_id),
    INDEX idx_viewed_at (viewed_at),
    UNIQUE KEY unique_session_product_recent (session_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Son görüntülenen ürünler tablosu';

-- İşlem Logları Tablosu (Bonus)
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Log ID',
    level VARCHAR(20) NOT NULL COMMENT 'Log seviyesi (info, warning, error)',
    message TEXT NOT NULL COMMENT 'Log mesajı',
    context JSON COMMENT 'Ek bilgiler (JSON format)',
    session_id VARCHAR(100) COMMENT 'Session identifier',
    ip_address VARCHAR(45) COMMENT 'IP adresi',
    user_agent VARCHAR(500) COMMENT 'Tarayıcı bilgisi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturma tarihi',
    INDEX idx_level (level),
    INDEX idx_session (session_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='İşlem logları tablosu';
