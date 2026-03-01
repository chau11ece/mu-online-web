# SQL Server Diagnostic Script
# Run as Administrator

Write-Host "=== SQL Server Diagnostic Script ===" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[ERROR] This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    exit 1
} else {
    Write-Host "[OK] Running as Administrator" -ForegroundColor Green
}

Write-Host ""

# Find all SQL Server services
Write-Host "=== SQL Server Services ===" -ForegroundColor Yellow
$sqlServices = Get-Service | Where-Object { $_.Name -like '*MSSQL*' -or $_.DisplayName -like '*SQL Server*' } | Sort-Object Name
if ($sqlServices) {
    $sqlServices | Format-Table -Auto Name, Status, StartType, DisplayName
} else {
    Write-Host "[WARNING] No SQL Server services found!" -ForegroundColor Yellow
}

Write-Host ""

# Auto-detect running SQL Server instance
$sqlInstance = $null
$sqlServiceName = $null
$runningSqlService = Get-Service | Where-Object { $_.Name -like 'MSSQL*' -and $_.Name -notlike '*FDLauncher*' -and $_.Status -eq 'Running' } | Select-Object -First 1
if ($runningSqlService) {
    if ($runningSqlService.Name -eq 'MSSQLSERVER') {
        $sqlInstance = "localhost"
        $sqlServiceName = "MSSQLSERVER"
    } elseif ($runningSqlService.Name -match 'MSSQL\$(.+)') {
        $sqlInstance = "localhost\$($Matches[1])"
        $sqlServiceName = $runningSqlService.Name
    }
    Write-Host "[DETECTED] Running SQL Server instance: $sqlInstance (Service: $sqlServiceName)" -ForegroundColor Green
}

Write-Host ""

# Check for any running SQL Server service
Write-Host "=== Checking SQL Server Service ===" -ForegroundColor Yellow
$sqlServicesToCheck = @('MSSQLSERVER', 'MSSQL$SQLEXPRESS', 'MSSQL$MSSQLSERVER01')
$foundService = $false

foreach ($svcName in $sqlServicesToCheck) {
    try {
        $service = Get-Service -Name $svcName -ErrorAction SilentlyContinue
        if ($service) {
            Write-Host "Service Name: $($service.Name)" -ForegroundColor Cyan
            Write-Host "Display Name: $($service.DisplayName)" -ForegroundColor Cyan
            Write-Host "Status: $($service.Status)" -ForegroundColor $(if ($service.Status -eq 'Running') { 'Green' } else { 'Yellow' })
            Write-Host "Start Type: $($service.StartType)" -ForegroundColor Cyan
            $foundService = $true
            
            if ($service.Status -eq 'Stopped') {
                Write-Host ""
                Write-Host "Attempting to start service..." -ForegroundColor Yellow
                
                # Check dependencies
                Write-Host "Checking dependencies..." -ForegroundColor Cyan
                $deps = Get-Service -Name $svcName | Select-Object -ExpandProperty DependentServices -ErrorAction SilentlyContinue
                if ($deps) {
                    Write-Host "Dependent services found:" -ForegroundColor Cyan
                    $deps | Format-Table -Auto Name, Status
                }
                
                $required = Get-Service -Name $svcName | Select-Object -ExpandProperty ServicesDependedOn -ErrorAction SilentlyContinue
                if ($required) {
                    Write-Host "Required services:" -ForegroundColor Cyan
                    $required | Format-Table -Auto Name, Status
                }
                
                Write-Host ""
                Write-Host "Starting service..." -ForegroundColor Yellow
                Start-Service -Name $svcName -ErrorAction Stop
                Start-Sleep -Seconds 3
                
                $service.Refresh()
                if ($service.Status -eq 'Running') {
                    Write-Host "[SUCCESS] SQL Server started successfully!" -ForegroundColor Green
                } else {
                    Write-Host "[WARNING] Service status: $($service.Status)" -ForegroundColor Yellow
                }
            } else {
                Write-Host "[OK] SQL Server is already running" -ForegroundColor Green
            }
            break
        }
    } catch {
        # Service doesn't exist, continue to next
    }
}

if (-not $foundService) {
    Write-Host "[WARNING] No standard SQL Server service found. Checking for any running MSSQL service..." -ForegroundColor Yellow
    $anyMssql = Get-Service | Where-Object { $_.Name -like 'MSSQL*' -and $_.Status -eq 'Running' }
    if ($anyMssql) {
        Write-Host "Found running MSSQL services:" -ForegroundColor Cyan
        $anyMssql | Format-Table -Auto Name, Status
    }
}

Write-Host ""

# Try alternative service names
Write-Host "=== Alternative SQL Server Instances ===" -ForegroundColor Yellow
$altServices = @('MSSQLSERVER', 'MSSQL$MSSQLSERVER', 'SQLSERVERAGENT', 'SQLSERVERAGENT$SQLEXPRESS', 'MSSQL$MSSQLSERVER01')
foreach ($svcName in $altServices) {
    try {
        $svc = Get-Service -Name $svcName -ErrorAction SilentlyContinue
        if ($svc) {
            Write-Host "[FOUND] $svcName - Status: $($svc.Status)" -ForegroundColor Cyan
        }
    } catch {
        # Service doesn't exist, skip
    }
}

Write-Host ""

# Check SQL Server error logs location
Write-Host "=== SQL Server Error Log Location ===" -ForegroundColor Yellow
$logPaths = @(
    "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL12.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL13.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL14.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL15.SQLEXPRESS\MSSQL\Log\ERRORLOG",
    "C:\Program Files\Microsoft SQL Server\MSSQL16.MSSQLSERVER01\MSSQL\Log\ERRORLOG",
    "C:\Program Files (x86)\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log\ERRORLOG"
)

$foundLog = $false
foreach ($logPath in $logPaths) {
    if (Test-Path $logPath) {
        Write-Host "[FOUND] Error log: $logPath" -ForegroundColor Green
        Write-Host "Last 10 lines:" -ForegroundColor Cyan
        Get-Content $logPath -Tail 10 | ForEach-Object { Write-Host "  $_" }
        $foundLog = $true
        break
    }
}

if (-not $foundLog) {
    Write-Host "[INFO] Could not find SQL Server error log automatically" -ForegroundColor Yellow
    Write-Host "Try checking: C:\Program Files\Microsoft SQL Server\*\*\MSSQL\Log\ERRORLOG" -ForegroundColor Cyan
}

Write-Host ""

# Test SQL connection
Write-Host "=== Testing SQL Server Connection ===" -ForegroundColor Yellow

# Use auto-detected instance or try common ones
$connectionStrings = @()
if ($sqlInstance) {
    $connectionStrings += "Server=$sqlInstance;Integrated Security=True;Connection Timeout=5;"
}
# Add fallback connection strings
$connectionStrings += "Server=localhost\MSSQLSERVER01;Integrated Security=True;Connection Timeout=5;"
$connectionStrings += "Server=localhost\SQLEXPRESS;Integrated Security=True;Connection Timeout=5;"
$connectionStrings += "Server=localhost;Integrated Security=True;Connection Timeout=5;"

$connectionSuccess = $false
foreach ($connectionString in $connectionStrings) {
    try {
        Write-Host "Trying: $connectionString" -ForegroundColor Cyan
        $connection = New-Object System.Data.SqlClient.SqlConnection($connectionString)
        $connection.Open()
        Write-Host "[SUCCESS] Connected to SQL Server!" -ForegroundColor Green
        $connection.Close()
        $connectionSuccess = $true
        break
    } catch {
        Write-Host "  [FAIL] $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== Diagnostic Complete ===" -ForegroundColor Cyan
