# Changelog - AgroSangapati

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2025-11-13

### 🎉 Initial Release - Production Ready

#### Added
- **Core System**
  - Complete Laravel 12.36.0 application with Filament 4.x admin panel
  - Docker containerization (Nginx + PHP 8.4 + MySQL 8.0)
  - Service Repository Pattern architecture
  - Multi-level role-based access control (5 roles)

- **Database & Models**
  - 15 database tables with complete relationships
  - 12 Eloquent models with proper relationships
  - Comprehensive database seeders (Users, Transactions, Products)
  - Migration files for all entities

- **Features - Transaction Management**
  - Transaction CRUD dengan Filament Resource
  - Multi-level approval system (Pending → Approved/Rejected)
  - Transaction categories management
  - Approval logs untuk audit trail
  - Real-time cash balance updates

- **Features - Product & Order Management**
  - Product catalog dengan categories
  - Stock management system
  - Order processing dengan multiple items
  - Automatic total calculation (afterCreate/afterSave hooks)
  - Order status tracking (Pending → Processing → Completed/Cancelled)

- **Features - Financial Reports**
  - Cash balance tracking per Poktan
  - Cash balance history logging
  - Financial report generation dengan filters
  - Consolidated report untuk Gapoktan
  - Export to Excel capability

- **Dashboard**
  - **StatsOverviewWidget**: 4 KPI cards
    - Pendapatan Bulan Ini (monthly comparison)
    - Pengeluaran Bulan Ini (monthly comparison)
    - Transaksi Menunggu Persetujuan
    - Pengguna Aktif
  - **RevenueChartWidget**: 12-month income vs expense line chart
  - **LatestTransactionsWidget**: 5 latest transactions table

- **Landing Page**
  - Modern static landing page dengan Tailwind CSS
  - 6 sections: Hero, Features, Stats, About, CTA, Contact
  - Responsive design (mobile-first)
  - Smooth scroll navigation
  - Font Awesome icons integration

- **Documentation**
  - Comprehensive README.md
  - SERVICE_REPOSITORY_PATTERN.md (architecture guide)
  - PROJECT_ANALYSIS.md (technical analysis)
  - TASK_LIST.md (progress tracking)
  - CHANGELOG.md (this file)

#### Fixed
- **Dashboard Stats -100% Error**
  - Changed from all-time totals to monthly calculations
  - Added whereMonth() and whereYear() filters
  - Created separate Carbon instances to avoid mutation

- **Chart Width Only Half**
  - Added `$columnSpan = 'full'` to RevenueChartWidget
  - Chart now spans full dashboard width

- **Orders with Total = 0**
  - Fixed Filament relationship timing issue
  - Added afterCreate() hook in CreateOrder
  - Added afterSave() hook in EditOrder
  - Recalculates totals from database after items saved

- **Dashboard Chart Empty**
  - Created comprehensive TransactionSeeder
  - Added October 2025 data (27 transactions)
  - Added November 2025 data (28 transactions)
  - Realistic amounts and descriptions

- **Chart Height Control**
  - Researched Filament 4.x documentation
  - Implemented `$maxHeight = '400px'` property
  - Removed ineffective height control attempts

- **Now() Mutation Bug**
  - Fixed `now()->subMonth()` modifying original object
  - Created separate instances: `$now = now()` and `$lastMonth = now()->subMonth()`
  - Fixed percentage calculations

#### Changed
- **Dashboard Metrics**
  - Renamed "Total Transaksi" to "Pendapatan Bulan Ini"
  - Added "Pengeluaran Bulan Ini" metric
  - Changed from transaction count to amount (Rupiah)
  - More meaningful business KPIs

- **Chart Configuration**
  - Switched from aspectRatio to maxHeight for height control
  - Simplified getOptions() method
  - Removed redundant Chart.js options

#### Security
- Role-based access control implemented
- Password hashing with bcrypt
- CSRF protection enabled
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade escaping
- Mass assignment protection ($fillable)

---

## [0.9.0] - 2025-11-10

### Dashboard Development Phase

#### Added
- StatsOverviewWidget dengan 4 cards
- RevenueChartWidget dengan Chart.js
- LatestTransactionsWidget table

#### Fixed
- Dashboard statistics calculation
- Chart responsive behavior
- Widget ordering and layout

---

## [0.8.0] - 2025-11-07

### Core Features Implementation

#### Added
- TransactionResource dengan approval actions
- ProductResource dengan stock management
- OrderResource dengan items repeater
- Financial report generation
- Cash balance tracking system

---

## [0.7.0] - 2025-11-05

### Service Layer Development

#### Added
- TransactionService for business logic
- FinancialReportService
- DashboardService
- CashBalanceService
- ConsolidatedReportService

---

## [0.6.0] - 2025-11-03

### Repository Layer Implementation

#### Added
- TransactionRepository
- FinancialReportRepository
- DashboardRepository
- ConsolidatedReportRepository
- Repository contracts/interfaces

---

## [0.5.0] - 2025-11-03

### Authentication & Authorization

#### Added
- Role-based access control (5 roles)
- User seeder dengan sample accounts
- Filament authentication setup
- Authorization policies
- Role checking middleware

---

## [0.4.0] - 2025-11-02

### Database Seeding

#### Added
- UserSeeder (5 roles)
- TransactionSeeder (Oct + Nov data)
- ProductSeeder (50+ products)
- Realistic test data

---

## [0.3.0] - 2025-11-01

### Eloquent Models & Relationships

#### Added
- 12 Eloquent models
- Model relationships (hasMany, belongsTo, etc.)
- Model factories
- Model observers

---

## [0.2.0] - 2025-11-01

### Database Schema

#### Added
- 15 database migrations
- Complete table relationships
- Indexes for performance
- Foreign key constraints

---

## [0.1.0] - 2025-10-31

### Initial Setup

#### Added
- Docker configuration (docker-compose.yml)
- Nginx configuration
- PHP 8.4 Dockerfile
- Laravel 12 installation
- Filament 4 installation
- Basic project structure
- Git repository initialization

---

## Version History

| Version | Date | Status | Highlights |
|---------|------|--------|------------|
| 1.0.0 | 2025-11-13 | 🚀 Production | Complete feature set, documentation ready |
| 0.9.0 | 2025-11-10 | ✅ Beta | Dashboard fully functional |
| 0.8.0 | 2025-11-07 | ✅ Alpha | Core features complete |
| 0.7.0 | 2025-11-05 | 🔨 Dev | Service layer done |
| 0.6.0 | 2025-11-03 | 🔨 Dev | Repository layer done |
| 0.5.0 | 2025-11-03 | 🔨 Dev | Auth & roles complete |
| 0.4.0 | 2025-11-02 | 🔨 Dev | Seeding complete |
| 0.3.0 | 2025-11-01 | 🔨 Dev | Models & relationships |
| 0.2.0 | 2025-11-01 | 🔨 Dev | Database schema |
| 0.1.0 | 2025-10-31 | 🎬 Initial | Project setup |

---

## Upcoming Features (Roadmap)

### v1.1.0 (Planned - December 2025)
- [ ] Unit tests untuk Services
- [ ] Feature tests untuk Resources
- [ ] Email notification system
- [ ] Export reports to PDF
- [ ] User manual (Indonesian)

### v1.2.0 (Planned - January 2026)
- [ ] WhatsApp integration
- [ ] Real-time notifications
- [ ] Advanced analytics
- [ ] Custom report builder
- [ ] Mobile app (Flutter)

### v2.0.0 (Planned - Q2 2026)
- [ ] Multi-language support (EN/ID)
- [ ] Multi-tenancy for multiple Gapoktans
- [ ] AI-powered financial predictions
- [ ] Government integration
- [ ] Blockchain transaction verification

---

## Breaking Changes

### None
This is the initial release, no breaking changes.

---

## Migration Guide

### From Development to Production
1. Update `.env` file:
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```
2. Generate production key:
   ```bash
   php artisan key:generate
   ```
3. Run migrations:
   ```bash
   php artisan migrate --force
   ```
4. Seed production data:
   ```bash
   php artisan db:seed --class=UserSeeder
   ```
5. Optimize:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## Contributors

- **Developer**: Copilot AI Assistant
- **Project Owner**: Fahry Maodah
- **Institution**: PMM Hibah Project

---

## License

Open-sourced software licensed under the MIT license.

---

**Changelog Maintained By**: Development Team  
**Last Updated**: November 13, 2025  
**Current Version**: 1.0.0
