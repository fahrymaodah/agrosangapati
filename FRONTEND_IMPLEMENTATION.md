# AgroSangapati - Frontend Implementation Plan (Filament PHP)

**Technology**: Filament PHP v3.2  
**Started**: October 29, 2025  
**Target Completion**: November 15, 2025 (12 working days)  
**Current Progress**: 0/8 phases (0%)

---

## 📊 Progress Overview

| Phase | Module | Resources | Pages | Actions | Status | Days |
|-------|--------|-----------|-------|---------|--------|------|
| 1 | Setup & Auth | - | 1 | - | ⏳ Pending | 1 |
| 2 | Keuangan (KEU) | 3 | 3 | 2 | ⏳ Pending | 2 |
| 3 | Hasil Bumi (HBM) | 5 | 3 | 4 | ⏳ Pending | 2 |
| 4 | Pemasaran (PMR) | 4 | 2 | 5 | ⏳ Pending | 2 |
| 5 | User & Poktan | 3 | - | - | ⏳ Pending | 1 |
| 6 | Activity Log | 1 | 1 | - | ⏳ Pending | 0.5 |
| 7 | System Settings | - | 2 | - | ⏳ Pending | 0.5 |
| 8 | Polish & Testing | - | - | - | ⏳ Pending | 2 |
| **TOTAL** | **All Modules** | **16** | **12** | **11+** | **0%** | **10-12** |

---

## 🎯 Phase 1: Setup & Authentication (Day 1)

**Status**: ⏳ Pending  
**Estimated**: 1 day  
**Progress**: 0/5 tasks (0%)

### Tasks:
- [ ] Install Filament package (`composer require filament/filament:"^3.2" -W`)
- [ ] Run Filament installation (`php artisan filament:install --panels`)
- [ ] Create admin user (`php artisan make:filament-user`)
- [ ] Configure authentication with existing User model
- [ ] Setup role-based navigation & access control

### Deliverables:
- ✅ Filament admin panel accessible at `/admin`
- ✅ Login page with email/password
- ✅ Role-based dashboard redirect
- ✅ Navigation menu structure

### Files Created:
- `app/Filament/Pages/Dashboard.php` - Main dashboard
- `app/Providers/Filament/AdminPanelProvider.php` - Panel configuration

---

## 💰 Phase 2: Modul Keuangan (Day 2-3)

**Status**: ⏳ Pending  
**Estimated**: 2 days  
**Progress**: 0/8 tasks (0%)

### 2.1 Resources (CRUD)

#### TransactionCategoryResource
- [ ] Create resource (`php artisan make:filament-resource TransactionCategory`)
- [ ] Form: name, type (income/expense), poktan_id, is_default, description
- [ ] Table: columns with filters (type, poktan)
- [ ] Actions: Edit, Delete (with can_delete check)
- [ ] Soft delete support

**Files**: `app/Filament/Resources/TransactionCategoryResource.php`

#### TransactionResource
- [ ] Create resource (`php artisan make:filament-resource Transaction`)
- [ ] Form fields:
  - Select poktan (relationship)
  - Select type (income/expense)
  - Select category (filtered by type)
  - Amount (currency formatted)
  - Description (textarea)
  - Transaction date (date picker)
  - Receipt photo (file upload with preview)
- [ ] Table columns:
  - Poktan name (searchable)
  - Type (badge: green=income, red=expense)
  - Category
  - Amount (formatted Rp)
  - Date (sortable)
  - Status (badge)
- [ ] Filters:
  - Poktan dropdown
  - Type dropdown
  - Category dropdown
  - Date range
  - Status
- [ ] Actions:
  - Edit
  - View receipt (modal)
  - Approve (if pending, role: ketua)
  - Reject (if pending, role: ketua)
  - Delete (soft delete)
- [ ] Bulk actions:
  - Bulk approve
  - Bulk reject
  - Bulk delete

**Files**: `app/Filament/Resources/TransactionResource.php`

#### CashBalanceResource (Read-only)
- [ ] Create resource (read-only)
- [ ] Table columns:
  - Poktan name
  - Balance (formatted Rp with color coding)
  - Last transaction date
  - Status indicator
- [ ] Filters:
  - Poktan dropdown
  - Balance range
- [ ] Actions:
  - View history (redirect to history page)

**Files**: `app/Filament/Resources/CashBalanceResource.php`

### 2.2 Pages (Custom)

#### Financial Dashboard (Poktan Level)
- [ ] Create page (`php artisan make:filament-page FinancialDashboard`)
- [ ] Widgets:
  - Stats cards: Total Income, Total Expense, Current Balance
  - Income Statement chart (monthly)
  - Cash Flow chart
  - Recent transactions table (last 10)
- [ ] Filter: Date range, Poktan selector (for gapoktan users)

**Files**: 
- `app/Filament/Pages/FinancialDashboard.php`
- `app/Filament/Widgets/FinancialStatsWidget.php`
- `app/Filament/Widgets/IncomeStatementChartWidget.php`
- `app/Filament/Widgets/RecentTransactionsWidget.php`

#### Financial Reports Page
- [ ] Create page with tabs for each report type:
  - Income Statement
  - Cash Flow Statement
  - Balance Sheet
  - Transaction List
  - Category Summary
  - Monthly Trend
- [ ] Export buttons (PDF/Excel) - Phase 6
- [ ] Date range filter
- [ ] Poktan filter (for gapoktan)

**Files**: `app/Filament/Pages/FinancialReports.php`

#### Consolidated Dashboard (Gapoktan Level)
- [ ] Create page (only accessible to gapoktan roles)
- [ ] Widgets:
  - All Poktan summary stats
  - Comparison chart (bar chart by poktan)
  - Consolidated trend (line chart)
  - Top performing Poktans table
- [ ] Date range filter

**Files**: `app/Filament/Pages/ConsolidatedDashboard.php`

---

## 🌾 Phase 3: Modul Hasil Bumi (Day 4-5)

**Status**: ⏳ Pending  
**Estimated**: 2 days  
**Progress**: 0/11 tasks (0%)

### 3.1 Resources

#### CommodityResource
- [ ] Create resource
- [ ] Form: name, unit, market_price, status
- [ ] Table: name, unit, price (formatted), grade count, status badge
- [ ] Relation managers: Grades (nested CRUD)
- [ ] Actions: Edit, Toggle Status, Delete

**Files**: 
- `app/Filament/Resources/CommodityResource.php`
- `app/Filament/Resources/CommodityResource/RelationManagers/GradesRelationManager.php`

#### HarvestResource
- [ ] Create resource
- [ ] Form:
  - Poktan (relationship)
  - Commodity (select)
  - Grade (select, filtered by commodity)
  - Quantity (number)
  - Harvest date (date picker)
  - Reporter (user relationship)
  - Photo (file upload)
  - Notes
  - Status (select: pending/verified/sold)
- [ ] Table:
  - Poktan
  - Commodity + Grade
  - Quantity with unit
  - Date
  - Reporter
  - Status badge
- [ ] Filters: Poktan, Commodity, Status, Date range
- [ ] Actions: Edit, Verify (bulk), Delete
- [ ] Image preview in table

**Files**: `app/Filament/Resources/HarvestResource.php`

#### StockResource
- [ ] Create resource
- [ ] Table (read-mostly):
  - Location
  - Poktan
  - Commodity + Grade
  - Quantity (with low stock indicator)
  - Last updated
- [ ] Filters: Location, Poktan, Commodity
- [ ] Actions:
  - Adjust Stock (modal form)
  - View Movements
  - Transfer to Gapoktan
- [ ] Bulk actions: none (stock safety)

**Files**: `app/Filament/Resources/StockResource.php`

#### StockMovementResource (Read-only)
- [ ] Create resource (view only)
- [ ] Table:
  - Type badge (add/remove/transfer/damage)
  - Stock info (location, commodity, grade)
  - Quantity (with +/- indicator)
  - From/To location
  - Reason
  - Created by
  - Date
- [ ] Filters: Type, Location, Commodity, Date range
- [ ] Export functionality

**Files**: `app/Filament/Resources/StockMovementResource.php`

### 3.2 Pages

#### Production Dashboard (Poktan)
- [ ] Stats widgets:
  - Total harvests
  - Total quantity by commodity
  - Active members
  - This month vs last month
- [ ] Charts:
  - Production by commodity (pie chart)
  - Monthly trend (line chart)
- [ ] Tables:
  - Top producing members
  - Recent harvests

**Files**: `app/Filament/Pages/ProductionDashboard.php`

#### Production Dashboard (Gapoktan)
- [ ] Consolidated stats across all Poktans
- [ ] Comparison by Poktan (bar chart)
- [ ] Best performing members (all Poktans)
- [ ] Commodity distribution

**Files**: `app/Filament/Pages/GapoktanProductionDashboard.php`

#### Stock Dashboard
- [ ] Current stock overview by location
- [ ] Low stock alerts (notification)
- [ ] Stock value calculation
- [ ] Recent movements log

**Files**: `app/Filament/Pages/StockDashboard.php`

### 3.3 Custom Actions

- [ ] Transfer Stock Action (modal with form)
- [ ] Add Stock Action
- [ ] Remove Stock Action
- [ ] Damage Stock Action

**Files**: `app/Filament/Actions/` directory

---

## 🛒 Phase 4: Modul Pemasaran (Day 6-7)

**Status**: ⏳ Pending  
**Estimated**: 2 days  
**Progress**: 0/10 tasks (0%)

### 4.1 Resources

#### ProductResource
- [ ] Create resource
- [ ] Form:
  - Name, description
  - Commodity + Grade (linked)
  - Price, minimum order
  - Stock (sync with warehouse)
  - Multiple photos (repeater/multi-upload)
  - Status (active/inactive)
- [ ] Table: name, commodity, price, stock, status, views
- [ ] Filters: Commodity, Status, Stock level
- [ ] Actions: Edit, Toggle Status, Delete

**Files**: `app/Filament/Resources/ProductResource.php`

#### OrderResource
- [ ] Create resource (mostly read + actions)
- [ ] View page:
  - Order header (number, date, customer)
  - Order items table
  - Total calculation
  - Status workflow
  - Payment status
- [ ] Table:
  - Order number
  - Customer name + phone
  - Total (formatted)
  - Order status badge
  - Payment status badge
  - Date
- [ ] Filters: Status, Payment status, Date range
- [ ] Actions:
  - View details (full page)
  - Confirm order (if pending)
  - Reject order (if pending)
  - Create shipment (if confirmed)
  - Cancel order

**Files**: 
- `app/Filament/Resources/OrderResource.php`
- `app/Filament/Resources/OrderResource/Pages/ViewOrder.php`

#### ShipmentResource
- [ ] Create resource
- [ ] Form:
  - Order (relationship)
  - Courier, tracking number
  - Status (pending/shipped/delivered)
  - Shipped date, delivered date
  - Proof of delivery photo
- [ ] Table:
  - Shipment number
  - Order number (link)
  - Courier
  - Tracking number (clickable)
  - Status badge
  - Date
- [ ] Filters: Courier, Status, Date
- [ ] Actions: Update status, Upload proof

**Files**: `app/Filament/Resources/ShipmentResource.php`

#### SalesDistributionResource
- [ ] Create resource (read + payment action)
- [ ] Table:
  - Order number
  - Poktan
  - Sale amount
  - Poktan share (calculated)
  - Payment status badge
  - Created date
- [ ] Filters: Poktan, Payment status, Date
- [ ] Actions:
  - Mark as Paid (single)
  - Upload payment proof
- [ ] Bulk actions:
  - Bulk mark as paid
  - Generate payment report

**Files**: `app/Filament/Resources/SalesDistributionResource.php`

### 4.2 Pages

#### Marketing Dashboard
- [ ] Stats widgets:
  - Total sales (today, week, month)
  - Total orders
  - Pending payments
  - Delivered orders
- [ ] Charts:
  - Revenue trend (line chart)
  - Orders by status (pie chart)
- [ ] Tables:
  - Top products
  - Recent orders
  - Pending payments (alert)

**Files**: `app/Filament/Pages/MarketingDashboard.php`

#### Sales Reports
- [ ] Tabs for report types:
  - Sales summary
  - Sales by product
  - Sales by poktan
  - Best sellers
  - Revenue analysis
  - Top customers
- [ ] Date filters
- [ ] Export buttons

**Files**: `app/Filament/Pages/SalesReports.php`

### 4.3 Public Pages (Guest Access)

#### Product Catalog
- [ ] Public product listing (no auth required)
- [ ] Search & filter
- [ ] Product detail modal
- [ ] Add to cart (future)

**Files**: `resources/views/catalog.blade.php`

#### Order Tracking
- [ ] Track by order number (no auth)
- [ ] Show order status
- [ ] Show shipment info

**Files**: `resources/views/tracking.blade.php`

---

## 👥 Phase 5: User & Poktan Management (Day 8)

**Status**: ⏳ Pending  
**Estimated**: 1 day  
**Progress**: 0/3 tasks (0%)

### 5.1 Resources

#### UserResource
- [ ] Create resource
- [ ] Form:
  - Name, email, phone
  - Password (only on create/reset)
  - Role (select from enum)
  - Poktan (relationship, conditional on role)
  - Status (active/inactive)
- [ ] Table:
  - Name, email, phone
  - Role badge
  - Poktan
  - Status badge
  - Last login
- [ ] Filters: Role, Poktan, Status
- [ ] Actions:
  - Edit
  - Reset password
  - Toggle status
  - Delete (soft)
- [ ] Bulk actions: Bulk activate/deactivate

**Files**: `app/Filament/Resources/UserResource.php`

#### PoktanResource
- [ ] Create resource
- [ ] Form:
  - Name, code
  - Gapoktan (relationship)
  - Address, phone
  - Chairman name, chairman phone
  - Status
- [ ] Table:
  - Name, code
  - Gapoktan
  - Chairman
  - Member count
  - Status badge
- [ ] Filters: Gapoktan, Status
- [ ] Relation managers:
  - Members (users)
  - Cash balance
  - Recent transactions

**Files**: `app/Filament/Resources/PoktanResource.php`

#### GapoktanResource
- [ ] Create resource (minimal, mostly view)
- [ ] Form: name, address, phone, chairman
- [ ] Table: basic info with poktan count
- [ ] Relation manager: Poktans list

**Files**: `app/Filament/Resources/GapoktanResource.php`

---

## 📊 Phase 6: Activity Log & Monitoring (Day 9 - Morning)

**Status**: ⏳ Pending  
**Estimated**: 0.5 day  
**Progress**: 0/2 tasks (0%)

### 6.1 Resources

#### ActivityLogResource
- [ ] Create resource (read-only)
- [ ] Table:
  - Causer (user who did action)
  - Subject (model affected)
  - Event (created/updated/deleted)
  - Description
  - Changes (old vs new) - modal view
  - Date
- [ ] Filters:
  - Causer
  - Subject type
  - Event type
  - Date range
- [ ] Search: description
- [ ] Actions: View changes (modal with diff)

**Files**: `app/Filament/Resources/ActivityLogResource.php`

### 6.2 Pages

#### Activity Dashboard
- [ ] Stats:
  - Total activities today
  - Most active users
  - Most changed models
- [ ] Recent activities table
- [ ] Charts: Activity over time

**Files**: `app/Filament/Pages/ActivityDashboard.php`

---

## 🔧 Phase 7: System Settings & Backup (Day 9 - Afternoon)

**Status**: ⏳ Pending  
**Estimated**: 0.5 day  
**Progress**: 0/2 tasks (0%)

### 7.1 Pages

#### Backup Management
- [ ] Create page (admin only)
- [ ] Actions:
  - Run Full Backup button
  - Run DB Only button
  - Run Files Only button
- [ ] Table: List backups
  - Filename
  - Size
  - Date
  - Actions: Download, Delete
- [ ] Stats:
  - Last backup date
  - Total backups
  - Total size
  - Health status
- [ ] Schedule info display

**Files**: `app/Filament/Pages/BackupManagement.php`

#### System Settings
- [ ] Create settings page
- [ ] Form fields:
  - App name
  - Logo upload
  - Contact email
  - Contact phone
  - Notification settings
  - Report footer text
- [ ] Save to config/database

**Files**: `app/Filament/Pages/SystemSettings.php`

---

## 🎨 Phase 8: Polish & Testing (Day 10-12)

**Status**: ⏳ Pending  
**Estimated**: 2 days  
**Progress**: 0/15 tasks (0%)

### 8.1 UI/UX Polish

- [ ] Setup custom branding:
  - Upload logo
  - Set brand colors
  - Configure navigation
- [ ] Indonesian translations:
  - Translate all labels
  - Translate validation messages
  - Translate notifications
- [ ] Customize navigation structure:
  - Group by module
  - Set icons
  - Order items
  - Role-based visibility
- [ ] Setup global search:
  - Add searchable models
  - Configure search priorities
- [ ] Configure notifications:
  - Success messages
  - Error handling
  - Toast notifications

### 8.2 Role-Based Access Control

- [ ] Superadmin policies:
  - View all
  - Manage all
- [ ] Ketua Gapoktan policies:
  - View all Poktans
  - Manage Gapoktan data
  - View reports
- [ ] Ketua Poktan policies:
  - View own Poktan only
  - Approve transactions
  - Manage members
- [ ] Anggota policies:
  - View own data only
  - Create harvest reports
  - View own transactions

### 8.3 Business Logic & Validations

- [ ] Transaction approval workflow:
  - Email notification to ketua
  - Approval modal with notes
  - Auto-update cash balance
- [ ] Stock validations:
  - Prevent negative stock
  - Warn on low stock
  - Validate transfer quantities
- [ ] Order workflow:
  - Stock reservation on confirm
  - Stock restoration on cancel
  - Payment validation
- [ ] Sales distribution:
  - Auto-calculate shares
  - Validate payment amounts
  - Generate transaction on payment

### 8.4 Testing

- [ ] Test all CRUD operations
- [ ] Test all workflows (approval, stock, order)
- [ ] Test role-based access
- [ ] Test on mobile devices
- [ ] Test filters and search
- [ ] Test exports
- [ ] Test file uploads
- [ ] Load testing with sample data
- [ ] User acceptance testing

### 8.5 Documentation

- [ ] Create user manual screenshots
- [ ] Document admin workflows
- [ ] Create video tutorials (optional)

---

## 📁 Project Structure

```
app/
├── Filament/
│   ├── Pages/
│   │   ├── Dashboard.php
│   │   ├── FinancialDashboard.php
│   │   ├── FinancialReports.php
│   │   ├── ConsolidatedDashboard.php
│   │   ├── ProductionDashboard.php
│   │   ├── GapoktanProductionDashboard.php
│   │   ├── StockDashboard.php
│   │   ├── MarketingDashboard.php
│   │   ├── SalesReports.php
│   │   ├── ActivityDashboard.php
│   │   ├── BackupManagement.php
│   │   └── SystemSettings.php
│   ├── Resources/
│   │   ├── TransactionCategoryResource.php
│   │   ├── TransactionResource.php
│   │   ├── CashBalanceResource.php
│   │   ├── CommodityResource.php
│   │   ├── HarvestResource.php
│   │   ├── StockResource.php
│   │   ├── StockMovementResource.php
│   │   ├── ProductResource.php
│   │   ├── OrderResource.php
│   │   ├── ShipmentResource.php
│   │   ├── SalesDistributionResource.php
│   │   ├── UserResource.php
│   │   ├── PoktanResource.php
│   │   ├── GapoktanResource.php
│   │   └── ActivityLogResource.php
│   ├── Widgets/
│   │   ├── FinancialStatsWidget.php
│   │   ├── IncomeStatementChartWidget.php
│   │   ├── RecentTransactionsWidget.php
│   │   └── [other widgets...]
│   └── Actions/
│       ├── TransferStockAction.php
│       ├── ApproveTransactionAction.php
│       └── [other custom actions...]
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php
```

---

## 🎯 Success Criteria

### Functionality
- ✅ All 16 resources working with CRUD
- ✅ All 12 custom pages displaying data
- ✅ All custom actions functioning
- ✅ Role-based access working correctly
- ✅ File uploads working (photos, documents)
- ✅ All validations in place

### Performance
- ✅ Page load < 2 seconds
- ✅ Table pagination efficient (50 items/page)
- ✅ Charts render smoothly
- ✅ Search responsive

### UX
- ✅ Mobile responsive (tested on phone)
- ✅ Dark mode working
- ✅ Notifications clear and helpful
- ✅ Navigation intuitive
- ✅ Forms user-friendly

### Quality
- ✅ No console errors
- ✅ No database errors
- ✅ All workflows tested
- ✅ User acceptance passed

---

## 📝 Notes & Decisions

### Technology Choices
- **Filament 3.2**: Latest stable version with full Livewire 3 support
- **Alpine.js**: Built-in for interactions (no manual JS needed)
- **Tailwind CSS**: Built-in for styling
- **ApexCharts**: For dashboard charts (via Filament plugin)

### Design Decisions
- **Color scheme**: Keep Filament default (customizable later)
- **Logo**: Upload via System Settings page
- **Language**: Indonesian (translate all text)
- **Date format**: d/m/Y (Indonesian standard)
- **Currency**: Rp (Indonesian Rupiah)

### Security Decisions
- **Authentication**: Use existing Laravel Sanctum tokens
- **Authorization**: Filament policies + Laravel Gates
- **File uploads**: Validate size, type, dimensions
- **XSS protection**: Filament handles automatically
- **CSRF protection**: Laravel middleware enabled

---

## 🚀 Next Steps

1. **Install Filament** - Run composer command
2. **Create first resource** - Start with TransactionResource
3. **Test workflow** - Ensure CRUD working
4. **Iterate** - Move to next resource
5. **Daily commits** - Push progress to GitHub

---

**Last Updated**: October 29, 2025  
**Status**: Ready to start Phase 1
