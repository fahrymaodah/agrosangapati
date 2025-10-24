# Docker Port Configuration

## Default Ports Used

- **80** - Nginx (Web Server)
- **3306** - MySQL Database
- **8080** - PhpMyAdmin

## How to Change Ports if Conflict Occurs

If you have another project using the same ports, edit `docker-compose.yml`:

### Change Nginx Port (80)

```yaml
nginx:
  ports:
    - "8000:80"  # Change 80 to 8000 or any available port
```

Access: http://agrosangapati.local:8000

### Change MySQL Port (3306)

```yaml
mysql:
  ports:
    - "3307:3306"  # Change 3306 to 3307 or any available port
```

### Change PhpMyAdmin Port (8080)

```yaml
phpmyadmin:
  ports:
    - "8081:80"  # Change 8080 to 8081 or any available port
```

Access: http://localhost:8081

## Check Port Usage

Before starting, check if ports are in use:

```bash
# Check if port 80 is in use
lsof -i :80

# Check if port 3306 is in use
lsof -i :3306

# Check if port 8080 is in use
lsof -i :8080

# List all Docker containers and their ports
docker ps
```

## Running Multiple Projects Simultaneously

You can run multiple Docker projects at the same time by:

1. **Using different ports** for each project
2. **Different container names** (already configured uniquely per project)
3. **Different volume names** (already configured: `agrosangapati_mysql_data`)
4. **Different network names** (already configured: `agrosangapati_network`)

### Example: Running 2 Projects

**Project 1 (agrosangapati):**
- Nginx: port 80
- MySQL: port 3306
- PhpMyAdmin: port 8080

**Project 2 (another-project):**
- Nginx: port 8000
- MySQL: port 3307
- PhpMyAdmin: port 8081

Both can run simultaneously without conflicts! ✅

## Quick Commands

```bash
# Stop this project
docker-compose down

# Stop and start specific service
docker-compose stop nginx
docker-compose start nginx

# View all running containers
docker ps

# View all containers (including stopped)
docker ps -a
```
