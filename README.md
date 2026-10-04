# 🛍️ Opale Store

🌐 **[English](#english)** | **[Türkçe](#turkce)**

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)

---

<a id="english"></a>

## 🇬🇧 English

> 🌐 [Türkçe sürüm için tıkla ⬇️ / Jump to Türkçe](#turkce)

An **e-commerce web application** built with Laravel 11 (Turkish UI). The storefront offers a shop, cart, wishlist, coupons and orders; the admin panel manages products, brands, categories, slides, coupons, orders and contact messages.

---

### 📑 Table of Contents
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Admin Login](#-admin-login)
- [Project Structure](#-project-structure)
- [Database](#-database)
- [Pages and Routes](#-pages-and-routes)
- [Tests](#-tests)
- [Troubleshooting](#-troubleshooting)
- [Roadmap](#-roadmap)
- [License](#-license)

---

### ✨ Features

#### Storefront
- Sign up, log in, password reset
- Home page: slider, categories, featured and discounted products
- Shop: brand / category / price range filters, sorting, page size selection
- Product detail page, live product search
- Cart: increase / decrease quantity, remove item, clear cart
- Wishlist: add, remove, move to cart
- Coupon codes: fixed or percentage discount, minimum cart value, expiry date
- Checkout: saved default address or a new address, VAT calculation
- My account: order list, order details, order cancellation
- Contact form, About Us page

#### Admin panel (`/admin`)
- Dashboard: total / pending / delivered / canceled order amounts, monthly chart, latest orders
- Brand, category, product (multiple images), slide and coupon management (create / edit / delete)
- Order list, order details and **order status updates** (Ordered → Delivered / Canceled)
- View and delete messages sent through the contact form
- Product search inside the admin panel

#### Security
- Admin routes are protected by the `auth` + `AuthAdmin` middleware (`users.utype = 'ADM'`)
- The price is read from the database when adding to cart, never from the form
- Users can only view / cancel their own orders
- Order creation runs inside a single database transaction

---

### 🧰 Tech Stack

| Layer | Used |
|---|---|
| Backend | PHP 8.2+, Laravel 11, Laravel UI (auth scaffolding) |
| Database | MySQL / MariaDB (XAMPP compatible), SQLite for tests |
| Cart | `surfsidemedia/shoppingcart` |
| Image processing | `intervention/image` 2.7 (GD) |
| Frontend | Blade, Bootstrap 5, jQuery, Swiper, Bootstrap Slider |
| Other | `mews/purifier`, `laravel-lang/lang` |
| CI | GitHub Actions (`php artisan test`) |

---

### 🚀 Installation

#### Requirements
- PHP **8.2+** (with the `gd`, `mbstring`, `pdo_mysql`, `fileinfo` extensions enabled)
- Composer
- Node.js and npm
- MySQL (with XAMPP, start Apache and MySQL)

#### Steps

```bash
# 1) Clone the project
git clone https://github.com/fatemalzahraa/Opale-Store.git
cd Opale-Store

# 2) Install dependencies
composer install
npm install

# 3) Prepare the environment file
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Check the database settings in `.env` (XAMPP defaults):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=opalstore
DB_USERNAME=root
DB_PASSWORD=
```

Create an empty database named `opalstore` in phpMyAdmin (collation: `utf8mb4_general_ci`), then:

```bash
# 4) Create tables + insert initial data (month names, admin user)
php artisan migrate --seed

# 5) Storage link for uploaded files
php artisan storage:link

# 6) (Optional) build the frontend
npm run build

# 7) Start the server
php artisan serve
```

Site: **http://localhost:8000**

> If your database is already set up, `php artisan migrate` and `php artisan db:seed --class=AdminSeeder` are enough; existing data is left untouched.

#### Sample data (optional)
To see the store populated, **import** `database/seed_data.sql` into the `opalstore` database in phpMyAdmin (or paste it into the SQL tab). It adds 6 categories, 4 brands, 17 products, 2 slides, 3 coupons, 3 customers, 4 orders and 2 contact messages; images point to the existing files in `public/uploads/`.

- Existing records are left alone (`INSERT IGNORE` / `NOT EXISTS`), but orders are inserted again on every run, so **run the file only once**.
- Sample customer login: `ayse@example.com` / `Password123`
- Coupons: `HOSGELDIN10` (10%, min. 500 TRY), `INDIRIM100` (100 TRY, min. 1000 TRY), `YAZ25` (25%, min. 2000 TRY)

---

### 🔐 Admin Login

`AdminSeeder` creates the default administrator account:

| Email | Password |
|---|---|
| `admin@opale.com` | `Admin12345` |

**Change the password right after your first login.** Admin panel: `/admin`

To make someone else an admin, set `utype` to `ADM` in the `users` table (regular user: `USR`).

---

### 🗂️ Project Structure

```
Opale_Store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      # Admin panel (CRUD, orders, dashboard)
│   │   │   ├── CartController.php       # Cart, coupon, checkout, order creation
│   │   │   ├── ShopController.php       # Shop listing and product details
│   │   │   ├── UserController.php       # My account, my orders, cancellation
│   │   │   ├── WishlistController.php   # Wishlist
│   │   │   └── HomeController.php       # Home page, contact, search
│   │   └── Middleware/AuthAdmin.php     # Admin authorization check
│   └── Models/                          # Eloquent models
├── config/                              # cart.php (VAT rate), image.php, ...
├── database/
│   ├── migrations/                      # Table definitions
│   ├── seeders/                         # MonthSeeder, AdminSeeder
│   └── seed_data.sql                    # Optional sample data
├── public/
│   ├── assets/                          # Theme CSS / JS / images
│   └── uploads/                         # Uploaded product, brand, category, slide images
├── resources/views/
│   ├── admin/                           # Admin panel pages
│   ├── auth/                            # Login, register, password reset
│   ├── user/                            # My account pages
│   └── layouts/                         # app.blade.php, admin.blade.php
├── routes/web.php                       # All routes
└── tests/Feature/ShopFlowTest.php       # End-to-end tests
```

---

### 🗄️ Database

| Table | Description |
|---|---|
| `users` | Users (`utype`: `ADM` / `USR`) |
| `categories`, `brands` | Product categories and brands |
| `products` | Products (price, sale price, SKU, stock status, images) |
| `slides` | Home page slider |
| `coupons` | Discount coupons (`fixed` / `percent`) |
| `addresses` | User addresses (`is_default`) |
| `orders`, `order_items` | Orders and order lines (`ordered` / `delivered` / `canceled`) |
| `transactions` | Payment records (`cod` / `card` / `paypal`; `pending` / `approved` / `declined` / `refunded`) |
| `contacts` | Contact form messages |
| `month_names` | Month names for the dashboard chart |
| `shoppingcart` | Table used by the cart package |
| `sessions`, `cache`, `jobs`, ... | Laravel system tables |

Main relationships: `Product → Category / Brand`, `Order → User / OrderItem / Transaction`, `OrderItem → Product`.

---

### 🧭 Pages and Routes

| URL | Description | Access |
|---|---|---|
| `/` | Home page | Everyone |
| `/shop`, `/shop/{slug}` | Shop, product details | Everyone |
| `/cart`, `/wishlist` | Cart, wishlist | Everyone |
| `/contact-us`, `/aboutus` | Contact, About Us | Everyone |
| `/checkout`, `/place-an-order` | Checkout and ordering | Logged-in users |
| `/account-orders`, `/account-order/details/{id}` | My orders | Logged-in users |
| `/admin` | Dashboard | Admin |
| `/admin/products`, `/admin/brands`, `/admin/categories` | Catalog management | Admin |
| `/admin/orders` | Orders | Admin |
| `/admin/coupon`, `/admin/slides`, `/admin/contacts` | Coupons, slides, messages | Admin |

For the full list of routes: `php artisan route:list`

---

### 🧪 Tests

```bash
php artisan test
```

Tests use **in-memory SQLite** and never touch your MySQL data. Coverage: public pages, admin authorization, the cart → coupon → order flow, price tampering prevention, not being able to cancel someone else's order, admin order status updates, the contact form and the wishlist.

---

### 🩺 Troubleshooting

| Error | Fix |
|---|---|
| `Unknown database 'opalstore'` | Create the `opalstore` database in phpMyAdmin |
| `No application encryption key has been specified` | `php artisan key:generate` |
| `Base table or view already exists` | The tables already exist; `php artisan migrate` is enough, `migrate:fresh` **deletes all data** |
| Images not showing | Run `php artisan storage:link` and check the `public/uploads` folder |
| Changes not showing on the page | `php artisan optimize:clear` |
| Image upload error | Make sure the `gd` extension is enabled in PHP (`php.ini` → `extension=gd`) |

---

### 📌 Roadmap
- [ ] Decrease stock (`quantity`) after an order and out-of-stock checks
- [ ] Card / PayPal payment integration (only cash on delivery is complete for now)
- [ ] Invoice (PDF) generation in the admin panel
- [ ] Order email notifications
- [ ] Multi-language support

---

### 📄 License
This project is released under the [MIT](LICENSE) license.

---

<a id="turkce"></a>

## 🇹🇷 Türkçe

> 🌐 [Switch to English ⬆️ / İngilizce sürüm](#english)

Laravel 11 ile geliştirilmiş, Türkçe arayüzlü bir **e-ticaret web uygulaması**. Müşteri tarafında mağaza, sepet, favoriler, kupon ve sipariş; yönetici tarafında ürün, marka, kategori, slide, kupon, sipariş ve mesaj yönetimi içerir.

---

### 📑 İçindekiler
- [Özellikler](#-özellikler)
- [Teknolojiler](#-teknolojiler)
- [Kurulum](#-kurulum)
- [Admin girişi](#-admin-girişi)
- [Klasör yapısı](#-klasör-yapısı)
- [Veritabanı](#-veritabanı)
- [Sayfalar ve rotalar](#-sayfalar-ve-rotalar)
- [Testler](#-testler)
- [Sık karşılaşılan hatalar](#-sık-karşılaşılan-hatalar)
- [Yapılacaklar](#-yapılacaklar)
- [Lisans](#-lisans)

---

### ✨ Özellikler

#### Müşteri tarafı
- Üye olma, giriş yapma, şifre sıfırlama
- Ana sayfa: slider, kategoriler, öne çıkan ve indirimli ürünler
- Mağaza: marka / kategori / fiyat aralığı filtresi, sıralama, sayfa boyutu seçimi
- Ürün detay sayfası, canlı ürün arama
- Sepet: adet artır / azalt, ürün sil, sepeti boşalt
- Favoriler (wishlist): ekle, sil, sepete taşı
- Kupon kodu: sabit tutar veya yüzde indirimi, minimum sepet tutarı, son kullanma tarihi
- Ödeme sayfası: kayıtlı varsayılan adres veya yeni adres, KDV hesabı
- Hesabım: sipariş listesi, sipariş detayı, sipariş iptali
- İletişim formu, Hakkımızda sayfası

#### Yönetim paneli (`/admin`)
- Dashboard: toplam / bekleyen / teslim edilen / iptal edilen sipariş tutarları, aylık grafik, son siparişler
- Marka, kategori, ürün (çoklu görsel), slide ve kupon yönetimi (ekle / düzenle / sil)
- Sipariş listesi, sipariş detayı ve **sipariş durumu güncelleme** (Ordered → Delivered / Canceled)
- İletişim formundan gelen mesajları görüntüleme ve silme
- Admin içi ürün arama

#### Güvenlik
- Admin rotaları `auth` + `AuthAdmin` middleware ile korunur (`users.utype = 'ADM'`)
- Sepete eklerken fiyat formdan değil veritabanından okunur
- Kullanıcı yalnızca kendi siparişini görüntüleyebilir / iptal edebilir
- Sipariş oluşturma tek bir veritabanı işlemi (transaction) içinde yapılır

---

### 🧰 Teknolojiler

| Katman | Kullanılanlar |
|---|---|
| Backend | PHP 8.2+, Laravel 11, Laravel UI (auth iskeleti) |
| Veritabanı | MySQL / MariaDB (XAMPP uyumlu), testlerde SQLite |
| Sepet | `surfsidemedia/shoppingcart` |
| Görsel işleme | `intervention/image` 2.7 (GD) |
| Frontend | Blade, Bootstrap 5, jQuery, Swiper, Bootstrap Slider |
| Diğer | `mews/purifier`, `laravel-lang/lang` |
| CI | GitHub Actions (`php artisan test`) |

---

### 🚀 Kurulum

#### Gereksinimler
- PHP **8.2+** (`gd`, `mbstring`, `pdo_mysql`, `fileinfo` eklentileri açık)
- Composer
- Node.js ve npm
- MySQL (XAMPP kullanıyorsan Apache + MySQL'i başlat)

#### Adımlar

```bash
# 1) Projeyi indir
git clone https://github.com/fatemalzahraa/Opale-Store.git
cd Opale-Store

# 2) Bağımlılıkları kur
composer install
npm install

# 3) Ortam dosyasını hazırla
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

`.env` dosyasında veritabanı bilgilerini kontrol et (XAMPP varsayılanı):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=opalstore
DB_USERNAME=root
DB_PASSWORD=
```

phpMyAdmin'de `opalstore` adında boş bir veritabanı oluştur (karşılaştırma: `utf8mb4_general_ci`), sonra:

```bash
# 4) Tabloları oluştur + başlangıç verilerini (aylar, admin kullanıcı) ekle
php artisan migrate --seed

# 5) Yüklenen dosyalar için storage bağlantısı
php artisan storage:link

# 6) (İsteğe bağlı) frontend derleme
npm run build

# 7) Sunucuyu başlat
php artisan serve
```

Site: **http://localhost:8000**

> Veritabanın zaten kuruluysa sadece `php artisan migrate` ve `php artisan db:seed --class=AdminSeeder` yeterlidir; mevcut verilere dokunulmaz.

#### Örnek veri (isteğe bağlı)
Mağazayı dolu görmek için `database/seed_data.sql` dosyasını phpMyAdmin'de `opalstore` veritabanına **İçe aktar** (veya SQL sekmesine yapıştır). Dosya 6 kategori, 4 marka, 17 ürün, 2 slide, 3 kupon, 3 müşteri, 4 sipariş ve 2 iletişim mesajı ekler; görseller `public/uploads/` içindeki mevcut dosyalara bağlıdır.

- Mevcut kayıtlara dokunmaz (`INSERT IGNORE` / `NOT EXISTS`), ama siparişler her çalıştırmada tekrar eklenir, **dosyayı bir kez çalıştır**.
- Örnek müşteri girişi: `ayse@example.com` / `Password123`
- Kuponlar: `HOSGELDIN10` (%10, min. 500 TL), `INDIRIM100` (100 TL, min. 1000 TL), `YAZ25` (%25, min. 2000 TL)

---

### 🔐 Admin girişi

`AdminSeeder` varsayılan yönetici hesabını oluşturur:

| E-posta | Şifre |
|---|---|
| `admin@opale.com` | `Admin12345` |

Giriş yaptıktan sonra **şifreyi mutlaka değiştir**. Panel adresi: `/admin`

Başka birini admin yapmak için `users` tablosunda `utype` alanını `ADM` yap (normal kullanıcı: `USR`).

---

### 🗂️ Klasör yapısı

```
Opale_Store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      # Admin paneli (CRUD, siparişler, dashboard)
│   │   │   ├── CartController.php       # Sepet, kupon, ödeme, sipariş oluşturma
│   │   │   ├── ShopController.php       # Mağaza listesi ve ürün detayı
│   │   │   ├── UserController.php       # Hesabım, siparişlerim, iptal
│   │   │   ├── WishlistController.php   # Favoriler
│   │   │   └── HomeController.php       # Ana sayfa, iletişim, arama
│   │   └── Middleware/AuthAdmin.php     # Admin yetki kontrolü
│   └── Models/                          # Eloquent modelleri
├── config/                              # cart.php (KDV oranı), image.php, ...
├── database/
│   ├── migrations/                      # Tablo tanımları
│   └── seeders/                         # MonthSeeder, AdminSeeder
├── public/
│   ├── assets/                          # Tema CSS / JS / görseller
│   └── uploads/                         # Yüklenen ürün, marka, kategori, slide görselleri
├── resources/views/
│   ├── admin/                           # Yönetim paneli sayfaları
│   ├── auth/                            # Giriş, kayıt, şifre sıfırlama
│   ├── user/                            # Hesabım sayfaları
│   └── layouts/                         # app.blade.php, admin.blade.php
├── routes/web.php                       # Tüm rotalar
└── tests/Feature/ShopFlowTest.php       # Uçtan uca testler
```

---

### 🗄️ Veritabanı

| Tablo | Açıklama |
|---|---|
| `users` | Kullanıcılar (`utype`: `ADM` / `USR`) |
| `categories`, `brands` | Ürün kategorileri ve markaları |
| `products` | Ürünler (fiyat, indirimli fiyat, SKU, stok durumu, görseller) |
| `slides` | Ana sayfa slider'ı |
| `coupons` | İndirim kuponları (`fixed` / `percent`) |
| `addresses` | Kullanıcı adresleri (`is_default`) |
| `orders`, `order_items` | Siparişler ve sipariş kalemleri (`ordered` / `delivered` / `canceled`) |
| `transactions` | Ödeme kayıtları (`cod` / `card` / `paypal`; `pending` / `approved` / `declined` / `refunded`) |
| `contacts` | İletişim formu mesajları |
| `month_names` | Dashboard grafiği için ay adları |
| `shoppingcart` | Sepet paketinin tablosu |
| `sessions`, `cache`, `jobs`, ... | Laravel sistem tabloları |

Temel ilişkiler: `Product → Category / Brand`, `Order → User / OrderItem / Transaction`, `OrderItem → Product`.

---

### 🧭 Sayfalar ve rotalar

| URL | Açıklama | Erişim |
|---|---|---|
| `/` | Ana sayfa | Herkes |
| `/shop`, `/shop/{slug}` | Mağaza, ürün detayı | Herkes |
| `/cart`, `/wishlist` | Sepet, favoriler | Herkes |
| `/contact-us`, `/aboutus` | İletişim, hakkımızda | Herkes |
| `/checkout`, `/place-an-order` | Ödeme ve sipariş | Giriş yapmış kullanıcı |
| `/account-orders`, `/account-order/details/{id}` | Siparişlerim | Giriş yapmış kullanıcı |
| `/admin` | Dashboard | Admin |
| `/admin/products`, `/admin/brands`, `/admin/categories` | Katalog yönetimi | Admin |
| `/admin/orders` | Siparişler | Admin |
| `/admin/coupon`, `/admin/slides`, `/admin/contacts` | Kupon, slide, mesajlar | Admin |

Tüm rotaların listesi için: `php artisan route:list`

---

### 🧪 Testler

```bash
php artisan test
```

Testler **bellekte SQLite** kullanır; MySQL verilerine dokunmaz. Kapsam: herkese açık sayfalar, admin yetki kontrolü, sepet → kupon → sipariş akışı, fiyat manipülasyonunun engellenmesi, başkasının siparişini iptal edememe, admin sipariş durumu güncelleme, iletişim formu ve favoriler.

---

### 🩺 Sık karşılaşılan hatalar

| Hata | Çözüm |
|---|---|
| `Unknown database 'opalstore'` | phpMyAdmin'de `opalstore` veritabanını oluştur |
| `No application encryption key has been specified` | `php artisan key:generate` |
| `Base table or view already exists` | Tablolar zaten var; `php artisan migrate` yeterli, `migrate:fresh` **tüm verileri siler** |
| Görseller görünmüyor | `php artisan storage:link` çalıştır, `public/uploads` klasörünü kontrol et |
| Değişiklikler sayfaya yansımıyor | `php artisan optimize:clear` |
| Görsel yükleme hatası | PHP'de `gd` eklentisinin açık olduğundan emin ol (`php.ini` → `extension=gd`) |

---

### 📌 Yapılacaklar
- [ ] Sipariş sonrası stok (`quantity`) düşümü ve stokta yok kontrolü
- [ ] Kart / PayPal ödeme entegrasyonu (şu an yalnızca kapıda ödeme tamamlanmış durumda)
- [ ] Admin tarafında fatura (PDF) oluşturma
- [ ] Sipariş e-posta bildirimleri
- [ ] Çoklu dil desteği

---

### 📄 Lisans
Bu proje [MIT](LICENSE) lisansı ile paylaşılmıştır.
