# Installation Guide - AgroSangapati

Panduan lengkap instalasi dan konfigurasi sistem AgroSangapati.

---

## 📋 Requirements

### Minimum Requirements
- **OS**: macOS, Linux, or Windows (dengan WSL2)
- **Docker**: 20.10+
- **Docker Compose**: 2.0+
- **RAM**: 4GB minimum (8GB recommended)
- **Storage**: 5GB free space
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

### Optional Tools
- **Git**: Version control
- **TablePlus** atau **PhpMyAdmin**: Database management
- **Postman**: API testing
- **VS Code**: Code editor dengan extensions:
  - PHP Intelephense
  - Laravel Extension Pack
  - Docker

---

## 🚀 Quick Start (5 Minutes)

### 1. Clone Repository
```bash
git clone https://github.com/fahrymaodah/agrosangapati.git
cd agrosangapati
```

### 2. Start Docker Containers
```bash
docker-compose up -d --build
```

### 3. Install Dependencies
```bash
cd src
composer install
```

### 4. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate --seed
```

### 6. Configure Local Domain
```bash
sudo nano /etc/hosts
```

Add this line:
```
127.0.0.1    agrosangapati.local
```

### 7. Access Application
Open browser: **http://agrosangapati.local**

**Default Login**:
- Email: `admin@example.com`
- Password: `password`

---

## 📝 Detailed Installation

### Step 1: System Preparation

#### macOS
```bash
# Install Homebrew (if not installed)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install Docker Desktop
brew install --cask docker

# Start Docker
open /Applications/Docker.app
```

#### Linux (Ubuntu/Debian)
```bash
# Update package index
sudo apt update

# Install Docker
sudo apt install docker.io docker-compose -y

# Add user to docker group
sudo usermod -aG docker $USER

# Start Docker service
sudo systemctl start docker
sudo systemctl enable docker
```

#### Windows (WSL2)
1. Install WSL2: [Microsoft Guide](https://learn.microsoft.com/en-us/windows/wsl/install)
2. Install Docker Desktop: [Docker Desktop](https://www.docker.com/products/docker-desktop/)
3. Enable WSL2 integration in Docker Desktop settings

### Step 2: Clone & Setup

```bash
# Clone repository
git clone https://github.com/fahrymaodah/agrosangapati.git
cd agrosangapati

# Check Docker installation
docker --version
docker-compose --version

# Verify files
ls -la
```

You should see:
```
- docker/
- src/
- docker-compose.yml
- setup.sh
- Makefile
- README.md
```

### Step 3: Docker Configuration

```bash
# Build and start containers
docker-compose up -d --build

# Verify containers are running
docker-compose ps
```

Expected output:
```
NAME                    STATUS    PORTS
agrosangapati_nginx     Up        0.0.0.0:80->80/tcp
agrosangapati_php       Up        9000/tcp
agrosangapati_mysql     Up        0.0.0.0:3306->3306/tcp
```

### Step 4: Laravel Setup

```bash
# Enter src directory
cd src

# Install Composer dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 5: Database Configuration

Edit `.env` file:
```bash
nano .env
```

Update these values:
```env
DB_CONNECTION=mysql
DB_HOST=mysql          # Important: use 'mysql' not 'localhost'
DB_PORT=3306
DB_DATABASE=agrosangapati
DB_USERNAME=agrosangapati_user
DB_PASSWORD=agrosangapati_pass
```

### Step 6: Run Migrations

```bash
# Run migrations
php artisan migrate

# Verify migrations
php artisan migrate:status
```

Expected output:
```
Migration name ................................................. Batch / Status  
0001_01_01_000000_create_users_table ................................ [1] Ran  
0001_01_01_000001_create_cache_table ................................ [1] Ran  
...
```

### Step 7: Seed Database

```bash
# Run all seeders
php artisan db:seed

# Or run specific seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=TransactionSeeder
php artisan db:seed --class=ProductSeeder
```

### Step 8: Configure Local Domain

#### macOS / Linux
```bash
sudo nano /etc/hosts
```

#### Windows (Run Notepad as Administrator)
```
C:\Windows\System32\drivers\etc\hosts
```

Add this line:
```
127.0.0.1    agrosangapati.local
```

Save and exit.

### Step 9: Verify Installation

#### Check Web Access
1. Open browser: **http://agrosangapati.local**
2. You should see the landing page

#### Check Admin Panel
1. Navigate to: **http://agrosangapati.local/admin/login**
2. Login with:
   - Email: `admin@example.com`
   - Password: `password`

#### Check Database
```bash
# Using Tinker
php artisan tinker

# Run these commands
User::count()        // Should return 5
Transaction::count() // Should return 55
Product::count()     // Should return 50+
```

---

## 🔧 Configuration

### Environment Variables

Key `.env` configurations:

```env
# Application
APP_NAME=AgroSangapati
APP_ENV=local
APP_DEBUG=true
APP_URL=http://agrosangapati.local

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=agrosangapati
DB_USERNAME=agrosangapati_user
DB_PASSWORD=agrosangapati_pass

# Mail (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null

# Queue (Optional)
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Docker Customization

Edit `docker-compose.yml` if needed:

```yaml
services:
  nginx:
    ports:
      - "80:80"      # Change to "8000:80" if port 80 is busy
  
  mysql:
    ports:
      - "3306:3306"  # Change to "33060:3306" if port 3306 is busy
```

---

## 🌐 Network Access

### Local Access (Same Computer)
```
http://agrosangapati.local
http://localhost
http://127.0.0.1
```

### LAN Access (Other Devices)

#### 1. Find Your IP Address

**macOS / Linux**:
```bash
ipconfig getifaddr en0    # WiFi
ipconfig getifaddr en1    # Ethernet
```

**Windows**:
```cmd
ipconfig | findstr IPv4
```

Example output: `192.168.100.124`

#### 2. Access from Other Devices

**Direct IP**:
```
http://192.168.100.124
http://192.168.100.124/admin/login
```

**Using Domain** (requires editing hosts file on client device):

**Windows Client**:
```
# Edit: C:\Windows\System32\drivers\etc\hosts
192.168.100.124    agrosangapati.local
```

**Mac/Linux Client**:
```bash
sudo nano /etc/hosts
# Add: 192.168.100.124    agrosangapati.local
```

**Mobile Device**: Use IP address directly (domain tidak work)

---

## 🔐 Default Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|-------------|
| Superadmin | admin@example.com | password | Full system access |
| Ketua Gapoktan | ketua@example.com | password | Gapoktan management |
| Bendahara | bendahara@example.com | password | Financial management |
| Ketua Poktan | ketua.poktan@example.com | password | Poktan management |
| Anggota | anggota@example.com | password | Limited access |

**⚠️ Security Warning**: Change all default passwords in production!

---

## 🧪 Testing Installation

### 1. Check Docker Services
```bash
docker-compose ps
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f mysql
```

### 2. Check PHP
```bash
docker-compose exec php php -v
# Should show: PHP 8.4.1

docker-compose exec php php -m
# Should list installed extensions
```

### 3. Check Database Connection
```bash
cd src
php artisan tinker

# Test database connection
DB::connection()->getPdo();
// Should return PDO object

// Check tables
DB::select('SHOW TABLES');
// Should list all tables
```

### 4. Check Filament
```bash
# List Filament resources
php artisan filament:list

# Check Filament version
composer show filament/filament
```

### 5. Access All URLs

Test these URLs in browser:
- ✅ http://agrosangapati.local (Landing page)
- ✅ http://agrosangapati.local/admin (Redirect to login)
- ✅ http://agrosangapati.local/admin/login (Login page)
- ✅ http://agrosangapati.local/admin/dashboard (After login)

---

## 🐛 Troubleshooting

### Issue: Port 80 Already in Use

**Solution 1**: Stop conflicting service
```bash
# macOS
sudo lsof -i :80
sudo kill -9 <PID>

# Linux
sudo netstat -tulpn | grep :80
sudo kill -9 <PID>
```

**Solution 2**: Change port in docker-compose.yml
```yaml
nginx:
  ports:
    - "8000:80"  # Use port 8000 instead
```

Then access: `http://agrosangapati.local:8000`

### Issue: Permission Denied

```bash
cd src
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Issue: MySQL Connection Refused

**Check MySQL is running**:
```bash
docker-compose ps mysql
docker-compose logs mysql
```

**Restart MySQL**:
```bash
docker-compose restart mysql
```

**Check .env configuration**:
```env
DB_HOST=mysql    # NOT localhost!
DB_PORT=3306
```

### Issue: 404 Not Found

**Clear caches**:
```bash
cd src
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Restart containers**:
```bash
docker-compose restart
```

### Issue: Composer Install Fails

**Update Composer**:
```bash
docker-compose exec php composer self-update
```

**Install with verbose output**:
```bash
docker-compose exec php composer install -vvv
```

### Issue: Cannot Access from Other Devices

**Check Firewall** (macOS):
```bash
# System Preferences → Security & Privacy → Firewall
# Allow incoming connections
```

**Check Docker Network**:
```bash
docker network ls
docker network inspect agrosangapati_network
```

**Verify IP**:
```bash
ipconfig getifaddr en0
# Use this IP from other devices
```

---

## 🔄 Update & Maintenance

### Pull Latest Changes
```bash
git pull origin main
docker-compose down
docker-compose up -d --build
cd src
composer install
php artisan migrate
php artisan optimize:clear
```

### Backup Database
```bash
# Export
docker-compose exec mysql mysqldump -u agrosangapati_user -p agrosangapati > backup.sql

# Import
docker-compose exec -T mysql mysql -u agrosangapati_user -p agrosangapati < backup.sql
```

### Reset Application
```bash
cd src
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

## 📚 Additional Resources

- **Documentation**: See `/docs` folder
- **Laravel Docs**: https://laravel.com/docs
- **Filament Docs**: https://filamentphp.com/docs/4.x
- **Docker Docs**: https://docs.docker.com

---

## ✅ Post-Installation Checklist

- [ ] Docker containers running
- [ ] Database migrated
- [ ] Database seeded
- [ ] Landing page accessible
- [ ] Admin panel accessible
- [ ] Can login with default accounts
- [ ] Dashboard loads correctly
- [ ] Transactions list shows data
- [ ] Products list shows data
- [ ] Reports can be generated
- [ ] LAN access works (if needed)

---

## 🎉 Success!

If all checks pass, your AgroSangapati installation is complete!

**Next Steps**:
1. Change default passwords
2. Configure email settings
3. Add real Gapoktan/Poktan data
4. Train users
5. Start using!

**Need Help?**
- Check TROUBLESHOOTING section
- Review logs: `docker-compose logs -f`
- Contact: info@agrosangapati.com

---

**Document Version**: 1.0  
**Last Updated**: November 13, 2025  
**Tested On**: macOS Sonoma 14.0, Ubuntu 22.04, Windows 11 (WSL2)
