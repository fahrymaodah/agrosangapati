# ========================================
# Panduan Push ke GitHub
# ========================================

## Langkah-langkah Push ke GitHub:

### 1. Create New Repository on GitHub
- Visit https://github.com/new
- Repository name: `agrosangapati`
- Description: "Modern Laravel application with Docker containerization"
- Choose Public or Private
- **DO NOT** check "Initialize this repository with a README"
- Click "Create repository"

### 2. Push Project to GitHub

Run the following commands in terminal:

```bash
# Stage all files
git add .

# Initial commit
git commit -m "Initial commit: Laravel Docker setup"

# Add remote repository (replace <username> with your GitHub username)
git remote add origin https://github.com/<username>/agrosangapati.git

# Or if using SSH:
# git remote add origin git@github.com:<username>/agrosangapati.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### 3. Clone on Another Machine

```bash
git clone https://github.com/<username>/agrosangapati.git
cd agrosangapati
./setup.sh
```

## Useful Git Commands

```bash
# Check status
git status

# Stage changes
git add .

# Commit changes
git commit -m "Your commit message"

# Push to GitHub
git push

# Pull latest changes
git pull

# View commit history
git log --oneline --graph

# Create new branch
git checkout -b branch-name

# Switch branch
git checkout main

# View all branches
git branch -a
```

## Files Excluded from Git

These files are already listed in `.gitignore`:
- `/src/vendor/` - Composer dependencies
- `/src/node_modules/` - NPM dependencies
- `/src/.env` - Environment variables (sensitive credentials)
- `/src/storage/` - Laravel temporary files
- `mysql_data/` - MySQL Docker volume data

## Security Best Practices

⚠️ **IMPORTANT**: Never commit `.env` files or credentials to GitHub!

For team collaboration:
1. Update `.env.example` with required variables (without sensitive values)
2. Commit `.env.example` to repository
3. Each developer copies `.env.example` to `.env` and fills in their credentials

## Branching Strategy (Recommended)

For structured development workflow:

```bash
# Development branch
git checkout -b development

# Feature branch
git checkout -b feature/feature-name

# After completion, merge to development
git checkout development
git merge feature/feature-name

# After testing, merge to main
git checkout main
git merge development
git push
```

### Branch Naming Convention

- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Production hotfixes
- `refactor/*` - Code refactoring
- `docs/*` - Documentation updates
