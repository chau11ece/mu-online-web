# Database restoration script for MU Online
Write-Host "Starting database restoration..."

# Wait for SQL Server to be ready
$timeout = 300
$timer = 0
do {
    try {
        & 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE' -S localhost -U sa -P "Abcd@1234" -Q "SELECT 1" -b
        if ($LASTEXITCODE -eq 0) {
            Write-Host "SQL Server is ready"
            break
        }
    }
    catch {
        # Ignore errors
    }
    Write-Host "Waiting for SQL Server... ($timer seconds)"
    Start-Sleep 5
    $timer += 5
} while ($timer -lt $timeout)

if ($timer -ge $timeout) {
    Write-Error "SQL Server failed to start within timeout"
    exit 1
}

# Restore databases from SQL scripts
$databases = @(
    @{Name="MuOnline"; Script="C:/SQLScripts/MuOnline/MuOnline.sql"},
    @{Name="Me_MuOnline"; Script="C:/SQLScripts/Me_MuOnline/Me_MuOnline.sql"},
    @{Name="Events"; Script="C:/SQLScripts/Ranking and Events/Events.sql"},
    @{Name="Ranking"; Script="C:/SQLScripts/Ranking and Events/Ranking.sql"}
)

foreach ($db in $databases) {
    try {
        Write-Host "Restoring database: $($db.Name)"

        # Check if database exists
        & 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE' -S localhost -U sa -P "Abcd@1234" -Q "SELECT 1 FROM sys.databases WHERE name = '$($db.Name)'" -b
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Database $($db.Name) already exists, skipping..."
            continue
        }

        # Create the database first
        & 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE' -S localhost -U sa -P "Abcd@1234" -Q "CREATE DATABASE [$($db.Name)]" -b
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to create database $($db.Name)"
        }

        # Execute SQL script
        if (Test-Path $db.Script) {
            & 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE' -S localhost -U sa -P "Abcd@1234" -i $db.Script -b
            if ($LASTEXITCODE -ne 0) {
                throw "Failed to execute script for $($db.Name)"
            }
            Write-Host "Successfully restored: $($db.Name)"
        } else {
            Write-Warning "Script not found: $($db.Script)"
        }
    }
    catch {
        Write-Error "Failed to restore $($db.Name): $($_.Exception.Message)"
    }
}

# Apply extras/triggers if available
if (Test-Path "C:/SQLExtras/Triggers/Summoner_RagrFighter_Creation.sql") {
    try {
        Write-Host "Applying additional triggers..."
        & 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE' -S localhost -U sa -P "Abcd@1234" -i "C:/SQLExtras/Triggers/Summoner_RagrFighter_Creation.sql" -b
        if ($LASTEXITCODE -eq 0) {
            Write-Host "Triggers applied successfully"
        } else {
            Write-Warning "Failed to apply triggers"
        }
    }
    catch {
        Write-Warning "Failed to apply triggers: $($_.Exception.Message)"
    }
}

Write-Host "Database restoration completed"
