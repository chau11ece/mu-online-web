# Setup-LocalSQLServer.ps1
# Script to set up SQL Server 2012 locally for MU Online

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MU Online - Local SQL Server Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Find sqlcmd.exe
$sqlcmdPaths = @(
    "C:\Program Files\Microsoft SQL Server\110\Tools\Binn\sqlcmd.exe",
    "C:\Program Files (x86)\Microsoft SQL Server\110\Tools\Binn\sqlcmd.exe",
    "C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\sqlcmd.exe",
    "${env:ProgramFiles}\Microsoft SQL Server\110\Tools\Binn\sqlcmd.exe",
    "${env:ProgramFiles(x86)}\Microsoft SQL Server\110\Tools\Binn\sqlcmd.exe"
)

$sqlcmd = $null
foreach ($path in $sqlcmdPaths) {
    if (Test-Path $path) {
        $sqlcmd = $path
        break
    }
}

# If not found, search recursively
if (-not $sqlcmd) {
    Write-Host "Searching for sqlcmd.exe..." -ForegroundColor Yellow
    $searchPaths = @("C:\Program Files\Microsoft SQL Server", "C:\Program Files (x86)\Microsoft SQL Server")
    foreach ($searchPath in $searchPaths) {
        if (Test-Path $searchPath) {
            $found = Get-ChildItem -Path $searchPath -Filter "sqlcmd.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
            if ($found) {
                $sqlcmd = $found.FullName
                break
            }
        }
    }
}

if (-not $sqlcmd) {
    Write-Host "[ERROR] sqlcmd.exe not found!" -ForegroundColor Red
    Write-Host "Please install SQL Server 2012 Management Studio or SQL Server 2012 Express" -ForegroundColor Yellow
    Write-Host "Download from: https://www.microsoft.com/en-us/download/details.aspx?id=29062" -ForegroundColor Cyan
    exit 1
}

Write-Host "[OK] Found sqlcmd: $sqlcmd" -ForegroundColor Green

# SQL Server connection settings
$server = "localhost"
$saPassword = "Abcd@1234"

# Check if SQL Server service exists and start it
Write-Host ""
Write-Host "Checking SQL Server services..." -ForegroundColor Yellow

$sqlServices = Get-Service | Where-Object { $_.Name -like "*MSSQL*" -or $_.Name -like "*SQLSERVER*" }

if ($sqlServices) {
    Write-Host "Found SQL Server services:" -ForegroundColor Cyan
    $sqlServices | ForEach-Object { Write-Host "  $($_.Name) - $($_.Status)" }
    
    # Try to start each MSSQLSERVER service
    $mssqlService = $sqlServices | Where-Object { $_.Name -eq "MSSQLSERVER" } | Select-Object -First 1
    if ($mssqlService) {
        if ($mssqlService.Status -ne "Running") {
            Write-Host "Starting MSSQLSERVER service..." -ForegroundColor Yellow
            Start-Service -Name "MSSQLSERVER" -ErrorAction SilentlyContinue
            Start-Sleep 3
        } else {
            Write-Host "MSSQLSERVER is already running" -ForegroundColor Green
        }
    }
} else {
    Write-Host "[WARN] No SQL Server services found!" -ForegroundColor Yellow
    Write-Host "Please start SQL Server 2012 manually from Windows Services" -ForegroundColor Yellow
}

# Wait for SQL Server to be ready
Write-Host ""
Write-Host "Waiting for SQL Server to be ready..." -ForegroundColor Yellow

$timeout = 60
$timer = 0
$sqlReady = $false

while ($timer -lt $timeout) {
    try {
        & $sqlcmd -S $server -U sa -P $saPassword -Q "SELECT 1" -b 2>$null
        if ($LASTEXITCODE -eq 0) {
            $sqlReady = $true
            break
        }
    } catch { }
    
    Start-Sleep 5
    $timer += 5
    Write-Host "  Waiting... ($timer seconds)"
}

if (-not $sqlReady) {
    Write-Host "[ERROR] SQL Server is not responding!" -ForegroundColor Red
    Write-Host "Please ensure SQL Server is running and sa password is 'Abcd@1234'" -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] SQL Server is ready!" -ForegroundColor Green

# Restore databases
Write-Host ""
Write-Host "Restoring databases..." -ForegroundColor Yellow

$scriptPath = "C:\MU-Project\DB"
$databases = @(
    @{Name="MuOnline"; Script="$scriptPath\1. Scripts\MuOnline\MuOnline.sql"},
    @{Name="Me_MuOnline"; Script="$scriptPath\1. Scripts\Me_MuOnline\Me_MuOnline.sql"},
    @{Name="Events"; Script="$scriptPath\1. Scripts\Ranking and Events\Events.sql"},
    @{Name="Ranking"; Script="$scriptPath\1. Scripts\Ranking and Events\Ranking.sql"}
)

foreach ($db in $databases) {
    Write-Host "  Checking database: $($db.Name)..."
    
    # Check if database exists
    $result = & $sqlcmd -S $server -U sa -P $saPassword -Q "SELECT COUNT(*) FROM sys.databases WHERE name = '$($db.Name)'" -h -1 -W -b 2>$null
    $count = [int]($result -replace '[^0-9]', '')
    
    if ($count -gt 0) {
        Write-Host "    Database $($db.Name) already exists, skipping..." -ForegroundColor Cyan
        continue
    }
    
    # Create and restore database
    if (Test-Path $db.Script) {
        Write-Host "    Creating database: $($db.Name)..."
        & $sqlcmd -S $server -U sa -P $saPassword -Q "CREATE DATABASE [$($db.Name)]" -b 2>$null
        
        Write-Host "    Restoring from script..."
        & $sqlcmd -S $server -U sa -P $saPassword -i $db.Script -b 2>$null
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "    [OK] $($db.Name) restored successfully" -ForegroundColor Green
        } else {
            Write-Host "    [WARN] Failed to restore $($db.Name)" -ForegroundColor Yellow
        }
    } else {
        Write-Host "    [WARN] Script not found: $($db.Script)" -ForegroundColor Yellow
    }
}

# Apply triggers
$triggerScript = "$scriptPath\2. Extras\Triggers\Summoner_RagrFighter_Creation.sql"
if (Test-Path $triggerScript) {
    Write-Host ""
    Write-Host "Applying triggers..." -ForegroundColor Yellow
    & $sqlcmd -S $server -U sa -P $saPassword -i $triggerScript -b 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Triggers applied successfully" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "SQL Server is ready at: $server" -ForegroundColor White
Write-Host "sa password: $saPassword" -ForegroundColor White
