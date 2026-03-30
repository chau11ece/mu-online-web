# MU Online Development Workflow

## 🎯 Overview

**Development** → **Testing** → **Production**

- **develop branch**: Local development with live reload
- **main branch**: Production deployment to danangmu.com

---

## 🛠️ Development Environment Setup

### 1. Start Development Environment

```bash
# Start both web server + database
docker-compose -f docker-compose.dev.yml up -d

# Or rebuild if Dockerfile changed
docker-compose -f docker-compose.dev.yml up -d --build
```

**Access**:
- Website: http://localhost:8080
- Database: localhost:1433 (SQL Server)

### 2. Make Code Changes

Edit files in `Web/` directory:
```bash
cd /Users/mac/Desktop/CPPProjects/mu-online-web/Web
# Edit any PHP files - changes apply IMMEDIATELY on browser refresh!
```

**✅ No rebuild needed** - volume mounts sync code changes live

### 3. View Logs

```bash
# Web server logs
docker-compose -f docker-compose.dev.yml logs -f mu-web-dev

# Database logs
docker-compose -f docker-compose.dev.yml logs -f mu-db-dev
```

### 4. Stop Development Environment

```bash
docker-compose -f docker-compose.dev.yml down
```

---

## 🔄 Git Workflow (Recommended)

### Development Branch (develop)

```bash
# Ensure you're on develop branch
git checkout develop

# Pull latest changes
git pull origin develop

# Make your changes
# ... edit code ...

# Commit changes
git add .
git commit -m "feat: add new feature"

# Push to develop
git push origin develop
```

### Testing Locally

```bash
# Start dev environment
docker-compose -f docker-compose.dev.yml up -d

# Test your changes at http://localhost:8080
# - Check functionality
# - Test database operations
# - Verify no errors

# Stop when done
docker-compose -f docker-compose.dev.yml down
```

### Deploy to Production

When your changes are tested and ready:

```bash
# 1. Merge develop to main
git checkout main
git pull origin main
git merge develop

# 2. Push to main (triggers CI/CD)
git push origin main
```

**⚡ CI/CD Pipeline Auto-Deploys:**
- Builds Docker image
- Pushes to registries
- Deploys to danangmu.com via Ansible
- Verifies health checks

---

## 📂 File Structure

```
mu-online-web/
├── Web/                           # Application code (volume-mounted in dev)
│   ├── application/              # MVC structure
│   ├── assets/                   # CSS, JS, images
│   ├── constants.php             # Configuration
│   ├── index.php                 # Entry point
│   └── Dockerfile                # Production image
├── docker-compose.dev.yml        # Development (local + live reload)
├── docker-compose.prod.yml       # Production reference
└── .github/workflows/
    └── ci-cd-mu-web.yml          # CI/CD pipeline
```

---

## 🔑 Key Commands Reference

### Development

```bash
# Start dev
docker-compose -f docker-compose.dev.yml up -d

# Rebuild (after Dockerfile changes)
docker-compose -f docker-compose.dev.yml up -d --build

# View logs
docker-compose -f docker-compose.dev.yml logs -f

# Shell into container
docker exec -it mu-web-dev bash

# Check database
docker exec -it mu-db-dev /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Abcd@1234' -C

# Stop all
docker-compose -f docker-compose.dev.yml down
```

### Git Operations

```bash
# Check current branch
git branch

# Switch branches
git checkout develop     # for development
git checkout main        # for production

# View changes
git status
git diff

# Create feature branch
git checkout -b feature/new-feature develop
git push -u origin feature/new-feature
```

---

## 🚀 Deployment Flow

```
┌─────────────────┐
│  Edit Code      │  Edit files in Web/ directory
│  (develop)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Test Locally   │  docker-compose -f docker-compose.dev.yml up
│  (localhost)    │  Test at http://localhost:8080
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Commit & Push  │  git push origin develop
│  (develop)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Merge to main  │  git merge develop
│  (main)         │  git push origin main
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  CI/CD Deploy   │  Automatic deployment via GitHub Actions
│  (danangmu.com) │  Build → Test → Push → Deploy
└─────────────────┘
```

---

## ⚠️ Important Notes

### Development Environment
- ✅ Code changes are LIVE (no rebuild)
- ✅ Separate database (safe to test)
- ✅ Debug mode enabled
- ⚠️ Don't commit sensitive data (passwords, keys)

### Production Environment
- ⚠️ Only deploy from `main` branch
- ⚠️ Always test on `develop` first
- ✅ CI/CD handles deployment automatically
- ✅ Health checks verify deployment success

### CI/CD Pipeline
- **Triggers**: Push to `main` branch
- **Build**: Fresh Docker image with --no-cache
- **Deploy**: Ansible playbook to production server
- **Verify**: Health endpoint checks (http://danangmu.com/health.php)

---

## 🐛 Troubleshooting

### Dev Environment Won't Start

```bash
# Check if containers are running
docker ps -a

# View logs for errors
docker-compose -f docker-compose.dev.yml logs

# Clean restart
docker-compose -f docker-compose.dev.yml down -v
docker-compose -f docker-compose.dev.yml up --build
```

### Code Changes Not Reflecting

```bash
# 1. Check volume mount is working
docker exec mu-web-dev ls -la /var/www/html

# 2. Clear PHP/application cache
docker exec mu-web-dev rm -rf /var/www/html/application/data/cache/*

# 3. Restart container
docker-compose -f docker-compose.dev.yml restart mu-web-dev
```

### Database Connection Failed

```bash
# Check database is healthy
docker-compose -f docker-compose.dev.yml ps

# Test connection
docker exec mu-db-dev /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Abcd@1234' -C -Q 'SELECT @@VERSION'
```

---

## 📞 Quick Help

- **CI/CD Status**: https://github.com/chau11ece/mu-online-web/actions
- **Production Health**: http://danangmu.com/health.php
- **Dev Environment**: http://localhost:8080

---

**Document Version**: 1.0
**Last Updated**: 2026-03-29
**Status**: Production Ready
