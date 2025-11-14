# AgroSangapati - Sistem Informasi Manajemen Gapoktan

Sistem Informasi Manajemen Keuangan untuk Gabungan Kelompok Tani (Gapoktan) berbasis Laravel 12 dengan Filament 4, menggunakan Docker untuk deployment yang mudah dan konsisten.

## 🎯 Fitur Utama

- ✅ **Dashboard Interaktif** - Statistik real-time pendapatan & pengeluaran
- ✅ **Manajemen Transaksi** - Sistem approval berlapis untuk transparansi
- ✅ **Laporan Keuangan** - Generate laporan lengkap dengan saldo kas
- ✅ **Multi-level User** - Role-based access (Admin, Ketua, Bendahara, Anggota)
- ✅ **Manajemen Produk** - Kelola produk, stok, dan pesanan
- ✅ **Landing Page** - Halaman statis untuk informasi publik
- ✅ **Responsive Design** - Tampil sempurna di desktop & mobile

## 🛠️ Technology Stack

- **Backend**: Laravel 12.36.0
- **Admin Panel**: Filament 4.x
- **PHP**: 8.4.1
- **Database**: MySQL 8.0
- **Web Server**: Nginx (Alpine)
- **Containerization**: Docker & Docker Compose

## 📋 Prerequisites

- Docker & Docker Compose
- Git
- (Opsional) TablePlus / PhpMyAdmin untuk manajemen database

## Installation

### 1. Clone Repository

```bash
git clone <repository-url> agrosangapati
cd agrosangapati
```

### 2. Configure Local Domain

Add local domain to hosts file:

```bash
sudo nano /etc/hosts
```

Add the following line:

```
127.0.0.1    agrosangapati.local
```

Save and exit.

### 3. Install Dependencies

Run automated setup script:

```bash
./setup.sh
```

Or manually:

```bash
# Build and start containers
docker-compose up -d --build

# Install Laravel
docker-compose run --rm php composer create-project laravel/laravel .

# Configure environment
cp src/.env.example src/.env

# Update database configuration in src/.env
# DB_HOST=mysql
# DB_DATABASE=agrosangapati
# DB_USERNAME=agrosangapati_user
# DB_PASSWORD=agrosangapati_pass

# Generate application key
docker-compose run --rm php php artisan key:generate

# Set permissions
chmod -R 777 src/storage src/bootstrap/cache

# Run migrations
docker-compose exec php php artisan migrate
```

## 🌐 Access Points

### Lokal (MacBook)
- **Landing Page**: http://agrosangapati.local
- **Admin Panel**: http://agrosangapati.local/admin/login
- **Database**: localhost:3306

### Dari Jaringan Lain (Windows/Device Lain)
- **Landing Page**: http://192.168.100.124
- **Admin Panel**: http://192.168.100.124/admin/login

> **Note**: Ganti `192.168.100.124` dengan IP MacBook Anda (cek dengan `ipconfig getifaddr en0`)

### Database Credentials
```
Host: localhost (atau mysql dari dalam container)
Port: 3306
Database: agrosangapati
Username: agrosangapati_user
Password: agrosangapati_pass
```

## 👥 User Accounts

Default accounts setelah seeding:

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Superadmin | admin@example.com | password | Full access |
| Ketua Gapoktan | ketua@example.com | password | Gapoktan management |
| Bendahara | bendahara@example.com | password | Financial management |
| Ketua Poktan | ketua.poktan@example.com | password | Poktan management |
| Anggota | anggota@example.com | password | Limited access |

## 🚀 Development Commands

### Artisan Commands

```bash
# Masuk ke direktori src terlebih dahulu
cd src

# Migration
php artisan migrate
php artisan migrate:fresh --seed  # Reset & seed data

# Seeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=TransactionSeeder
php artisan db:seed --class=ProductSeeder

# Cache
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Tinker (Database Console)
php artisan tinker

# Create Filament Resources
php artisan make:filament-resource Product
php artisan make:filament-widget StatsOverviewWidget

# Queue
php artisan queue:work
```

### Composer Commands

```bash
cd src

# Install dependencies
composer install

# Update dependencies
composer update

# Add package
composer require package/name
```

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# Stop and remove volumes
docker-compose down -v
```

## Troubleshooting

### Permission Issues

```bash
chmod -R 777 src/storage src/bootstrap/cache
```

### Port Conflicts

If port 80 is already in use, modify `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8000:80"
```

Then access via: http://agrosangapati.local:8000

## 📁 Project Structure

```
agrosangapati/
├── docker/
│   ├── nginx/
│   │   └── default.conf          # Nginx configuration
│   └── php/
│       └── Dockerfile            # PHP 8.4 + extensions
├── src/                          # Laravel application
│   ├── app/
│   │   ├── Filament/
│   │   │   ├── Resources/        # CRUD resources
│   │   │   │   ├── TransactionResource.php
│   │   │   │   ├── ProductResource.php
│   │   │   │   ├── OrderResource.php
│   │   │   │   └── UserResource.php
│   │   │   ├── Widgets/          # Dashboard widgets
│   │   │   │   ├── StatsOverviewWidget.php
│   │   │   │   ├── RevenueChartWidget.php
│   │   │   │   └── LatestTransactionsWidget.php
│   │   │   └── Pages/
│   │   ├── Models/               # Eloquent models
│   │   │   ├── Transaction.php
│   │   │   ├── Product.php
│   │   │   ├── Order.php
│   │   │   ├── CashBalance.php
│   │   │   ├── Gapoktan.php
│   │   │   └── Poktan.php
│   │   ├── Services/             # Business logic layer
│   │   │   ├── TransactionService.php
│   │   │   ├── FinancialReportService.php
│   │   │   ├── DashboardService.php
│   │   │   └── CashBalanceService.php
│   │   ├── Repositories/         # Data access layer
│   │   │   ├── TransactionRepository.php
│   │   │   ├── FinancialReportRepository.php
│   │   │   └── Contracts/
│   │   └── Providers/
│   │       ├── AppServiceProvider.php
│   │       └── RepositoryServiceProvider.php
│   ├── database/
│   │   ├── migrations/           # Database schema
│   │   └── seeders/              # Demo data
│   │       ├── DatabaseSeeder.php
│   │       ├── UserSeeder.php
│   │       ├── TransactionSeeder.php
│   │       └── ProductSeeder.php
│   ├── resources/
│   │   └── views/
│   │       └── landing.blade.php # Public landing page
│   └── routes/
│       ├── web.php
│       └── api.php
├── docker-compose.yml            # Container orchestration
├── setup.sh                      # Automated setup script
├── Makefile                      # Development shortcuts
├── PROJECT_ANALYSIS.md           # Technical analysis
├── SERVICE_REPOSITORY_PATTERN.md # Architecture docs
└── README.md
```

## 🏗️ Architecture

This project implements the **Service Repository Pattern** with Filament Admin Panel:

### Pattern Flow
```
Filament Resource → Service Layer → Repository Layer → Model → Database
```

### Layers
1. **Filament Resource Layer**: Admin UI (forms, tables, actions)
2. **Service Layer**: Business logic & data transformation
3. **Repository Layer**: Data access & complex queries
4. **Model Layer**: Eloquent ORM
5. **Database Layer**: MySQL storage

### Benefits
- ✅ Clear separation of concerns
- ✅ Highly testable code
- ✅ Easy to maintain and extend
- ✅ Reusable components
- ✅ Admin panel dengan minimal code

For detailed architecture documentation, see [SERVICE_REPOSITORY_PATTERN.md](SERVICE_REPOSITORY_PATTERN.md)

## 📊 Modules & Features

### 1. Dashboard
- **Widgets**:
  - Pendapatan Bulan Ini (dengan perbandingan bulan lalu)
  - Pengeluaran Bulan Ini (dengan perbandingan bulan lalu)
  - Transaksi Menunggu Persetujuan
  - Pengguna Aktif
  - Chart 12 bulan Tren Pendapatan & Pengeluaran
  - Latest Transactions Table

### 2. Manajemen Transaksi
- CRUD transaksi (Income/Expense)
- Multi-level approval system
- Filter by status, type, date
- Export to Excel/PDF
- Approval logs tracking

### 3. Laporan Keuangan
- Saldo Kas per Poktan
- Rincian Transaksi per periode
- Laporan Konsolidasi Gapoktan
- Export ke Excel

### 4. Manajemen Produk & Pesanan
- Product catalog dengan stock
- Order management dengan status tracking
- Order items dengan subtotal calculation
- Shipping cost calculation

### 5. Master Data
- User management dengan roles
- Gapoktan & Poktan management
- Transaction categories
- Cash balance tracking

## 🎨 Dashboard Features

### Stats Cards
```php
- Pendapatan Bulan Ini: Rp 46.150.000 (+0%)
- Pengeluaran Bulan Ini: Rp 8.800.000 (-37%)
- Menunggu Persetujuan: 0 Transaksi
- Pengguna Aktif: 100%
```

### Revenue Chart
- Line chart dengan 12 bulan data
- Income (hijau) vs Expense (merah)
- Filled area untuk visualisasi lebih baik
- Max height: 400px (configurable)

### Latest Transactions
- 5 transaksi terbaru
- Status badge (Approved/Pending/Rejected)
- Amount formatting
- Quick view action

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
