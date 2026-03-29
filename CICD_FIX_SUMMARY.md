# CI/CD Pipeline Fix - Homepage 500 Error

## Problem
The CI/CD pipeline was failing at the "Test homepage responds" step with a 500 error:
```
Homepage returned 500
Error: Process completed with exit exit code 1.
```

## Root Cause
The test job was starting a web container with database environment variables, but **no database service was running**. The health.php endpoint was designed to be resilient and skip database checks if the driver wasn't available, but the homepage (index.php) loads the full CMS which attempts to connect to the database and fails with a 500 error.

## Solution
Added a SQL Server database service to the test job using GitHub Actions service containers.

### Changes Made to `.github/workflows/ci-cd-mu-web.yml`

1. **Added database service** (lines 61-75):
   ```yaml
   services:
     mu-db-dev:
       image: mcr.microsoft.com/mssql/server:2019-latest
       env:
         ACCEPT_EULA: Y
         MSSQL_SA_PASSWORD: Abcd@1234
         MSSQL_PID: Express
       ports:
         - 1433:1433
       options: >-
         --health-cmd "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Abcd@1234' -C -Q 'SELECT 1' || exit 1"
         --health-interval 10s
         --health-timeout 5s
         --health-retries 20
         --health-start-period 45s
   ```

2. **Web container already configured** to connect to database:
   - The existing container startup already had the correct environment variables:
     - `DB_HOST=mu-db-dev`
     - `DB_NAME=MuOnline`
     - `DB_USER=sa`
     - `DB_PASS=Abcd@1234`

## How It Works

1. **Service Containers**: GitHub Actions automatically creates a network for service containers and makes them accessible via their service name
2. **Health Checks**: The database service includes health checks to ensure SQL Server is ready before tests run
3. **Network Communication**: The web container connects to the database service using the service name `mu-db-dev` on port 1433

## Verification Steps

After pushing these changes:

1. **Monitor the pipeline** in GitHub Actions
2. **Check the test job logs** for:
   - Database service starting successfully
   - Health check passing
   - Homepage returning 200 OK

3. **Expected behavior**:
   - Database service starts and becomes healthy
   - Web container connects to database
   - Health.php endpoint returns 200 with status "ok"
   - Homepage returns 200 (no more 500 error)

## Additional Notes

- The database uses the same credentials as your local development environment
- The health check uses `sqlcmd` with the `-C` flag to trust the server certificate
- The 45-second start period gives SQL Server enough time to initialize
- The web container will wait for the database to be healthy before attempting connections

## Testing Locally

To test this setup locally before pushing:

```bash
# Start the database service
docker run -d \
  --name mu-db-test \
  -e ACCEPT_EULA=Y \
  -e MSSQL_SA_PASSWORD=Abcd@1234 \
  -e MSSQL_PID=Express \
  -p 1433:1433 \
  mcr.microsoft.com/mssql/server:2019-latest

# Wait for database to be ready (check logs)
docker logs -f mu-db-test

# Start the web container
docker run -d \
  --name mu-web-test \
  -p 8089:80 \
  -e SQL_SERVER=localhost,1433 \
  -e DB_HOST=localhost \
  -e DB_NAME=MuOnline \
  -e DB_USER=sa \
  -e "DB_PASS=Abcd@1234" \
  mu-web:latest

# Test the homepage
curl -I http://localhost:8089
```

## Related Files
- Pipeline: `.github/workflows/ci-cd-mu-web.yml`
- Web Dockerfile: `Web/Dockerfile`
- Health endpoint: `Web/health.php`
- Homepage: `Web/index.php`
- Database config: `Web/constants.php`
