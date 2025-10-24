# Agrosangapati - Laravel Docker Project

Project Laravel dengan Docker untuk MacBook Air M1.

## Persyaratan

- Docker Desktop untuk Mac (dengan dukungan M1)
- Git

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url> agrosangapati
cd agrosangapati
```

### 2. Setup Hosts File

Tambahkan domain lokal ke file hosts:

```bash
sudo nano /etc/hosts
```

Tambahkan baris berikut:

```
127.0.0.1    agrosangapati.local
```

Simpan dengan `CTRL + O`, lalu `CTRL + X`.

### 3. Install Laravel

Jalankan perintah berikut untuk membuat project Laravel baru:

```bash
docker-compose run --rm php composer create-project laravel/laravel .
```

### 4. Setup Environment

Copy file `.env.example` ke `.env`:

```bash
cp src/.env.example src/.env
```

Update konfigurasi database di `src/.env`:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=agrosangapati
DB_USERNAME=agrosangapati_user
DB_PASSWORD=agrosangapati_pass
```

### 5. Generate Application Key

```bash
docker-compose run --rm php php artisan key:generate
```

### 6. Set Permissions

```bash
sudo chmod -R 777 src/storage src/bootstrap/cache
```

### 7. Jalankan Docker

```bash
docker-compose up -d
```

### 8. Migrasi Database

```bash
docker-compose exec php php artisan migrate
```

## Akses Aplikasi

- **Website**: http://agrosangapati.local
- **PhpMyAdmin**: http://localhost:8080

## Perintah Artisan

Untuk menjalankan perintah Laravel Artisan:

```bash
docker-compose exec php php artisan [command]
```

Contoh:
```bash
docker-compose exec php php artisan migrate
docker-compose exec php php artisan make:controller UserController
```

## Perintah Composer

```bash
docker-compose exec php composer [command]
```

## Menghentikan Docker

```bash
docker-compose down
```

## Menghentikan dan Menghapus Volume

```bash
docker-compose down -v
```

## Troubleshooting

### Permission Issues

Jika ada masalah permission:

```bash
sudo chmod -R 777 src/storage src/bootstrap/cache
```

### Port Conflict

Jika port 80 sudah digunakan, edit `docker-compose.yml` dan ubah port nginx:

```yaml
ports:
  - "8000:80"
```

Lalu akses via: http://agrosangapati.local:8000

## Struktur Project

```
agrosangapati/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── src/                    # Laravel application
├── docker-compose.yml
├── .gitignore
└── README.md
```

## Lisensi

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
