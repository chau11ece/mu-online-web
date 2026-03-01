$ErrorActionPreference = 'Stop'

Write-Host "Starting SQL Server service..."
Start-Service -Name "MSSQLSERVER"

# Wait for the service to reach Running state (up to 120 seconds)
$timeout = 120
$timer = 0
do {
    Start-Sleep 5
    $timer += 5
    $service = Get-Service -Name "MSSQLSERVER" -ErrorAction SilentlyContinue
    Write-Host "Waiting for SQL Server service... ($timer seconds) - Status: $($service.Status)"
} while ($service.Status -ne "Running" -and $timer -lt $timeout)

if ($service.Status -ne "Running") {
    Write-Error "SQL Server failed to start within $timeout seconds."
    exit 1
}

Write-Host "SQL Server service is running."

Write-Host "SQL Server is running. Checking for database restoration..."

$sqlcmd = 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE'

if (-not (Test-Path C:\SQLData\MuOnline.mdf)) {
    # First time: no data files exist, run full restore
    Write-Host "Database files not found, running full restore..."
    try {
        & C:/restore-databases.ps1
        Write-Host "Database restoration completed."
    } catch {
        Write-Error "Database restoration failed: $($_.Exception.Message)"
        exit 1
    }
} else {
    # Data files exist on volume but may not be attached after container recreate
    Write-Host "Database files found on volume. Checking if attached..."
    $databases = @(
        @{Name="MuOnline"; Mdf="C:\SQLData\MuOnline.mdf"; Ldf="C:\SQLData\MuOnline_log.ldf"},
        @{Name="Me_MuOnline"; Mdf="C:\SQLData\Me_MuOnline.mdf"; Ldf="C:\SQLData\Me_MuOnline_log.ldf"},
        @{Name="Events"; Mdf="C:\SQLData\Events.mdf"; Ldf="C:\SQLData\Events_log.ldf"},
        @{Name="Ranking"; Mdf="C:\SQLData\Ranking.mdf"; Ldf="C:\SQLData\Ranking_log.ldf"}
    )
    foreach ($db in $databases) {
        # Check if database is already registered in SQL Server
        $result = & $sqlcmd -S localhost -U sa -P "Abcd@1234" -Q "SET NOCOUNT ON; SELECT COUNT(*) FROM sys.databases WHERE name = '$($db.Name)'" -h -1 -W -b 2>&1
        $count = ($result | Select-String -Pattern '^\d+$').Matches.Value
        if ($count -eq '0' -and (Test-Path $db.Mdf)) {
            Write-Host "Attaching database: $($db.Name)..."
            & $sqlcmd -S localhost -U sa -P "Abcd@1234" -Q "CREATE DATABASE [$($db.Name)] ON (FILENAME = '$($db.Mdf)'), (FILENAME = '$($db.Ldf)') FOR ATTACH" -b
            if ($LASTEXITCODE -eq 0) {
                Write-Host "Successfully attached: $($db.Name)"
            } else {
                Write-Warning "Failed to attach: $($db.Name)"
            }
        } else {
            Write-Host "Database $($db.Name) already attached or .mdf not found."
        }
    }
    Write-Host "Database attach check completed."
}

Write-Host "SQL Server and databases are ready. Keeping container running..."
# Keep the container running indefinitely
while ($true) {
    Start-Sleep 60
    # Optional: Check if SQL Server is still running
    $service = Get-Service -Name "MSSQLSERVER" -ErrorAction SilentlyContinue
    if ($service.Status -ne "Running") {
        Write-Error "SQL Server service stopped unexpectedly. Exiting."
        exit 1
    }
}
