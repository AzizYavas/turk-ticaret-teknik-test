-- Test verileri ekle
USE turkticaret_db;

-- Kategoriler ekle (En az 3 kategori)
INSERT INTO categories (name, slug) VALUES
('Elektronik', 'elektronik'),
('Giyim', 'giyim'),
('Ev & Yaşam', 'ev-yasam');

-- Kuponlar ekle (3 farklı kupon: yüzdelik, sabit, minimum tutarlı)
-- Bonus: Kupon kullanım limiti eklendi
INSERT INTO coupons (code, type, value, min_cart_total, usage_limit, used_count, expires_at, is_active) VALUES
-- 1. Yüzdelik kupon (%15 indirim, minimum tutar yok, 100 kullanım limiti)
('YUZDE15', 'percentage', 15.00, 0.00, 100, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), TRUE),
-- 2. Sabit kupon (100 TL indirim, minimum tutar yok, 50 kullanım limiti)
('SABIT100', 'fixed', 100.00, 0.00, 50, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), TRUE),
-- 3. Minimum tutarlı kupon (%20 indirim, minimum 500 TL sepet tutarı, sınırsız kullanım)
('MIN500', 'percentage', 20.00, 500.00, NULL, 0, DATE_ADD(NOW(), INTERVAL 30 DAY), TRUE);

-- Ürünler ekle (15+ ürün)
INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES
-- Elektronik Kategorisi (1)
('iPhone 15 Pro', 'Apple iPhone 15 Pro 256GB, Titanium, 5G, A17 Pro çip', 45999.99, 15, 1, 'https://example.com/images/iphone15pro.jpg'),
('Samsung Galaxy S24', 'Samsung Galaxy S24 Ultra 512GB, 5G, S Pen', 42999.99, 8, 1, 'https://example.com/images/galaxys24.jpg'),
('MacBook Pro 14"', 'Apple MacBook Pro 14" M3 Pro, 18GB RAM, 512GB SSD', 69999.99, 5, 1, 'https://example.com/images/macbookpro.jpg'),
('iPad Air', 'Apple iPad Air 11" M2, 256GB, Wi-Fi', 24999.99, 12, 1, 'https://example.com/images/ipadair.jpg'),
('AirPods Pro', 'Apple AirPods Pro 2. Nesil, Aktif Gürültü Engelleme', 8999.99, 20, 1, 'https://example.com/images/airpodspro.jpg'),
('Sony WH-1000XM5', 'Sony WH-1000XM5 Kablosuz Kulaklık, Gürültü Engelleme', 12999.99, 10, 1, 'https://example.com/images/sony-headphone.jpg'),
-- Giyim Kategorisi (2)
('Erkek Polo Tişört', 'Pamuklu erkek polo tişört, çok renk seçeneği, nefes alabilir kumaş', 299.99, 50, 2, 'https://example.com/images/polo-tshirt.jpg'),
('Kadın Denim Pantolon', 'Klasik kesim kadın denim pantolon, mavi, esnek bel', 599.99, 30, 2, 'https://example.com/images/denim-pantolon.jpg'),
('Erkek Gömlek', 'Klasik kesim erkek gömlek, beyaz, %100 pamuk', 399.99, 25, 2, 'https://example.com/images/gomlek.jpg'),
('Kadın Elbise', 'Yazlık kadın elbise, çiçek desenli, pamuklu kumaş', 799.99, 18, 2, 'https://example.com/images/elbise.jpg'),
('Erkek Ceket', 'Klasik kesim erkek ceket, siyah, %100 yün', 1999.99, 12, 2, 'https://example.com/images/ceket.jpg'),
('Kadın Mont', 'Kışlık kadın mont, su geçirmez, yalıtımlı', 1499.99, 15, 2, 'https://example.com/images/mont.jpg'),
-- Ev & Yaşam Kategorisi (3)
('Kahve Makinesi', 'Otomatik espresso kahve makinesi, 15 bar basınç, süt köpürtücü', 8999.99, 12, 3, 'https://example.com/images/kahve-makinesi.jpg'),
('Yatak Odası Takımı', '5 parça yatak odası takımı, meşe ağacı, modern tasarım', 12999.99, 3, 3, 'https://example.com/images/yatak-odasi.jpg'),
('Masa Lambası', 'LED masa lambası, dokunmatik kontrol, 3 farklı ışık modu', 499.99, 20, 3, 'https://example.com/images/masa-lambasi.jpg'),
('Yemek Takımı', '12 kişilik porselen yemek takımı, beyaz, şık tasarım', 2999.99, 8, 3, 'https://example.com/images/yemek-takimi.jpg'),
('Halı', 'Modern desenli halı, 200x300 cm, yıkanabilir', 2499.99, 6, 3, 'https://example.com/images/hali.jpg'),
('Mutfak Robotu', 'Çok fonksiyonlu mutfak robotu, 1000W motor, 5 hız', 5999.99, 10, 3, 'https://example.com/images/mutfak-robotu.jpg');

-- Ürün Varyantları ekle (Bonus: Renk ve beden varyantları)
-- Erkek Polo Tişört (ID: 7) için renk ve beden varyantları
INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock, sku) VALUES
(7, 'renk', 'Beyaz', 0.00, 15, 'POLO-WHITE-M'),
(7, 'renk', 'Siyah', 0.00, 12, 'POLO-BLACK-M'),
(7, 'renk', 'Mavi', 0.00, 10, 'POLO-BLUE-M'),
(7, 'beden', 'S', 0.00, 8, 'POLO-WHITE-S'),
(7, 'beden', 'M', 0.00, 15, 'POLO-WHITE-M'),
(7, 'beden', 'L', 0.00, 12, 'POLO-WHITE-L'),
(7, 'beden', 'XL', 50.00, 5, 'POLO-WHITE-XL');

-- Kadın Denim Pantolon (ID: 8) için beden varyantları
INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock, sku) VALUES
(8, 'beden', '36', 0.00, 8, 'DENIM-36'),
(8, 'beden', '38', 0.00, 10, 'DENIM-38'),
(8, 'beden', '40', 0.00, 7, 'DENIM-40'),
(8, 'beden', '42', 0.00, 5, 'DENIM-42');

-- Erkek Gömlek (ID: 9) için renk ve beden varyantları
INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock, sku) VALUES
(9, 'renk', 'Beyaz', 0.00, 10, 'GOMLEK-WHITE-M'),
(9, 'renk', 'Mavi', 0.00, 8, 'GOMLEK-BLUE-M'),
(9, 'beden', 'M', 0.00, 10, 'GOMLEK-WHITE-M'),
(9, 'beden', 'L', 0.00, 8, 'GOMLEK-WHITE-L'),
(9, 'beden', 'XL', 50.00, 7, 'GOMLEK-WHITE-XL');

-- Kadın Elbise (ID: 10) için renk ve beden varyantları
INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock, sku) VALUES
(10, 'renk', 'Pembe', 0.00, 6, 'ELBISE-PINK-S'),
(10, 'renk', 'Mavi', 0.00, 5, 'ELBISE-BLUE-S'),
(10, 'renk', 'Beyaz', 0.00, 7, 'ELBISE-WHITE-S'),
(10, 'beden', 'S', 0.00, 6, 'ELBISE-PINK-S'),
(10, 'beden', 'M', 0.00, 7, 'ELBISE-PINK-M'),
(10, 'beden', 'L', 0.00, 5, 'ELBISE-PINK-L');

-- Son Görüntülenen Ürünler (Bonus: Örnek veriler)
INSERT INTO recently_viewed (session_id, product_id, viewed_at) VALUES
('session_123', 1, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('session_123', 2, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('session_123', 7, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('session_456', 3, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
('session_456', 5, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('session_456', 10, DATE_SUB(NOW(), INTERVAL 2 HOUR));

-- İşlem Logları (Bonus: Örnek loglar)
INSERT INTO logs (level, message, context, session_id, ip_address, user_agent) VALUES
('info', 'Ürün görüntülendi', '{"product_id": 1, "product_name": "iPhone 15 Pro"}', 'session_123', '192.168.1.1', 'Mozilla/5.0'),
('info', 'Sepete ürün eklendi', '{"product_id": 7, "quantity": 2}', 'session_123', '192.168.1.1', 'Mozilla/5.0'),
('info', 'Kupon uygulandı', '{"coupon_code": "YUZDE15", "discount": 45.00}', 'session_456', '192.168.1.2', 'Mozilla/5.0'),
('warning', 'Stok yetersiz', '{"product_id": 3, "requested": 10, "available": 5}', 'session_789', '192.168.1.3', 'Mozilla/5.0'),
('error', 'Veritabanı bağlantı hatası', '{"error": "Connection timeout"}', NULL, '192.168.1.4', 'Mozilla/5.0');
