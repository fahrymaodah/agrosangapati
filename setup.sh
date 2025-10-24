#!/bin/bash

# Script untuk setup project Laravel dengan Docker
# Untuk MacBook Air M1

echo "🚀 Memulai setup Agrosangapati Laravel Project..."

# Cek apakah Docker sudah running
if ! docker info > /dev/null 2>&1; then
  echo "❌ Docker tidak berjalan. Silakan start Docker Desktop terlebih dahulu."
  exit 1
fi

echo "✅ Docker sudah berjalan"

# Build dan start containers
echo "📦 Building Docker containers..."
docker-compose up -d --build

# Tunggu MySQL siap
echo "⏳ Menunggu MySQL siap..."
sleep 10

# Install Laravel jika belum ada
if [ ! -f "src/artisan" ]; then
    echo "📥 Installing Laravel..."
    docker-compose run --rm php composer create-project laravel/laravel .
    
    # Setup .env
    if [ -f "src/.env.example" ]; then
        cp src/.env.example src/.env
        echo "✅ File .env dibuat"
    fi
    
    # Update .env untuk database
    docker-compose run --rm php sed -i '' 's/DB_HOST=127.0.0.1/DB_HOST=mysql/g' .env
    docker-compose run --rm php sed -i '' 's/DB_DATABASE=laravel/DB_DATABASE=agrosangapati/g' .env
    docker-compose run --rm php sed -i '' 's/DB_USERNAME=root/DB_USERNAME=agrosangapati_user/g' .env
    docker-compose run --rm php sed -i '' 's/DB_PASSWORD=/DB_PASSWORD=agrosangapati_pass/g' .env
    
    # Generate key
    docker-compose run --rm php php artisan key:generate
    echo "✅ Laravel application key generated"
    
    # Set permissions
    chmod -R 777 src/storage src/bootstrap/cache
    echo "✅ Permissions set"
    
    # Run migrations
    echo "🗄️  Running migrations..."
    docker-compose exec -T php php artisan migrate --force
    
    echo ""
    echo "🎉 Setup selesai!"
else
    echo "✅ Laravel sudah terinstall"
fi

echo ""
echo "═══════════════════════════════════════════════"
echo "🌐 Aplikasi berjalan di: http://agrosangapati.local"
echo "🗄️  PhpMyAdmin: http://localhost:8080"
echo "═══════════════════════════════════════════════"
echo ""
echo "Perintah berguna:"
echo "  make logs      - Lihat logs"
echo "  make shell     - Masuk ke container PHP"
echo "  make artisan   - Jalankan artisan command"
echo "  make down      - Stop containers"
echo ""
