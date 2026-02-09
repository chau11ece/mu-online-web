# Database restoration script for MU Online
Write-Host "Starting database restoration..."

# Wait for SQL Server to be ready
$timeout = 300
$timer = 0
do {
    try {
        Invoke-Sqlcmd -Query "SELECT 1" -ServerInstance "localhost" -Username "sa" -Password "Abcd@1234!" -ErrorAction Stop
        Write-Host "SQL Server is ready"
        break
    }
    catch {
        Write-Host "Waiting for SQL Server... ($timer seconds)"
        Start-Sleep 5
        $timer += 5
    }
} while ($timer -lt $timeout)

if ($timer -ge $timeout) {
    Write-Error "SQL Server failed to start within timeout"
    exit 1
}

# Restore databases from SQL scripts
$databases = @(
    @{Name="MuOnline"; Script="C:/SQLScripts/MuOnline/MuOnline.sql"},
    @{Name="Events"; Script="C:/SQLScripts/Ranking and Events/Events.sql"},
    @{Name="Ranking"; Script="C:/SQLScripts/Ranking and Events/Ranking.sql"}
)

foreach ($db in $databases) {
    try {
        Write-Host "Restoring database: $($db.Name)"
        
        # Check if database exists
        $exists = Invoke-Sqlcmd -Query "SELECT name FROM sys.databases WHERE name = '$($db.Name)'" -ServerInstance "localhost" -Username "sa" -Password "Abcd@1234!"
        
        if ($exists) {
            Write-Host "Database $($db.Name) already exists, skipping..."
            continue
        }
        
        # Execute SQL script
        if (Test-Path $db.Script) {
            Invoke-Sqlcmd -InputFile $db.Script -ServerInstance "localhost" -Username "sa" -Password "Abcd@1234!" -QueryTimeout 0
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
        Invoke-Sqlcmd -InputFile "C:/SQLExtras/Triggers/Summoner_RagrFighter_Creation.sql" -ServerInstance "localhost" -Username "sa" -Password "Abcd@1234!"
        Write-Host "Triggers applied successfully"
    }
    catch {
        Write-Warning "Failed to apply triggers: $($_.Exception.Message)"
    }
}

Write-Host "Database restoration completed"