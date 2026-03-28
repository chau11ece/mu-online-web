# Dev → Prod Containerization Workflow for MU Online Web

## Status: [x] 0% - Planning | [x] 25% - Docker Fix | [x] 50% - Dev Running | [x] 75% - Prod Config | [x] 100% - Deploy Ready

✅ **COMPLETE**: Local dev fully running (http://localhost:8080).
✅ Added: `docker-compose.prod.yml`, `.env.dev.example`, `.env.prod.example`.

**Final Workflow**:
### Dev (Local Test)
```
cp .env.dev.example .env.dev
docker-compose -f docker-compose.dev.yml up -d
open http://localhost:8080
```

### Prod Deploy (DO Droplets)
```
# On prod server
cp .env.prod.example .env.prod  # Edit SQL_SERVER
docker compose -f docker-compose.prod.yml up --build -d
```

Isolation achieved: Test code/DB local → deploy without data/config issues.


### 1. Fix Docker CLI (Done by BLACKBOXAI)
```
docker-compose -f docker-compose.dev.yml up --build -d
```
- Access: http://localhost:8080
- DB: localhost:1433 (sa/Abcd@1234)
- Logs: `docker-compose -f docker-compose.dev.yml logs -f`
- Stop: `docker-compose -f docker-compose.dev.yml down -v` (wipe DB volume)

### 2. Local Dev Testing
- Edit `Web/` → auto-reload (volume mount)
- Test DB changes (local MSSQL restores `MuOnline_Prod.bak`)
- Verify: `curl http://localhost:8080/health.php`

### 3. Prod Deploy (DO Droplets - danangmu.com)
```
# On prod server:
docker-compose -f docker-compose.prod.yml up --build -d
```
- Uses prod MSSQL (config in `.env.prod`)
- `rsync -avz Web/ user@prod:/path/to/Web/` then rebuild

### 4. Environments
| Env | Compose | DB | Code |
|-----|---------|----|------|
| Dev | dev.yml | Local MSSQL | Volume (live edit) |
| Prod Test | local.yml | Prod DB (tunnel) | Built image |
| Prod Live | prod.yml | Prod MSSQL | Built image |

### 5. Commands
```
# Dev
docker-compose -f docker-compose.dev.yml up --build -d
docker-compose -f docker-compose.dev.yml logs -f mu-web-dev

# Prod Test (tunnel open)
docker-compose -f docker-compose.local.yml up --build -d

# Clean dev DB
docker volume rm mu-online-web_mssql_dev_data
```

