#!/bin/bash

# Automated setup script for Agrosangapati Laravel Project
# Configures Docker environment and installs Laravel

echo "🚀 Starting Agrosangapati setup..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
  echo "❌ Docker is not running. Please start Docker Desktop first."
  exit 1
fi

echo "✅ Docker is running"

# Build and start containers
echo "📦 Building Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL..."
sleep 10

# Install Laravel if not exists
if [ ! -f "src/artisan" ]; then
    echo "📥 Installing Laravel..."
    docker-compose run --rm php composer create-project laravel/laravel .
    
    # Setup .env
    if [ -f "src/.env.example" ]; then
        cp src/.env.example src/.env
        echo "✅ Environment file created"
    fi
    
    # Update .env untuk database
    docker-compose run --rm php sed -i '' 's/DB_HOST=127.0.0.1/DB_HOST=mysql/g' .env
    docker-compose run --rm php sed -i '' 's/DB_DATABASE=laravel/DB_DATABASE=agrosangapati/g' .env
    docker-compose run --rm php sed -i '' 's/DB_USERNAME=root/DB_USERNAME=agrosangapati_user/g' .env
    docker-compose run --rm php sed -i '' 's/DB_PASSWORD=/DB_PASSWORD=agrosangapati_pass/g' .env
    
    # Generate key
    docker-compose run --rm php php artisan key:generate
    echo "✅ Application key generated"
    
    # Set permissions
    chmod -R 777 src/storage src/bootstrap/cache
    echo "✅ Permissions configured"
    
    # Run migrations
    echo "🗄️  Running database migrations..."
    docker-compose exec -T php php artisan migrate --force
    
    echo ""
    echo "🎉 Setup completed successfully!"
else
    echo "✅ Laravel already installed"
fi

echo ""
echo "═══════════════════════════════════════════════"
echo "🌐 Application: http://agrosangapati.local"
echo "🗄️  PhpMyAdmin: http://localhost:8080"
echo "═══════════════════════════════════════════════"
echo ""
echo "Useful commands:"
echo "  make logs      - View container logs"
echo "  make shell     - Access PHP container"
echo "  make artisan   - Run artisan commands"
echo "  make down      - Stop containers"
echo ""
