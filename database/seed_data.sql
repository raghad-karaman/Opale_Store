-- =====================================================================
-- Opale Store - ÖRNEK VERİ (phpMyAdmin > opalstore > İçe aktar / SQL)
-- Önce:  php artisan migrate   (tablolar hazır olmalı)
-- Güvenli: mevcut kayıtlara dokunmaz (INSERT IGNORE / NOT EXISTS).
-- Siparişler her çalıştırmada tekrar eklenir, bu yüzden dosyayı BİR KEZ çalıştır.
-- Görseller public/uploads/ altındaki mevcut dosyalara bağlıdır.
-- Giriş: admin@opale.com / Admin12345   |   ayse@example.com / Password123
-- =====================================================================
SET NAMES utf8mb4;

-- ---------- KULLANICILAR ----------
INSERT IGNORE INTO users (name, email, mobile, password, utype, created_at, updated_at) VALUES
('Admin',         'admin@opale.com',    '5000000000', '$2y$10$J5P9.bvLTn82xAHTXLIFQeWg.sRVMnqIDqFpRLLUmuvhovzf0ZmDC', 'ADM', NOW(), NOW()),
('Ayşe Yılmaz',   'ayse@example.com',   '5301112233', '$2y$10$2bOpftx9fi20hzi8FQQ7t.Cctg24t4qXzi3HODIGBpXTEJhGzN/jK', 'USR', NOW(), NOW()),
('Mehmet Demir',  'mehmet@example.com', '5302223344', '$2y$10$2bOpftx9fi20hzi8FQQ7t.Cctg24t4qXzi3HODIGBpXTEJhGzN/jK', 'USR', NOW(), NOW()),
('Zeynep Kaya',   'zeynep@example.com', '5303334455', '$2y$10$2bOpftx9fi20hzi8FQQ7t.Cctg24t4qXzi3HODIGBpXTEJhGzN/jK', 'USR', NOW(), NOW());

-- ---------- ADRESLER ----------
INSERT INTO addresses (name, phone, locality, address, city, state, country, landmark, zip, type, is_default, user_id, created_at, updated_at)
SELECT 'Ayşe Yılmaz', '5301112233', 'Şahinbey', 'Atatürk Cd. No:12 D:4', 'Gaziantep', 'Gaziantep', 'Turkiye', 'Park yanı', '270000', 'home', 1, u.id, NOW(), NOW()
FROM users u WHERE u.email='ayse@example.com'
AND NOT EXISTS (SELECT 1 FROM addresses a WHERE a.user_id=u.id);

INSERT INTO addresses (name, phone, locality, address, city, state, country, landmark, zip, type, is_default, user_id, created_at, updated_at)
SELECT 'Mehmet Demir', '5302223344', 'Şehitkamil', 'Gazi Mh. 45. Sk. No:7', 'Gaziantep', 'Gaziantep', 'Turkiye', 'Okul karşısı', '270100', 'home', 1, u.id, NOW(), NOW()
FROM users u WHERE u.email='mehmet@example.com'
AND NOT EXISTS (SELECT 1 FROM addresses a WHERE a.user_id=u.id);

INSERT INTO addresses (name, phone, locality, address, city, state, country, landmark, zip, type, is_default, user_id, created_at, updated_at)
SELECT 'Zeynep Kaya', '5303334455', 'Kadıköy', 'Moda Cd. No:21 D:2', 'İstanbul', 'İstanbul', 'Turkiye', 'Çarşı girişi', '340000', 'home', 1, u.id, NOW(), NOW()
FROM users u WHERE u.email='zeynep@example.com'
AND NOT EXISTS (SELECT 1 FROM addresses a WHERE a.user_id=u.id);

-- ---------- KATEGORİLER ----------
INSERT IGNORE INTO categories (name, slug, image, created_at, updated_at) VALUES
('Ayakkabı',            'ayakkabi',          '1748629708.jpg', NOW(), NOW()),
('Kolye',               'kolye',             '1748703665.jpg', NOW(), NOW()),
('Küpe & Set',          'kupe-set',          '1748649368.jpg', NOW(), NOW()),
('Bileklik',            'bileklik',          '1748551140.jpg', NOW(), NOW()),
('Elbise',              'elbise',            '1747326364.jpg', NOW(), NOW()),
('Sweatshirt & Triko',  'sweatshirt-triko',  '1748199350.jpg', NOW(), NOW());

-- ---------- MARKALAR ----------
INSERT IGNORE INTO brands (name, slug, image, created_at, updated_at) VALUES
('Nike',        'nike',        '1747086523.jpg', NOW(), NOW()),
('Adidas',      'adidas',      '1747086561.jpg', NOW(), NOW()),
('Opale',       'opale',       '1748535819.jpg', NOW(), NOW()),
('Opale Moda',  'opale-moda',  '1748537534.jpg', NOW(), NOW());

-- ---------- ÜRÜNLER ----------
-- (ad, slug, kısa açıklama, açıklama, normal fiyat, indirimli fiyat, SKU, stok durumu, öne çıkan, adet, görsel, galeri, kategori slug, marka slug)
INSERT IGNORE INTO products (name, slug, short_description, description, regular_price, sale_price, SKU, stock_status, featured, quantity, image, images, category_id, brand_id, created_at, updated_at)
SELECT p.name, p.slug, p.sd, p.d, p.rp, p.sp, p.sku, p.ss, p.f, p.q, p.img, p.imgs,
       (SELECT id FROM categories WHERE slug=p.cs), (SELECT id FROM brands WHERE slug=p.bs), NOW(), NOW()
FROM (
 SELECT 'Lacivert Spor Ayakkabı' name,'lacivert-spor-ayakkabi' slug,'Günlük kullanım için hafif spor ayakkabı' sd,'Hafif tabanı ve nefes alan kumaşıyla gün boyu rahat kullanım sağlar. Günlük yürüyüş ve spor için idealdir.' d,1499 rp,1199 sp,'AYK-001' sku,'instock' ss,0 f,25 q,'1746448292.png' img,'1746448292.1.png,1746448292.2.png' imgs,'ayakkabi' cs,'nike' bs
 UNION ALL SELECT 'Siyah Yüksek Taban Sneaker','siyah-yuksek-taban-sneaker','Şık siyah bilekli sneaker','Yüksek tabanı ve klasik tasarımıyla kombinlerinize tarz katar. Bağcıklı, rahat kalıp.',1299,NULL,'AYK-002','instock',0,18,'1746453451.jpg','1746453451.1.jpeg','ayakkabi','nike'
 UNION ALL SELECT 'Bej Fermuarlı Sneaker','bej-fermuarli-sneaker','Fermuar detaylı bej sneaker','Yan fermuar detayıyla kolay giyilir. Yumuşak iç astarı ve esnek tabanı vardır.',1349,1099,'AYK-003','instock',0,12,'1746453689.jpg','1746453689.1.jpeg','ayakkabi','opale-moda'
 UNION ALL SELECT 'Adidas Superstar','adidas-superstar','Klasik beyaz-siyah Superstar','Ikonik çizgili tasarım ve dayanıklı kauçuk burun. Her kombine uyan zamansız model.',2999,2499,'AYK-004','instock',1,30,'1748626780.jpg','1748626780.1.jpeg','ayakkabi','adidas'
 UNION ALL SELECT 'Lacivert Koşu Ayakkabısı','lacivert-kosu-ayakkabisi','Turuncu detaylı koşu ayakkabısı','Hafif ve esnek yapısıyla koşu ve antrenman için tasarlandı. Yastıklamalı taban.',1899,1599,'AYK-005','instock',0,8,'1746453765.jpg',NULL,'ayakkabi','nike'
 UNION ALL SELECT 'Beyaz Oversize Kapüşonlu Sweatshirt','beyaz-oversize-kapusonlu-sweatshirt','Bol kesim kapüşonlu sweatshirt','İçi yumuşak şardonlu kumaş, bol kalıp ve kapüşonlu tasarım. Günlük kullanım için çok rahat.',649,499,'GYM-001','instock',1,40,'1747086588.jpg','1747086588_1.jpeg','sweatshirt-triko','opale-moda'
 UNION ALL SELECT 'Kırmızı Balıkçı Yaka Kazak','kirmizi-balikci-yaka-kazak','Düğme detaylı kırmızı kazak','Sıcak tutan örgü doku, balıkçı yaka ve kolları düğme detaylı şık kazak.',799,649,'GYM-002','instock',0,15,'kazak1.jpeg',NULL,'sweatshirt-triko','opale-moda'
 UNION ALL SELECT 'Yeşil Dantel Elbise','yesil-dantel-elbise','Zarif zümrüt yeşili dantel elbise','Uzun boy, dantel kumaş ve zarif kesim. Özel davetler için şık bir seçim.',1899,1499,'GYM-003','instock',1,10,'1747328114.jpg','1747328114.1.jpg','elbise','opale-moda'
 UNION ALL SELECT 'Sarı Straplez Uzun Elbise','sari-straplez-uzun-elbise','Volanlı sarı uzun elbise','Straplez yakası ve akışkan eteğiyle yaz davetleri için ideal.',1299,NULL,'GYM-004','out of stock',0,0,'1747328444.jpg','1747328444.1.jpg','elbise','opale-moda'
 UNION ALL SELECT 'Kiraz Küpe','kiraz-kupe','Pembe taşlı kiraz küpe','Rose gold kaplama üzerine pembe zirkon taşlı sevimli kiraz küpe.',249,199,'TKI-001','instock',1,60,'1748537920.jpg','1748537920.1.jpeg','kupe-set','opale'
 UNION ALL SELECT 'Mavi Kelebek Kolye','mavi-kelebek-kolye','Altın zincirli kelebek kolye','İnce zincirli, renk geçişli kelebek figürlü zarif kolye.',299,249,'TKI-002','instock',0,35,'1748538010.jpg','1748538010_1.jpeg','kolye','opale'
 UNION ALL SELECT 'Zirkon Taşlı Gelin Seti','zirkon-tasli-gelin-seti','Kolye ve küpeden oluşan set','Parlak zirkon taşlı kolye ve küpe seti. Düğün, nişan ve özel günler için.',899,749,'TKI-003','instock',1,9,'1748538219.jpg','1748538219.1.jpeg','kupe-set','opale'
 UNION ALL SELECT 'Kuş Figürlü Rose Kolye','kus-figurlu-rose-kolye','Rose gold kuş kolye','Kanat açmış kuş figürlü, ince zincirli şık kolye.',349,NULL,'TKI-004','instock',0,22,'1748538738.jpg','1748538738.1.jpg','kolye','opale'
 UNION ALL SELECT 'Yeşil Taşlı Bileklik Seti','yesil-tasli-bileklik-seti','3 parça yeşil taşlı bileklik','Altın rengi, yeşil taş detaylı üçlü bileklik seti. Tek başına ya da kombinli kullanılır.',399,329,'TKI-005','instock',1,45,'1748551264.jpg','1748551264.1.jpeg','bileklik','opale'
 UNION ALL SELECT 'Kalp Pembe Zirkon Set','kalp-pembe-zirkon-set','Kolye, küpe, yüzük ve bileklik seti','Pembe kalp taşlı komple takı seti. Hediye olarak çok uygun.',1299,999,'TKI-006','instock',0,14,'1748649525.jpg','1748649525.1.jpeg,taki.jpeg','kupe-set','opale'
 UNION ALL SELECT 'Siyah Kelebek Kolye Küpe Seti','siyah-kelebek-kolye-kupe-seti','Hediye kutulu kelebek set','Siyah kelebek figürlü kolye ve küpe, şık hediye kutusunda.',549,449,'TKI-007','instock',0,28,'1748649984.jpg','1748649984_1.jpeg','kupe-set','opale'
 UNION ALL SELECT 'Pembe Ayıcık Kolye','pembe-ayicik-kolye','Pembe taşlı ayıcık kolye','Pembe zirkon taşlı sevimli ayıcık figürü, rose gold zincir.',799,649,'TKI-008','instock',1,20,'1748703719.jpg','1748703719.1.jpeg','kolye','opale'
) p;

-- ---------- SLIDER ----------
INSERT INTO slides (tagline, title, subtitle, link, image, status, created_at, updated_at)
SELECT 'Yeni Sezon', 'Tarzını Yansıtan', 'Özel Koleksiyonlar', '/shop', 'slide1.jpg', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM slides WHERE image='slide1.jpg');

INSERT INTO slides (tagline, title, subtitle, link, image, status, created_at, updated_at)
SELECT 'Takı Dünyası', 'Işıltılı Anlar İçin', 'Yeni Takı Setleri', '/shop', '1747490180.png', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM slides WHERE image='1747490180.png');

-- ---------- KUPONLAR ----------
INSERT IGNORE INTO coupons (code, type, value, cart_value, expiry_date, created_at, updated_at) VALUES
('HOSGELDIN10', 'percent', 10,  500,  '2027-12-31', NOW(), NOW()),
('INDIRIM100',  'fixed',   100, 1000, '2027-12-31', NOW(), NOW()),
('YAZ25',       'percent', 25,  2000, '2027-12-31', NOW(), NOW());

-- ---------- İLETİŞİM MESAJLARI ----------
INSERT INTO contacts (name, email, phone, comment, created_at, updated_at)
SELECT 'Elif Arslan', 'elif@example.com', '5304445566', 'Merhaba, Adidas Superstar ürününün 39 numarası ne zaman stoğa girer?', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE email='elif@example.com');
INSERT INTO contacts (name, email, phone, comment, created_at, updated_at)
SELECT 'Can Öztürk', 'can@example.com', '5305556677', 'Toplu alımlarda indirim yapıyor musunuz?', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE email='can@example.com');

-- ---------- SİPARİŞLER (KDV %21; subtotal = indirim sonrası tutar) ----------
-- Sipariş 1: Ayşe - teslim edildi - kapıda ödeme
INSERT INTO orders (user_id, subtotal, discount, tax, total, name, phone, locality, address, city, state, country, landmark, zip, type, status, is_shipping_different, delivered_date, canceled_date, created_at, updated_at)
SELECT id, 2897.00, 0.00, 608.37, 3505.37, 'Ayşe Yılmaz', '5301112233', 'Şahinbey', 'Atatürk Cd. No:12 D:4', 'Gaziantep', 'Gaziantep', 'Turkiye', 'Park yanı', '270000', 'home', 'delivered', 0, '2026-07-15', NULL, '2026-07-10 14:20:00', '2026-07-15 10:00:00'
FROM users WHERE email='ayse@example.com';
SET @o1 = LAST_INSERT_ID();
INSERT INTO order_items (product_id, order_id, price, quantity, created_at, updated_at) VALUES
((SELECT id FROM products WHERE slug='adidas-superstar'), @o1, 2499.00, 1, '2026-07-10 14:20:00', '2026-07-10 14:20:00'),
((SELECT id FROM products WHERE slug='kiraz-kupe'),       @o1,  199.00, 2, '2026-07-10 14:20:00', '2026-07-10 14:20:00');
INSERT INTO transactions (user_id, order_id, mode, status, created_at, updated_at)
SELECT user_id, id, 'cod', 'approved', '2026-07-10 14:20:00', '2026-07-15 10:00:00' FROM orders WHERE id=@o1;

-- Sipariş 2: Mehmet - yeni sipariş - kart (beklemede)
INSERT INTO orders (user_id, subtotal, discount, tax, total, name, phone, locality, address, city, state, country, landmark, zip, type, status, is_shipping_different, delivered_date, canceled_date, created_at, updated_at)
SELECT id, 1398.00, 0.00, 293.58, 1691.58, 'Mehmet Demir', '5302223344', 'Şehitkamil', 'Gazi Mh. 45. Sk. No:7', 'Gaziantep', 'Gaziantep', 'Turkiye', 'Okul karşısı', '270100', 'home', 'ordered', 0, NULL, NULL, '2026-10-01 11:05:00', '2026-10-01 11:05:00'
FROM users WHERE email='mehmet@example.com';
SET @o2 = LAST_INSERT_ID();
INSERT INTO order_items (product_id, order_id, price, quantity, created_at, updated_at) VALUES
((SELECT id FROM products WHERE slug='pembe-ayicik-kolye'),     @o2, 649.00, 1, '2026-10-01 11:05:00', '2026-10-01 11:05:00'),
((SELECT id FROM products WHERE slug='zirkon-tasli-gelin-seti'), @o2, 749.00, 1, '2026-10-01 11:05:00', '2026-10-01 11:05:00');
INSERT INTO transactions (user_id, order_id, mode, status, created_at, updated_at)
SELECT user_id, id, 'card', 'pending', '2026-10-01 11:05:00', '2026-10-01 11:05:00' FROM orders WHERE id=@o2;

-- Sipariş 3: Zeynep - iptal edildi - PayPal
INSERT INTO orders (user_id, subtotal, discount, tax, total, name, phone, locality, address, city, state, country, landmark, zip, type, status, is_shipping_different, delivered_date, canceled_date, created_at, updated_at)
SELECT id, 1499.00, 0.00, 314.79, 1813.79, 'Zeynep Kaya', '5303334455', 'Kadıköy', 'Moda Cd. No:21 D:2', 'İstanbul', 'İstanbul', 'Turkiye', 'Çarşı girişi', '340000', 'home', 'canceled', 0, NULL, '2026-09-05', '2026-09-03 18:40:00', '2026-09-05 09:30:00'
FROM users WHERE email='zeynep@example.com';
SET @o3 = LAST_INSERT_ID();
INSERT INTO order_items (product_id, order_id, price, quantity, created_at, updated_at) VALUES
((SELECT id FROM products WHERE slug='yesil-dantel-elbise'), @o3, 1499.00, 1, '2026-09-03 18:40:00', '2026-09-03 18:40:00');
INSERT INTO transactions (user_id, order_id, mode, status, created_at, updated_at)
SELECT user_id, id, 'paypal', 'declined', '2026-09-03 18:40:00', '2026-09-05 09:30:00' FROM orders WHERE id=@o3;

-- Sipariş 4: Ayşe - teslim edildi - HOSGELDIN10 kuponlu (1327 - %10 = 1194.30)
INSERT INTO orders (user_id, subtotal, discount, tax, total, name, phone, locality, address, city, state, country, landmark, zip, type, status, is_shipping_different, delivered_date, canceled_date, created_at, updated_at)
SELECT id, 1194.30, 132.70, 250.80, 1445.10, 'Ayşe Yılmaz', '5301112233', 'Şahinbey', 'Atatürk Cd. No:12 D:4', 'Gaziantep', 'Gaziantep', 'Turkiye', 'Park yanı', '270000', 'home', 'delivered', 0, '2026-08-24', NULL, '2026-08-20 16:15:00', '2026-08-24 12:00:00'
FROM users WHERE email='ayse@example.com';
SET @o4 = LAST_INSERT_ID();
INSERT INTO order_items (product_id, order_id, price, quantity, created_at, updated_at) VALUES
((SELECT id FROM products WHERE slug='beyaz-oversize-kapusonlu-sweatshirt'), @o4, 499.00, 2, '2026-08-20 16:15:00', '2026-08-20 16:15:00'),
((SELECT id FROM products WHERE slug='yesil-tasli-bileklik-seti'),           @o4, 329.00, 1, '2026-08-20 16:15:00', '2026-08-20 16:15:00');
INSERT INTO transactions (user_id, order_id, mode, status, created_at, updated_at)
SELECT user_id, id, 'cod', 'approved', '2026-08-20 16:15:00', '2026-08-24 12:00:00' FROM orders WHERE id=@o4;

-- ---------- AYLAR (dashboard grafiği) ----------
INSERT INTO month_names (name)
SELECT m.n FROM (SELECT 'January' n UNION ALL SELECT 'February' UNION ALL SELECT 'March' UNION ALL SELECT 'April' UNION ALL SELECT 'May' UNION ALL SELECT 'June' UNION ALL SELECT 'July' UNION ALL SELECT 'August' UNION ALL SELECT 'September' UNION ALL SELECT 'October' UNION ALL SELECT 'November' UNION ALL SELECT 'December') m
WHERE (SELECT COUNT(*) FROM month_names) = 0;
