# Agrosangapati

Modern Laravel application with Docker containerization for streamlined development and deployment.

## Prerequisites

- Docker & Docker Compose
- Git

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

## Access Points

- **Application**: http://agrosangapati.local
- **PhpMyAdmin**: http://localhost:8080
- **Database**: localhost:3306

## Development Commands

### Artisan Commands

```bash
docker-compose exec php php artisan [command]

# Examples
docker-compose exec php php artisan migrate
docker-compose exec php php artisan make:controller UserController
docker-compose exec php php artisan queue:work
```

### Composer Commands

```bash
docker-compose exec php composer [command]

# Examples
docker-compose exec php composer require package/name
docker-compose exec php composer update
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

## Project Structure

```
agrosangapati/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── src/                    # Laravel application
├── docker-compose.yml
├── setup.sh               # Automated setup script
├── Makefile              # Development shortcuts
├── .gitignore
└── README.md
```

## Technology Stack

- **PHP**: 8.2-FPM
- **Laravel**: 11.x
- **Web Server**: Nginx (Alpine)
- **Database**: MySQL 8.0
- **Database Management**: PhpMyAdmin

## Architecture

This project implements the **Service Repository Pattern** for clean architecture and separation of concerns:

- **Repository Layer**: Handles data access and database queries
- **Service Layer**: Contains business logic and data transformation
- **Controller Layer**: Manages HTTP requests and responses

For detailed documentation, see [SERVICE_REPOSITORY_PATTERN.md](SERVICE_REPOSITORY_PATTERN.md)

### Example Flow
```
HTTP Request → Controller → Service → Repository → Model → Database
```

### Benefits
- Clear separation of concerns
- Highly testable code
- Easy to maintain and extend
- Reusable components

## API Documentation

Base URL: `http://agrosangapati.local/api`

### User Endpoints
- `GET /users` - Get all users (paginated)
- `GET /users/{id}` - Get specific user
- `GET /users/search?q={query}` - Search users
- `GET /users/active` - Get active users
- `POST /users` - Create new user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user

See [SERVICE_REPOSITORY_PATTERN.md](SERVICE_REPOSITORY_PATTERN.md) for detailed API usage and examples.

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
