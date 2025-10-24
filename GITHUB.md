# ========================================
# Panduan Push ke GitHub
# ========================================

## Langkah-langkah Push ke GitHub:

### 1. Buat Repository Baru di GitHub
- Buka https://github.com/new
- Nama repository: `agrosangapati`
- Deskripsi: "Project Laravel Agrosangapati dengan Docker"
- Pilih Public atau Private
- **JANGAN** centang "Initialize this repository with a README"
- Klik "Create repository"

### 2. Push Project ke GitHub

Jalankan perintah berikut di terminal:

```bash
# Add semua file
git add .

# Commit pertama
git commit -m "Initial commit: Laravel project dengan Docker untuk M1"

# Tambahkan remote repository (ganti <username> dengan username GitHub Anda)
git remote add origin https://github.com/<username>/agrosangapati.git

# atau jika menggunakan SSH:
# git remote add origin git@github.com:<username>/agrosangapati.git

# Push ke GitHub
git branch -M main
git push -u origin main
```

### 3. Clone Repository di Komputer Lain

```bash
git clone https://github.com/<username>/agrosangapati.git
cd agrosangapati
./setup.sh
```

## Perintah Git Berguna

```bash
# Cek status
git status

# Add file baru
git add .

# Commit perubahan
git commit -m "Pesan commit Anda"

# Push ke GitHub
git push

# Pull perubahan terbaru
git pull

# Lihat history commit
git log --oneline

# Buat branch baru
git checkout -b nama-branch

# Pindah branch
git checkout main
```

## File yang TIDAK akan di-push ke GitHub

File-file berikut sudah tercantum di `.gitignore`:
- `/src/vendor/` - Dependencies Composer
- `/src/node_modules/` - Dependencies NPM
- `/src/.env` - Environment variables (credential database, dll)
- `/src/storage/` - File temporary Laravel
- `mysql_data/` - Data MySQL Docker volume

## Tips Keamanan

⚠️ **PENTING**: Jangan pernah commit file `.env` atau credential ke GitHub!

Untuk berbagi konfigurasi dengan team:
1. Update file `.env.example` dengan variable yang diperlukan (tanpa value sensitif)
2. Commit file `.env.example`
3. Setiap developer copy `.env.example` ke `.env` dan isi dengan credential mereka

## Branching Strategy (Opsional)

Untuk project yang lebih terstruktur:

```bash
# Branch untuk development
git checkout -b development

# Branch untuk fitur baru
git checkout -b feature/nama-fitur

# Setelah selesai, merge ke development
git checkout development
git merge feature/nama-fitur

# Setelah testing OK, merge ke main
git checkout main
git merge development
git push
```
