# Comprehensive SQL Server Diagnostic Script
# Run as Administrator

Write-Host "=== SQL Server Comprehensive Diagnostic ===" -ForegroundColor Cyan
Write-Host ""

# Check SQL Server error logs
Write-Host "[1/5] Checking SQL Server Error Logs..." -ForegroundColor Yellow
$logPaths = @(
    "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL12.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL13.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL14.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL15.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL16.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files (x86)\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log\ERRORLOG"
)

$foundLog = $false
foreach ($logPath in $logPaths) {
    $logDir = Split-Path $logPath -Parent
    if (Test-Path $logDir) {
        Write-Host "  Found SQL Server directory: $logDir" -ForegroundColor Green
        $foundLog = $true
        
        # Get the most recent ERRORLOG file
        $logFiles = Get-ChildItem -Path $logDir -Filter "ERRORLOG*" | Sort-Object LastWriteTime -Descending
        if ($logFiles) {
            $latestLog = $logFiles[0].FullName
            Write-Host "  Latest log: $latestLog" -ForegroundColor Cyan
            Write-Host "  Last 20 lines:" -ForegroundColor Yellow
            Get-Content $latestLog -Tail 20 | ForEach-Object { Write-Host "    $_" -ForegroundColor White }
        }
        break
    }
}

if (-not $foundLog) {
    Write-Host "  [WARNING] Could not find SQL Server installation directory!" -ForegroundColor Red
    Write-Host "  This suggests SQL Server may not be properly installed." -ForegroundColor Yellow
}

Write-Host ""

# Check if port 1433 is in use
Write-Host "[2/5] Checking if port 1433 is in use..." -ForegroundColor Yellow
try {
    $connection = Test-NetConnection -ComputerName localhost -Port 1433 -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    if ($connection.TcpTestSucceeded) {
        Write-Host "  [WARNING] Port 1433 is already in use!" -ForegroundColor Red
        Write-Host "  This could prevent SQL Server from starting." -ForegroundColor Yellow
        Write-Host "  Checking what's using it..." -ForegroundColor Cyan
        $netstat = netstat -ano | Select-String ":1433"
        if ($netstat) {
            Write-Host $netstat -ForegroundColor White
        }
    } else {
        Write-Host "  [OK] Port 1433 is available" -ForegroundColor Green
    }
} catch {
    Write-Host "  [INFO] Could not test port (may require admin)" -ForegroundColor Yellow
}

Write-Host ""

# Check SQL Server installation paths
Write-Host "[3/5] Checking SQL Server installation..." -ForegroundColor Yellow
$installPaths = @(
    "C:\Program Files\Microsoft SQL Server",
    "C:\Program Files (x86)\Microsoft SQL Server"
)

$foundInstall = $false
foreach ($path in $installPaths) {
    if (Test-Path $path) {
        Write-Host "  Found: $path" -ForegroundColor Green
        $instances = Get-ChildItem -Path $path -Directory -Filter "MSSQL*.SQLEXPRESS" -ErrorAction SilentlyContinue
        if ($instances) {
            foreach ($instance in $instances) {
                Write-Host "    Instance: $($instance.Name)" -ForegroundColor Cyan
                $dataPath = Join-Path $instance.FullName "MSSQL\DATA"
                if (Test-Path $dataPath) {
                    Write-Host "      Data directory exists: [OK]" -ForegroundColor Green
                } else {
                    Write-Host "      Data directory missing: [ERROR]" -ForegroundColor Red
                }
            }
        }
        $foundInstall = $true
    }
}

if (-not $foundInstall) {
    Write-Host "  [ERROR] SQL Server installation directory not found!" -ForegroundColor Red
}

Write-Host ""

# Check service account and permissions
Write-Host "[4/5] Checking service configuration..." -ForegroundColor Yellow
try {
    $svc = Get-WmiObject Win32_Service -Filter "Name='MSSQL`$SQLEXPRESS'" -ErrorAction Stop
    Write-Host "  Service Account: $($svc.StartName)" -ForegroundColor Cyan
    Write-Host "  Service Path: $($svc.PathName)" -ForegroundColor Cyan
    
    # Check if executable exists
    $exePath = ($svc.PathName -split '"')[1]
    if ($exePath) {
        if (Test-Path $exePath) {
            Write-Host "  SQL Server executable exists: [OK]" -ForegroundColor Green
        } else {
            Write-Host "  SQL Server executable missing: [ERROR]" -ForegroundColor Red
            Write-Host "    Expected: $exePath" -ForegroundColor Yellow
        }
    }
} catch {
    Write-Host "  [ERROR] Could not get service details: $_" -ForegroundColor Red
}

Write-Host ""

# Check Windows Event Logs for SQL Server errors
Write-Host "[5/5] Checking Windows Event Logs..." -ForegroundColor Yellow
try {
    $events = Get-EventLog -LogName Application -Source "MSSQL*" -Newest 5 -ErrorAction SilentlyContinue
    if ($events) {
        Write-Host "  Recent SQL Server events:" -ForegroundColor Cyan
        foreach ($event in $events) {
            Write-Host "    [$($event.TimeGenerated)] $($event.EntryType): $($event.Message.Substring(0, [Math]::Min(100, $event.Message.Length)))..." -ForegroundColor $(if ($event.EntryType -eq 'Error') { 'Red' } else { 'Yellow' })
        }
    } else {
        Write-Host "  No recent SQL Server events found" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  Could not read event logs: $_" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Diagnostic Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Recommendations:" -ForegroundColor Yellow
Write-Host "1. Review the error log above for specific error messages" -ForegroundColor White
Write-Host "2. If files are missing/corrupted, reinstall may be needed" -ForegroundColor White
Write-Host "3. If port is in use, stop the conflicting service" -ForegroundColor White
Write-Host "4. Check Windows Event Viewer for more details" -ForegroundColor White
